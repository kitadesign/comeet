(function(){
	var menu;
	function loaded () {
		if (!menu) menu = new MenuContainer();
		menu.attend();
	}
	document.addEventListener( 'DOMContentLoaded', loaded, false );
})();
// TODO: ここも出来るか確認する　http://yabooo.org/archives/180
// window.onload=function(){alert('test');}
// window.onunload=function(){};

var MenuContainer = defineClass({
	initialize: function () {
		this.$viewport     = $('#viewport');
		this.$pageContent  = $('#pageContent');
		this.$main         = $('.main');
		this.$menu         = $('#menu');
		this.$menuButton   = $('#headerMenu');
		this.$scroller     = $('#scroller');
		this.openFlag      = false;
		this.attendFlag    = false;

		this.$menu_meet_setting = $('#menu_meet_setting');
		this.$menu_meet_feed    = $('#menu_meet_feed');
		this.$menu_friend       = $('#menu_friend');
		this.$menu_profile      = $('#menu_profile');

		this.$menuName = this.$menu.find( 'div.text p' );

		this.meetfeedController    = new MeetFeedController( this );
		this.meetsettingController = new MeetSettingController( this );
		this.profileController     = new ProfileController( this );
		this.friendController      = new FriendController( this );
		this.selectedController    = this.meetfeedController;

		this.$loadingImage = getLoadingImage();

		this.signature         = getInternalParams( 'signature' );
		this.editButtonLabel   = getInternalParams( 'edit-button' );
		this.saveButtonLabel   = getInternalParams( 'save-button' );
		this.facebookSyncLabel = getInternalParams( 'facebook-sync' );
	},
	attend: function () {
		this.$menuButton.bind('click', _.bind( function () {
			if (this.openFlag) {
				this.close( true );
			} else {
				this.open();
			}
		}, this ) );

		this.$viewport.swipeRight( _.bind( function () {
			if (!this.openFlag) {
				this.open();
			}
		}, this ) );

		this.$pageContent.tap( _.bind( function () {
			if (this.openFlag) {
				this.close( true );
			}
		}, this ) );

		this.$viewport.swipeLeft( _.bind( function () {
			if (this.openFlag) {
				this.close( true );
			}
		}, this ) );
	},
	close: function ( controllerOpenFlag ) {
		// アニメーションを組むときはこの辺りを変更する
		this.$main.removeClass('paddingTop50');
		this.$viewport.removeClass('viewport');
		this.$pageContent.removeClass('pageContent');
		this.$menu.hide();
		this.removeLoadingImage();
		this.openFlag = false;
		if ( controllerOpenFlag ) this.selectedController.open();
	},
	open: function () {
		// アニメーションを組むときはこの辺りを変更する
		this.$main.addClass('paddingTop50');
		this.$viewport.addClass('viewport');
		this.$pageContent.addClass('pageContent');
		this.$menu.show();
		this.$menu.css( 'height', innerHeight );
		this.openFlag = true;
		if (!this.attendFlag) {
			this.setMenu();
			this.attendFlag = true;
		}
		if ( this.meetfeedController.isOpen() )    this.meetfeedController.close();
		if ( this.meetsettingController.isOpen() ) this.meetsettingController.close();
		if ( this.profileController.isOpen() )     this.profileController.close();
		if ( this.friendController.isOpen() )      this.friendController.close();
	},
	setMenu: function () {
		this.$menu_meet_feed.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.selectedController = this.meetfeedController;
			this.meetfeedController.open();
		}, this ) );
		this.$menu_meet_setting.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.selectedController = this.meetsettingController;
			this.meetsettingController.open();
		}, this ) );
		this.$menu_profile.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.selectedController = this.profileController;
			this.profileController.open();
		}, this ) );
		this.$menu_friend.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.selectedController = this.friendController;
			this.friendController.open();
		}, this ) );
	},
	setMemberName: function ( name ) {
		this.$menuName.text( name );
	},
	addLoadingImage: function ( $button ) {
		$button.after( this.$loadingImage );
		this.$loadingImage.show();
	},
	removeLoadingImage: function () {
		this.$loadingImage.hide();
	}
});

