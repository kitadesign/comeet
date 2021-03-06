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
		this.$viewport     = $( '#viewport' );
		this.$pageContent  = this.$viewport.find( '#pageContent' );
		this.$main         = this.$viewport.find( '.main' );
		this.$menu         = this.$viewport.find( '#menu' );
		this.$header       = this.$pageContent.find( 'header.siteHeader' );
		this.$menuButton   = this.$header.find( '#headerMenu' );
		this.$scroller     = this.$main.find( '#scroller' );
		this.openFlag      = false;
		this.attendFlag    = false;

		this.$menu_meet_setting = this.$menu.find( '#menu_meet_setting' );
		this.$menu_meet_feed    = this.$menu.find( '#menu_meet_feed' );
		this.$menu_friend       = this.$menu.find( '#menu_friend' );
		this.$menu_profile      = this.$menu.find( '#menu_profile' );

		this.$menuName        = this.$menu.find( 'div.text p' );
		this.$menuButtonLabel = this.$menuButton.find( 'li a' );

		this.meetfeedController    = new MeetFeedController( this );
		this.meetsettingController = new MeetSettingController( this );
		this.profileController     = new ProfileController( this );
		this.friendController      = new FriendController( this );
		this.selectedController    = this.meetfeedController;

		this.$loadingImage = getLoadingImage();

		this.signature          = getInternalParams( 'signature' );
		this.editButtonLabel    = getInternalParams( 'edit-button' );
		this.saveButtonLabel    = getInternalParams( 'save-button' );
		this.meetNowButtonLabel = getInternalParams( 'meet-now-button' );
		this.meetTextareaLabel  = getInternalParams( 'meet-textarea' );

		this.imageUrlSmall   = getInternalParams( 'image-url-s' );
		this.nodeName        = getInternalParams( 'node-name' );
		this.defaultImageUrl = getInternalParams( 'default-image-url' );

		this.toastSaveLabel           = convertLineFeed( getInternalParams( 'toast-save-label' ), true );
		this.toastNotSaveLabel        = convertLineFeed( getInternalParams( 'toast-not-save-label' ), true );
		this.toastServerErrorLabel    = convertLineFeed( getInternalParams( 'toast-server-error-label' ), true );
		this.toastNotGetLabel         = convertLineFeed( getInternalParams( 'toast-not-get-label' ), true );
		this.toastNotConnectLabel     = convertLineFeed( getInternalParams( 'toast-not-connect-label' ), true );
		this.toastRequestMeetNowLabel = convertLineFeed( getInternalParams( 'toast-request-meet-now-label' ), true );
	},
	attend: function () {
		this.$menuButton.on('click', _.bind( function () {
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
		this.selectedController.open();
	},
	close: function ( controllerOpenFlag ) {
		// アニメーションを組むときはこの辺りを変更する
		this.$viewport.removeClass('viewport');
		this.$pageContent.removeClass('pageContent');
		this.$menu.hide();
		this.$pageContent.css( 'height', 'auto' );
		this.$pageContent.css( 'overflow-y', 'auto' );
		this.$menuButtonLabel.attr( 'href', '#/menu' );
		this.$header.removeClass( 'menuOpenHeadArea' );
		this.removeLoadingImage();
		this.openFlag = false;
		if ( controllerOpenFlag ) this.selectedController.open();
	},
	open: function () {
		// アニメーションを組むときはこの辺りを変更する
		this.$viewport.addClass('viewport');
		this.$pageContent.addClass('pageContent');
		this.$menu.show();
		
		var menuHeight = this.$menu.css( 'height' );
		menuHeight = menuHeight.replace( 'px', '' );
		if ( menuHeight > innerHeight ) {
			this.$menu.css( 'height', menuHeight );
			this.$pageContent.css( 'height', menuHeight );
		} else {
			this.$menu.css( 'height', innerHeight );
			this.$pageContent.css( 'height', innerHeight );
		}

		this.$pageContent.css( 'overflow-y', 'hidden' );
		this.openFlag = true;
		if (!this.attendFlag) {
			this.setMenu();
			this.attendFlag = true;
		}
		this.selectedController.close();
		this.$menuButtonLabel.attr( 'href', this.selectedController.getMenuUri() );
		this.$header.addClass( 'menuOpenHeadArea' );
	},
	setMenu: function () {
		this.$menu_meet_feed.on('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.selectedController = this.meetfeedController;
			this.meetfeedController.open();
		}, this ) );
		this.$menu_meet_setting.on('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.selectedController = this.meetsettingController;
			this.meetsettingController.open();
		}, this ) );
		this.$menu_profile.on('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.selectedController = this.profileController;
			this.profileController.open();
		}, this ) );
		this.$menu_friend.on('click', _.bind( function ( event ) {
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
	},
	addPageContent: function ( className ) {
		this.$pageContent.addClass( className );
	},
	removePageContent: function ( className ) {
		this.$pageContent.removeClass( className );
	},
	getImageUrlSmall: function ( id ) {
		return this.imageUrlSmall.replace( '%s', id );
	},
	getNodeName: function () {
		return this.nodeName;
	},
	getDefaultImageUrl: function () {
		return this.defaultImageUrl;
	},
	getSaveButton: function () {
		return this.$pageContent.find( 'ul.headerLinkSave' );
	}
});

var MeetFeedController = defineClass({
	initialize: function ( menu ) {
		this.menu        = menu;
		this.$scroller   = $( '#scroller' );
		this.openFlag    = false;
		this.detailController = new MeetDetailController( menu, this );

		this.$loadingImage = getLoadingImage();
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
			var meetFeed = getTemplate( 'meet_feed', {data: data} );
			this.$scroller.html( meetFeed );
			this.addListClickEvent();

			this.openFlag = true;
			this.menu.close();
		} else {
			if ( !data.status ) {
				showToast( this.menu.toastNotConnectLabel );
			} else if ( data.status == 500 ) {
				showToast( this.menu.toastServerErrorLabel );
			} else {
				showToast( this.menu.toastNotGetLabel );
			}
		}
	},
	clickListRow: function ( event ) {
		var $parent = $( event.target ).parents('li');
		var memberId = $parent.data( 'member-id' )
		if ( !memberId ) return;

		if ( this.detailController.isOpen() ) return;

		this.detailController.open( memberId );
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.openFlag = false;
	},
	getMenuUri: function () {
		return '#/meetfeed';
	},
	getList: function () {
		return this.$scroller.find( 'section ul li' );
	},
	addListClickEvent: function () {
		var $list = this.getList();
		$list.on( 'click', _.bind( this.clickListRow, this ) );
	},
	addLoadingImage: function ( memberId ) {
		this.$name = this.$scroller.find( 'li#meetfeed_' + memberId + ' div.text p span.pickupName' );
		this.$name.append( this.$loadingImage );
		this.$loadingImage.show();
	},
	removeLoadingImage: function () {
		this.$loadingImage.remove();
	}
});