var MeetFeedController = defineClass({
	initialize: function ( menu ) {
		this.menu      = menu;
		this.$scroller = $('#scroller');
		this.openFlag  = false;
	},
	open: function () {
		this.callRpc( _.bind( this.callbackOpen, this ) );
	},
	callRpc: function ( callback ) {
		callJsonRpc( 'ajax/get_meetfeed.php', {
			auth_type: 'signature',
			signature: this.menu.signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
		if ( isSuccess ) {
			// TODO: この辺りの作り
			var meetFeed = getTemplate( 'meet_feed', {data: data} );
			this.$scroller.html(meetFeed);
			this.openFlag = true;
			this.menu.close();
		} else {
			console.log('TODO: Error handling');
		}
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.openFlag = false;
	}
});
var MeetSettingController = defineClass({
	initialize: function ( menu ) {
		this.menu      = menu;
		this.$scroller = $('#scroller');
		this.openFlag  = false;

		this.editMeetingTagsFlag    = false;
		this.editMeetingProfileFlag = false;
		this.editLocationFlag       = false;

		this.$loadingImage = getLoadingImage();
	},
	open: function () {
		this.callRpc( _.bind( this.callbackOpen, this ) );
	},
	callRpc: function ( callback ) {
		callJsonRpc( 'ajax/get_meet_setting.php', {
			auth_type: 'signature',
			signature: this.menu.signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.appendView( data );
			this.openFlag = true;
			this.menu.close();
		} else {
			console.log('TODO: Error handling');
		}
	},
	appendView: function ( data ) {
		_.each( data.meeting_tags, function ( value ) {
			switch ( value['number'] ) {
			case '0':
				data.meeting_tag0       = value['text'];
				data.meeting_tag_check0 = value['enable'];
				break;
			case '1':
				data.meeting_tag1       = value['text'];
				data.meeting_tag_check1 = value['enable'];
				break;
			case '2':
				data.meeting_tag2       = value['text'];
				data.meeting_tag_check2 = value['enable'];
				break;
			}
		});

		data.location = data.location_ids[0]; // 活動場所を複数にする場合はこの辺りを変更する
		data.mtg_profile = convertLineFeed( data.mtg_profile, true );
		var meetSetting = getTemplate( 'meet_setting', {data: data} );
		this.$scroller.html( meetSetting );

		this.$editMeetingTagsButton = this.$scroller.find( 'section#editMeetingTags p.editBtn a' );
		this.$sectionMeetingTags    = this.$scroller.find( 'section#editMeetingTags' );
		this.$checkMeetingTags      = this.$scroller.find( 'section#editMeetingTags ul li.radio' );
		this.$txtHeadMeetingTags    = this.$scroller.find( 'section#editMeetingTags h2.txtHead' );
		this.$editMeetingTagsButton.text( this.menu.editButtonLabel );
		this.$editMeetingTagsButton.bind( 'click', _.bind( this.clickMeetingTagsButton, this ) );

		this.$editMeetingProfileButton = this.$scroller.find( 'section#editMeetingProfile p.editBtn a' );
		this.$textAreaMeetingProfile   = this.$scroller.find( 'section#editMeetingProfile div p.text' );
		this.$txtHeadMeetingProfile    = this.$scroller.find( 'section#editMeetingProfile h2.txtHead' );
		this.$editMeetingProfileButton.text( this.menu.editButtonLabel );
		this.$editMeetingProfileButton.bind( 'click', _.bind( this.clickMeetingProfileButton, this ) );

		this.$editLocationButton = this.$scroller.find( 'section#editLocation p.editBtn a' );
		this.$txtHeadLocation    = this.$scroller.find( 'section#editLocation h2.txtHead' );
		this.$textAreaLocation   = this.$scroller.find( 'section#editLocation div#textLocation' );
		this.$selectLocation     = this.$scroller.find( 'section#editLocation div#selectLocation' );
		this.$editLocationButton.text( this.menu.editButtonLabel );
		this.$editLocationButton.bind( 'click', _.bind( this.clickLocationButton, this ) );
	},
	clickMeetingTagsButton: function ( event ) {
		if ( this.editMeetingTagsFlag ) {
			var tags = [];
			var $listText = this.$sectionMeetingTags.find( 'ul li.text input' );
			_.each( $listText, function ( value ) {
				tags[tags.length] = $(value).val();
			} );
			var enable_flg = this.$checkMeetingTags.find( 'input:checked' ).val();
			this.$txtHeadMeetingTags.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditMeetSetting( _.bind( this.callbackMeetingTags, this ),
				{meeting_tags: tags, enable_flg: enable_flg}
			);
		} else {
			var $listText = this.$sectionMeetingTags.find( 'ul li.text' );
			_.each( $listText, function ( value ) {
				var $input = getInputText( $(value).text(), 10 );
				$(value).html( $input );
			} );
			var $ul = this.$sectionMeetingTags.find( 'ul.checkboxList' );
			$ul.show();
			this.editMeetingTagsFlag = true;
			this.$editMeetingTagsButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackMeetingTags: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var $listText = this.$sectionMeetingTags.find( 'ul li.text' );
			_.each( $listText, function ( value, key ) {
				var $input = $(value).find( 'input' );
				$(value).text( $input.val() );
				if ( key == data.enable_flg ) {
					if ( $input.val().length > 0 ) $(value).addClass( 'borderGray' );
				} else {
					$(value).removeClass( 'borderGray' );
				}
			} );
			var $ul = this.$sectionMeetingTags.find( 'ul.checkboxList' );
			$ul.hide();
			this.editMeetingTagsFlag = false;
			this.$editMeetingTagsButton.text( this.menu.editButtonLabel );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	clickMeetingProfileButton: function ( event ) {
		if ( this.editMeetingProfileFlag ) {
			var data = this.$textAreaMeetingProfile.find( 'textarea' ).val();
			this.$txtHeadMeetingProfile.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditMeetSetting( _.bind( this.callbackMeetingProfile, this ),
				{mtg_profile: ( data ) ? data : ''}
			);
		} else {
			var $textarea = getTextArea( this.$textAreaMeetingProfile.html(), 10, 40 );
			this.$textAreaMeetingProfile.html( '' );
			this.$textAreaMeetingProfile.append( $textarea );
			this.editMeetingProfileFlag = true;
			this.$editMeetingProfileButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackMeetingProfile: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.$textAreaMeetingProfile.html( convertLineFeed( data.mtg_profile, true ) );
			this.editMeetingProfileFlag = false;
			this.$editMeetingProfileButton.text( this.menu.editButtonLabel );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	clickLocationButton: function ( event ) {
		if ( this.editLocationFlag ) {
			var data = this.$selectLocation.find( 'select' ).val();
			this.$txtHeadLocation.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditMeetSetting( _.bind( this.callbackLocation, this ),
				{location: ( data ) ? data : ''}
			);
		} else {
			this.$textAreaLocation.hide();
			this.$selectLocation.show();
			this.editLocationFlag = true;
			this.$editLocationButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackLocation: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var oldData = this.$textAreaLocation.text();
			if ( oldData != data.location ) {
				this.$textAreaLocation.text( data.location );
				var $options = this.$selectLocation.find( 'select option' );
				$options.each(function ( key, option ) {
					var $option = $(option);
					if ($option.text() != data.location ) {
						$option.removeAttr( 'selected' );
					} else {
						$option.attr( 'selected', 'selected' );
					}
				});
			}
			this.$textAreaLocation.show();
			this.$selectLocation.hide();
			this.editLocationFlag = false;
			this.$editLocationButton.text( this.menu.editButtonLabel );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.openFlag = false;
	},
	callEditMeetSetting: function ( callback, meet_setting ) {
		callJsonRpc( 'ajax/set_meet_setting.php', {
			auth_type: 'signature',
			signature: this.menu.signature,
			meet_setting: meet_setting
		}, callback );
	}
});

var FriendController = defineClass({
	initialize: function ( menu ) {
		this.menu          = menu;
		this.$scroller     = $('#scroller');
		this.$friendSelect = $('#selectedArea');
		this.openFlag      = false;
	},
	open: function () {
		this.callRpc( _.bind( this.callbackOpen, this ) );
	},
	callRpc: function ( callback ) {
		callJsonRpc( 'ajax/get_friends.php', {
			auth_type: 'signature',
			signature: this.menu.signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
		// TODO: この辺りはまだまだ作る
		var friend = getTemplate( 'friend', {data: data} );
		this.$scroller.html( friend );

		var friendSelect = getTemplate('friend_select', {data: data} );
		this.$friendSelect.html(friendSelect);
		this.$friendSelect.show();
		this.openFlag = true;
		this.menu.close();
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.$friendSelect.hide();
		this.openFlag = false;
	}
});

var ProfileController = defineClass({
	initialize: function ( menu ) {
		this.menu      = menu;
		this.$scroller = $('#scroller');
		this.openFlag  = false;

		this.editNameFlag         = false;
		this.editCompanyEmailFlag = false;
		this.editProfileTagsFlag  = false;

		this.$loadingImage = getLoadingImage();
	},
	open: function () {
		this.callRpc( _.bind( this.callbackOpen, this ) );
	},
	callRpc: function ( callback ) {
		callJsonRpc( 'ajax/get_profile.php', {
			auth_type: 'signature',
			signature: this.menu.signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.appendView( data );
			this.openFlag = true;
			this.menu.close();
		} else {
			console.log('TODO: Error handling');
		}
	},
	appendView: function ( data ) {
		var profile = getTemplate( 'profile', {data: data} );
		this.$scroller.html( profile );

		this.$editNameButton = this.$scroller.find( 'section#editName p.editBtn a' );
		this.$textAreaName   = this.$scroller.find( 'section#editName p.text' );
		this.$txtHeadName    = this.$scroller.find( 'section#editName h2.txtHead' );
		this.$editNameButton.text( this.menu.editButtonLabel );
		this.$editNameButton.bind( 'click', _.bind( this.clickEditName, this ) );

		this.$editConmpanyEmailButton = this.$scroller.find( 'section#editCompanyEmail p.editBtn a' );
		this.$textAreaCompanyEmail    = this.$scroller.find( 'section#editCompanyEmail p.text' );
		this.$txtHeadCompanyEmail     = this.$scroller.find( 'section#editCompanyEmail h2.txtHead' );
		this.$editConmpanyEmailButton.text( this.menu.editButtonLabel );
		this.$editConmpanyEmailButton.bind( 'click', _.bind( this.clickEditCompanyEmail, this ) );

		this.$editPRButton = this.$scroller.find( 'section#editPR p.editBtn a' );
		this.$textAreaPR   = this.$scroller.find( 'section#editPR p.text' );
		this.$txtHeadPR    = this.$scroller.find( 'section#editPR h2.txtHead' );
		this.$editPRButton.text( this.menu.facebookSyncLabel );
		this.$editPRButton.bind( 'click', _.bind( this.clickEditPR, this ) );

		this.$editProfileTagsButton = this.$scroller.find( 'section#editProfileTags p.editBtn a' );
		this.$sectionProfileTags    = this.$scroller.find( 'section#editProfileTags' );
		this.$editProfileTagsButton.text( this.menu.editButtonLabel );
		this.$editProfileTagsButton.bind( 'click', _.bind( this.clickEditProfileTags, this ) );
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.openFlag = false;
	},
	clickEditName: function ( event ) {
		if ( this.editNameFlag ) {
			var $input = this.$textAreaName.find( 'input' );
			this.$txtHeadName.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditProfile( _.bind( this.callbackEditName, this ), {member_name: $input.val()} );
		} else {
			var $input = getInputText( this.$textAreaName.text(), 40 );
			this.$textAreaName.html( '' );
			this.$textAreaName.append( $input );
			this.editNameFlag = true;
			this.$editNameButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackEditName: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.menu.setMemberName( data.member_name );
			this.$textAreaName.text( data.member_name );
			this.$editNameButton.text( this.menu.editButtonLabel );
			this.editNameFlag = false;
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	clickEditCompanyEmail: function ( event ) {
		if ( this.editCompanyEmailFlag ) {
			var $input = this.$textAreaCompanyEmail.find( 'input' );
			this.$txtHeadCompanyEmail.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditProfile( _.bind( this.callbackEditCompanyEmail, this ),
				{company_email_address: ( $input.val() ) ? $input.val() : ''}
			);
		} else {
			var $input = getInputText( this.$textAreaCompanyEmail.text(), 40 );
			this.$textAreaCompanyEmail.html( '' );
			this.$textAreaCompanyEmail.append( $input );
			this.editCompanyEmailFlag = true;
			this.$editConmpanyEmailButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackEditCompanyEmail: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.$textAreaCompanyEmail.text( data.company_email_address );
			this.$editConmpanyEmailButton.text( this.menu.editButtonLabel );
			this.editCompanyEmailFlag = false;
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	clickEditPR: function ( event ) {
		this.$txtHeadPR.append( this.$loadingImage );
		this.$loadingImage.show();
		this.callEditProfile( _.bind( this.callbackEditPR, this ), {member_pr: 'update'} );
	},
	callbackEditPR: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.$textAreaPR.text( data.member_pr );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	clickEditProfileTags: function ( event ) {
		if ( this.editProfileTagsFlag ) {
			var $list = this.$sectionProfileTags.find( 'ul li input' );
			var $txtHead = this.$sectionProfileTags.find( 'h2.txtHead' );
			$txtHead.append( this.$loadingImage );
			this.$loadingImage.show();
			var tags = [];
			_.each( $list, function ( value, key ) {
				tags[key] = $(value).val();
			});
			this.callEditProfile( _.bind( this.callbackEditProfileTags, this ), {profile_tags: tags} );
		} else {
			var $list = this.$sectionProfileTags.find( 'ul li' );
			_.each( $list, function ( value ) {
				var $input = getInputText( $(value).text(), 10 );
				$(value).html( $input );
			});
			this.editProfileTagsFlag = true;
			this.$editProfileTagsButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackEditProfileTags: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var $list = this.$sectionProfileTags.find( 'ul li' );
			_.each( $list, function ( value ) {
				var $input = $(value).find( 'input' );
				$(value).text( $input.val() );
			});
			this.editProfileTagsFlag = false;
			this.$editProfileTagsButton.text( this.menu.editButtonLabel );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	callEditProfile: function ( callback, profile ) {
		callJsonRpc( 'ajax/set_profile.php', {
			auth_type: 'signature',
			signature: this.menu.signature,
			profile: profile
		}, callback );
	}
});