var MeetDetailController = defineClass({
	initialize: function ( menu, feed ) {
		this.menu = menu;
		this.feed = feed;
		this.openFlag = false;
		this.openMemberId = 0;
		this.$detailView = $( '#subWrapper' );

		this.$loadingImage = getLoadingImage();
	},
	open: function ( memberId ) {
		this.openFlag = true;
		this.openMemberId = memberId;

		this.feed.addLoadingImage( memberId );
		this.callDetailView( _.bind( this.callbackDetailView, this ), memberId );
	},
	callDetailView: function ( callback, memberId ) {
		callJsonRpc( 'ajax/get_member_detail.php', {
			auth_type: 'signature',
			signature: this.menu.signature,
			member_id: memberId
		}, callback );
	},
	callbackDetailView: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var meetDetail = getTemplate( 'meet_detail', {data: data} );
			this.$detailView.html( meetDetail );
			this.$detailView.show();

			this.$closeButton = this.$detailView.find( 'div.closeBtn' );
			this.$closeButton.on( 'click', _.bind( this.close, this ) );

			this.$actionButton = this.$detailView.find( 'div.formBox ul.formBtnLarge' );
			this.$actionButton.on( 'click', _.bind( this.clickActionButton, this ) );

			this.$buttonAtag = this.$actionButton.find( 'li a' );
			this.$buttonAtag.text( this.menu.meetNowButtonLabel );

			this.$list = this.feed.getList();
			this.$list.off( 'click' );

			this.$meetTextarea = this.$detailView.find( 'div.formBox p.textarea textarea' );
			this.$meetTextarea.html( this.menu.meetTextareaLabel );
			this.$meetTextarea.on( 'focus', _.bind( this.focusMeetTextarea, this ) );
			this.$meetTextarea.on( 'blur', _.bind( this.blurMeetTextarea, this ) );

			this.$detailView.css( 'top', document.body.scrollTop );
		} else {
			if ( !data.status ) {
				showToast( this.menu.toastNotConnectLabel );
			} else if ( data.status == 500 ) {
				showToast( this.menu.toastServerErrorLabel );
			} else {
				showToast( this.menu.toastNotGetLabel );
			}
		}
		this.feed.removeLoadingImage();
	},
	focusMeetTextarea: function ( event ) {
		var $target = $( event.target );
		if ( $target.val() == this.menu.meetTextareaLabel ) {
			$target.val( '' );
		}
	},
	blurMeetTextarea: function ( event ) {
		var $target = $( event.target );
		if ( $target.val() == '' ) {
			$target.val( this.menu.meetTextareaLabel );
		}
	},
	clickActionButton: function ( event ) {
		this.addLoadingImage();

		var meetText = this.$meetTextarea.val();
		if ( meetText == this.menu.meetTextareaLabel ) meetText = '';
		this.callMeetAction( _.bind( this.callbackMeetAction, this ), this.openMemberId, meetText );
		event.stopPropagation();
	},
	callMeetAction: function ( callback, memberId, meetText ) {
		callJsonRpc( 'ajax/set_meet_now.php', {
			auth_type: 'signature',
			signature: this.menu.signature,
			to_member_id: memberId,
			meet_text: meetText
		}, callback );
	},
	callbackMeetAction: function ( isSuccess, data ) {
		if ( isSuccess ) {
			showToast( this.menu.toastRequestMeetNowLabel, 2000 );
		} else {
			if ( !data.status ) {
				showToast( this.menu.toastNotConnectLabel );
			} else {
				showToast( this.menu.toastServerErrorLabel );
			}
		}
		this.removeLoadingImage();
	},
	addLoadingImage: function () {
		this.$buttonAtag.html( this.$loadingImage );
		this.$loadingImage.show();
	},
	removeLoadingImage: function () {
		this.$loadingImage.remove();
		this.$buttonAtag.html( this.menu.meetNowButtonLabel );
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.$detailView.hide();
		this.feed.addListClickEvent();
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
			if ( !data.status ) {
				showToast( this.menu.toastNotConnectLabel );
			} else if ( data.status == 500 ) {
				showToast( this.menu.toastServerErrorLabel );
			} else {
				showToast( this.menu.toastNotGetLabel );
			}
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

		this.editMeetingTagsFlag    = false;
		this.editMeetingProfileFlag = false;
		this.editLocationFlag       = false;

		this.$editMeetingTagsButton = this.$scroller.find( 'section#editMeetingTags p.editBtn a' );
		this.$sectionMeetingTags    = this.$scroller.find( 'section#editMeetingTags' );
		this.$checkMeetingTags      = this.$scroller.find( 'section#editMeetingTags ul li.radio' );
		this.$txtHeadMeetingTags    = this.$scroller.find( 'section#editMeetingTags h2.txtHead' );
		this.$editMeetingTagsButton.text( this.menu.editButtonLabel );
		this.$editMeetingTagsButton.on( 'click', _.bind( this.clickMeetingTagsButton, this ) );

		this.$editMeetingProfileButton = this.$scroller.find( 'section#editMeetingProfile p.editBtn a' );
		this.$textAreaMeetingProfile   = this.$scroller.find( 'section#editMeetingProfile div p.text' );
		this.$txtHeadMeetingProfile    = this.$scroller.find( 'section#editMeetingProfile h2.txtHead' );
		this.$editMeetingProfileButton.text( this.menu.editButtonLabel );
		this.$editMeetingProfileButton.on( 'click', _.bind( this.clickMeetingProfileButton, this ) );

		this.$editLocationButton = this.$scroller.find( 'section#editLocation p.editBtn a' );
		this.$txtHeadLocation    = this.$scroller.find( 'section#editLocation h2.txtHead' );
		this.$textAreaLocation   = this.$scroller.find( 'section#editLocation div#textLocation' );
		this.$selectLocation     = this.$scroller.find( 'section#editLocation div#selectLocation' );
		this.$editLocationButton.text( this.menu.editButtonLabel );
		this.$editLocationButton.on( 'click', _.bind( this.clickLocationButton, this ) );

		this.menu.addPageContent( 'meet_setting' );
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
			var $firstValue;
			_.each( $listText, function ( value ) {
				var $input = getInputText( $(value).text(), 8 );
				$(value).html( $input );
				$firstValue = $(value);
			} );
			if ( $firstValue ) $firstValue.focus();
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
					var val = $input.val();
					if ( val && val.length > 0 ) $(value).addClass( 'borderGray' );
				} else {
					$(value).removeClass( 'borderGray' );
				}
			} );
			var $ul = this.$sectionMeetingTags.find( 'ul.checkboxList' );
			$ul.hide();
			this.editMeetingTagsFlag = false;
			this.$editMeetingTagsButton.text( this.menu.editButtonLabel );
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
		}
		this.$loadingImage.hide();
	},
	clickMeetingProfileButton: function ( event ) {
		if ( this.editMeetingProfileFlag ) {
			this.$txtHeadMeetingProfile.append( this.$loadingImage );
			this.$loadingImage.show();
			var data = this.$textAreaMeetingProfile.find( 'textarea' ).val();
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
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
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
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
		}
		this.$loadingImage.hide();
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.openFlag = false;
		this.menu.removePageContent( 'meet_setting' );
	},
	callEditMeetSetting: function ( callback, meet_setting ) {
		callJsonRpc( 'ajax/set_meet_setting.php', {
			auth_type: 'signature',
			signature: this.menu.signature,
			meet_setting: meet_setting
		}, callback );
	},
	getMenuUri: function () {
		return '#/meetsetting';
	}
});

var FriendController = defineClass({
	initialize: function ( menu ) {
		this.menu          = menu;
		this.$main         = $('.main');
		this.$scroller     = $('#scroller');
		this.$friendSelect = $('#selectedArea');
		this.openFlag      = false;

		this.beforeLikeMembers = new Array();

		this.$loadingImage = getLoadingImage();
		this.saveButtonOpenFlag = false;
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
		if ( isSuccess ) {
			this.appendView( data );
			this.openFlag = true;
			this.menu.close();
			this.removeSaveButton();
		} else {
			if ( !data.status ) {
				showToast( this.menu.toastNotConnectLabel );
			} else if ( data.status == 500 ) {
				showToast( this.menu.toastServerErrorLabel );
			} else {
				showToast( this.menu.toastNotGetLabel );
			}
		}
	},
	appendView: function ( data ) {
		var friend = getTemplate( 'friend', {data: data} );
		this.$scroller.html( friend );

		this.saveButtonOpenFlag = false;

		var $likeButton = this.$scroller.find('div.formBox ul.formBtn');
		$likeButton.on( 'click', _.bind( this.clickLikeButton, this ) );

		var friendSelect = getTemplate( 'friend_select', {data: data} );
		this.$friendSelect.html(friendSelect);
		this.$friendSelect.show();
		this.$main.addClass( 'mainSelectedTop' );

		var selectFields = this.$friendSelect.find('li.notSelect');
		var nodeName = this.menu.getNodeName();
		if ( data.my_frineds ) {
			_.each( data.my_frineds, _.bind( function ( value, key ) {
				var $likeButton = this.getFriendButton( value.id );
				this.disableButton($likeButton);

				var $selectField = $( selectFields[key] );
				this.beforeLikeMembers.push( value.name );
				this.setFriendSelect( $selectField, value.id, value.name );
			}, this ) );
		}

		this.menu.addPageContent( 'friends' );
	},
	appendSaveButton: function () {
		if ( this.saveButtonOpenFlag ) return;

		var $saveButton = this.menu.getSaveButton();
		$saveButton.show();
		$saveButton.on( 'click', _.bind( this.clickSaveFriends, this ) );

		this.saveButtonOpenFlag = true;
	},
	removeSaveButton: function () {
		if ( !this.saveButtonOpenFlag ) return;

		var $saveButton = this.menu.getSaveButton();
		$saveButton.off( 'click' );
		$saveButton.hide();
		this.saveButtonOpenFlag = false;
	},
	clickSaveFriends: function ( event ) {
		var friends = this.getSelects();
		var friendNames = this.getSelectedFriendNames();
		friendNames = _.difference( friendNames, this.beforeLikeMembers );

		var $saveButton = this.menu.getSaveButton();
		var $aButton = $saveButton.find( 'li a' );
		$aButton.html( this.$loadingImage );
		this.callSaveFriends( _.bind( this.callbackSaveFriends, this ), friends, friendNames );
	},
	callbackSaveFriends: function ( isSuccess, data ) {
		var $saveButton = this.menu.getSaveButton();
		var $aButton = $saveButton.find( 'li a' );
		$aButton.html( this.menu.saveButtonLabel );

		if ( isSuccess ) {
			this.removeSaveButton();
			this.open();
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
		}
	},
	callSaveFriends: function ( callback, friends, friendNames ) {
		var nodeName = this.menu.getNodeName();
		callJsonRpc( 'ajax/set_friends.php', {
			auth_type: 'signature',
			signature: this.menu.signature,
			friends: friends,
			friend_names: friendNames,
			node_name: nodeName
		}, callback );
	},
	clickLikeButton: function ( event ) {
		var $aTarget = $( event.target );
		var $ulTarget = $( event.target.parentNode.parentNode );
		var nodeName = this.menu.getNodeName();
		if ( $ulTarget.hasClass( 'disable' ) ) {
			this.removeSelect( $aTarget.data( nodeName ) );
			this.enableButton( $ulTarget );
		} else {
			var res = this.addSelect( $aTarget );
			if ( res ) {
				this.disableButton( $ulTarget );
			}
		}
	},
	addSelect: function ( $target ) {
		var selectFields = this.$friendSelect.find('li.notSelect');
		if ( selectFields.length == 0 ) return false;

		var nodeName = this.menu.getNodeName();
		var memberId = $target.data( 'member-id' );
		var id       = $target.data( nodeName );
		var name     = $target.data( 'name' );

		var $selectField = $( selectFields[0] );

		this.setFriendSelect( $selectField, id, name );
		this.appendSaveButton();

		return true;
	},
	setFriendSelect: function ( $selectField, id, name ) {
		var nodeName = this.menu.getNodeName();
		var imageUrl = this.menu.getImageUrlSmall( id );

		$selectField.removeClass( 'notSelect' );
		$selectField.addClass( 'selected' );
		$selectField.data( nodeName, id );
		var $icon = $selectField.find( 'div.image img.icon' );
		$icon.attr( 'src', imageUrl );

		var $badge = $selectField.find( 'div.image span.badge' );
		$badge.on( 'click', _.bind( this.clickSelectBadge, this ) );
		var $badgeImg = $badge.find( 'img' );
		$badgeImg.data( nodeName, id );
		$badge.show();

		var $name = $selectField.find( 'p.text' );
		$name.text( name );
	},
	removeSelect: function ( id ) {
		var nodeName = this.menu.getNodeName();
		var selectedFields = this.$friendSelect.find('li.selected');
		var $selectedField = $( _.find( selectedFields, function ( value ) {
			if ( $(value).data( nodeName ) == id ) return value;
		} ) );
		$selectedField.data( nodeName, '' );
		$selectedField.removeClass( 'selected' );
		$selectedField.addClass( 'notSelect' );

		var defaultImageUrl = this.menu.getDefaultImageUrl();
		var $icon = $selectedField.find( 'div.image img.icon' );
		$icon.attr( 'src', defaultImageUrl );
		var $badge = $selectedField.find( 'div.image span.badge' );
		var $badgeImg = $badge.find( 'img' );
		$badgeImg.data( nodeName, '' );
		$badge.off();
		$badge.hide();

		var $name = $selectedField.find( 'p.text' );
		$name.text( '' );

		this.appendSaveButton();
	},
	clickSelectBadge: function ( event ) {
		var $target = $( event.target );
		var nodeName = this.menu.getNodeName();
		var id = $target.data( nodeName );

		var $likeButton = this.getFriendButton( id );
		this.enableButton($likeButton);
		this.removeSelect( id );
	},
	getSelects: function () {
		var $selectedFields = this.$friendSelect.find('li.selected');
		var nodeName = this.menu.getNodeName();
		var friends = [];
		_.each( $selectedFields, function ( value, key ) {
			friends[key] = $(value).data( nodeName );
		} );
		return friends;
	},
	getSelectedFriendNames: function () {
		var $selectedFields = this.$friendSelect.find('li.selected');
		var $friendNames = $selectedFields.find( 'p.text' );
		var friends = [];
		_.each( $friendNames, function ( value, key ) {
			friends[key] = $(value).text();
		} );
		return friends;
	},
	enableButton: function ( $target ) {
		if ( $target ) {
			$target.removeClass( 'disable' );
			var $aTarget = $target.find('a');
			$aTarget.text('Like');
		}
	},
	disableButton: function ( $target ) {
		if ( $target ) {
			$target.addClass( 'disable' );
			var $aTarget = $target.find('a');
			$aTarget.text('Liked');
		}
	},
	getFriendButton: function ( id ) {
		var $likeButton = this.$scroller.find('ul.pickupList li#friend_'+id+' div.formBox ul.formBtn');
		return $likeButton;
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.$friendSelect.hide();
		this.$main.removeClass( 'mainSelectedTop' );
		this.removeSaveButton();
		this.menu.removePageContent( 'friends' );
		this.openFlag = false;
	},
	getMenuUri: function () {
		return '#/friend';
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
		this.editCompanyInfoFlag  = false;
		this.editPRFlag           = false;

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
			if ( !data.status ) {
				showToast( this.menu.toastNotConnectLabel );
			} else if ( data.status == 500 ) {
				showToast( this.menu.toastServerErrorLabel );
			} else {
				showToast( this.menu.toastNotGetLabel );
			}
		}
	},
	appendView: function ( data ) {
		var profile = getTemplate( 'profile', {data: data} );
		this.$scroller.html( profile );

		this.editNameFlag         = false;
		this.editCompanyEmailFlag = false;
		this.editProfileTagsFlag  = false;
		this.editCompanyInfoFlag  = false;
		this.editPRFlag           = false;

		this.$editNameButton = this.$scroller.find( 'section#editName p.editBtn a' );
		this.$textAreaName   = this.$scroller.find( 'section#editName p.text' );
		this.$txtHeadName    = this.$scroller.find( 'section#editName h2.txtHead' );
		this.$editNameButton.text( this.menu.editButtonLabel );
		this.$editNameButton.on( 'click', _.bind( this.clickEditName, this ) );

		this.$editConmpanyEmailButton = this.$scroller.find( 'section#editCompanyEmail p.editBtn a' );
		this.$textAreaCompanyEmail    = this.$scroller.find( 'section#editCompanyEmail p.text' );
		this.$txtHeadCompanyEmail     = this.$scroller.find( 'section#editCompanyEmail h2.txtHead' );
		this.$editConmpanyEmailButton.text( this.menu.editButtonLabel );
		this.$editConmpanyEmailButton.on( 'click', _.bind( this.clickEditCompanyEmail, this ) );

		this.$editPRButton = this.$scroller.find( 'section#editPR p.editBtn a' );
		this.$textAreaPR   = this.$scroller.find( 'section#editPR p.text' );
		this.$txtHeadPR    = this.$scroller.find( 'section#editPR h2.txtHead' );
		this.$editPRButton.text( this.menu.editButtonLabel );
		this.$editPRButton.on( 'click', _.bind( this.clickEditPR, this ) );

		this.$sectionProfileTags    = this.$scroller.find( 'section#editProfileTags' );
		this.$editProfileTagsButton = this.$sectionProfileTags.find( 'p.editBtn a' );
		this.$txtHeadProfileTags    = this.$sectionProfileTags.find( 'h2.txtHead' );
		this.$editProfileTagsButton.text( this.menu.editButtonLabel );
		this.$editProfileTagsButton.on( 'click', _.bind( this.clickEditProfileTags, this ) );

		this.$sectionCompanyInfo    = this.$scroller.find( 'section#editCompanyInfo' );
		this.$editCompanyInfoButton = this.$sectionCompanyInfo.find( 'p.editBtn a' );
		this.$textAreaCompanyName0  = this.$sectionCompanyInfo.find( 'p.text span.companyName0' );
		this.$textAreaCompanyUrl0   = this.$sectionCompanyInfo.find( 'p.text span.companyUrl0' );
		this.$textAreaCompanyTel0   = this.$sectionCompanyInfo.find( 'p.text span.companyTel0' );
		this.$txtHeadCompanyInfo    = this.$sectionCompanyInfo.find( 'h2.txtHead' );
		this.$editCompanyInfoButton.text( this.menu.editButtonLabel );
		this.$editCompanyInfoButton.on( 'click', _.bind( this.clickCompanyInfoButton, this ) );

		this.menu.addPageContent( 'profile' );
	},
	isOpen: function () {
		return ( this.openFlag == true );
	},
	close: function () {
		this.openFlag = false;
		this.menu.removePageContent( 'profile' );
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
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
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
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
		}
		this.$loadingImage.hide();
	},
	clickEditPR: function ( event ) {
		if ( this.editPRFlag ) {
			this.$txtHeadPR.append( this.$loadingImage );
			this.$loadingImage.show();
			var data = this.$textAreaPR.find( 'textarea' ).val();
			this.callEditProfile( _.bind( this.callbackEditPR, this ),
				{member_pr: ( data ) ? data : ''}
			);
		} else {
			var $textarea = getTextArea( this.$textAreaPR.html(), 10, 40 );
			this.$textAreaPR.html( '' );
			this.$textAreaPR.append( $textarea );
			this.editPRFlag = true;
			this.$editPRButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackEditPR: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.$textAreaPR.html( convertLineFeed( data.member_pr, true ) );
			this.$editPRButton.text( this.menu.editButtonLabel );
			this.editPRFlag = false;
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
		}
		this.$loadingImage.hide();
	},
	clickEditProfileTags: function ( event ) {
		if ( this.editProfileTagsFlag ) {
			this.$txtHeadProfileTags.append( this.$loadingImage );
			this.$loadingImage.show();

			var $list = this.$sectionProfileTags.find( 'ul li input' );
			var tags = [];
			_.each( $list, function ( value, key ) {
				tags[key] = $(value).val();
			});
			this.callEditProfile( _.bind( this.callbackEditProfileTags, this ), {profile_tags: tags} );
		} else {
			var $list = this.$sectionProfileTags.find( 'ul li' );
			_.each( $list, function ( value ) {
				var $input = getInputText( $(value).text(), 8 );
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
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
		}
		this.$loadingImage.hide();
	},
	clickCompanyInfoButton: function ( event ) {
		if ( this.editCompanyInfoFlag ) {
			var $inputCompanyName0 = this.$textAreaCompanyName0.find( 'input' );
			var $inputCompanyUrl0  = this.$textAreaCompanyUrl0.find( 'input' );
			var $inputCompanyTel0  = this.$textAreaCompanyTel0.find( 'input' );
			this.$txtHeadCompanyInfo.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditProfile( _.bind( this.callbackEditCompanyInfo, this ),
				{ company_info: {
						0: {
							name: $inputCompanyName0.val(),
							url: $inputCompanyUrl0.val(),
							tel: $inputCompanyTel0.val()
						}
				} }
			);
		} else {
			var $inputCompanyName0 = getInputText( this.$textAreaCompanyName0.text(), 30 );
			this.$textAreaCompanyName0.html( '' );
			this.$textAreaCompanyName0.append( $inputCompanyName0 );

			var $inputCompanyUrl0  = getInputText( this.$textAreaCompanyUrl0.text(),  30 );
			this.$textAreaCompanyUrl0.html( '' );
			this.$textAreaCompanyUrl0.append( $inputCompanyUrl0 );

			var $inputCompanyTel0  = getInputText( this.$textAreaCompanyTel0.text(),  30 );
			this.$textAreaCompanyTel0.html( '' );
			this.$textAreaCompanyTel0.append( $inputCompanyTel0 );

			this.editCompanyInfoFlag = true;
			this.$editCompanyInfoButton.text( this.menu.saveButtonLabel );
		}
	},
	callbackEditCompanyInfo: function ( isSuccess, data ) {
		if ( isSuccess ) {
			this.$textAreaCompanyName0.text( data.company_name0 );
			this.$textAreaCompanyUrl0.text( data.company_url0 );
			this.$textAreaCompanyTel0.text( data.company_tel0 );
			this.editCompanyInfoFlag = false;
			this.$editCompanyInfoButton.text( this.menu.editButtonLabel );
			showToast( this.menu.toastSaveLabel );
		} else {
			if ( !data.status ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 500 ) showToast( this.menu.toastServerErrorLabel );
			if ( data.status == 400 ) showToast( this.menu.toastNotSaveLabel );
		}
		this.$loadingImage.hide();
	},
	callEditProfile: function ( callback, profile ) {
		callJsonRpc( 'ajax/set_profile.php', {
			auth_type: 'signature',
			signature: this.menu.signature,
			profile: profile
		}, callback );
	},
	getMenuUri: function () {
		return '#/profile';
	}
});
