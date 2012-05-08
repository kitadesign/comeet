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

		this.$loadingImage = getLoadingImage();
	},
	attend: function () {
		// TODO: アニメーション表示を作り込む
		// TODO: 右フィールドに触れると元に戻るようにする
		this.$menuButton.bind('click', _.bind( function () {
			if (this.openFlag) {
				this.close();
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
			// TODO: ここがおかしい
			// http://zeptojs.com/#Touch events
			if (this.openFlag) {
				this.close();
			}
		}, this ) );

		this.$viewport.swipeLeft( _.bind( function () {
			// TODO: ここがおかしい
			//alert('swipe');
			if (this.openFlag) {
				this.close();
			}
		}, this ) );
	},
	close: function () {
		this.$main.removeClass('paddingTop50');
		this.$viewport.removeClass('viewport');
		this.$pageContent.removeClass('pageContent');
		this.$menu.hide();
		this.removeLoadingImage();
		this.openFlag = false;
	},
	open: function () {
		this.$main.addClass('paddingTop50');
		this.$viewport.addClass('viewport');
		this.$pageContent.addClass('pageContent');
		this.$menu.show();
		this.openFlag = true;
		if (!this.attendFlag) {
			this.setMenu();
			this.attendFlag = true;
		}
		if ( this.meetfeedController.isOpen() )    this.meetfeedController.close();
		if ( this.meetsettingController.isOpen() ) this.meetsettingController.close();
		if ( this.profileController.isOpen() )     this.profileController.close();
		if ( this.friendController.isOpen() )     this.friendController.close();
	},
	setMenu: function () {
		this.$menu_meet_feed.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.meetfeedController.open();
		}, this ) );
		this.$menu_meet_setting.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.meetsettingController.open();
		}, this ) );
		this.$menu_profile.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
			this.profileController.open();
		}, this ) );
		this.$menu_friend.bind('click', _.bind( function ( event ) {
			this.addLoadingImage( $(event.target) );
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
		var signature = getInternalParams('signature');
		callJsonRpc( 'ajax/get_meetfeed.php', {
			auth_type: 'signature',
			signature: signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
		if ( isSuccess ) {
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
	},
	open: function () {
		this.callRpc( _.bind( this.callbackOpen, this ) );
	},
	callRpc: function ( callback ) {
		var signature = getInternalParams('signature');
		callJsonRpc( 'ajax/get_meet_setting.php', {
			auth_type: 'signature',
			signature: signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
		if ( isSuccess ) {
			data.location = data.location_ids[0];
			// TODO: この辺りを作り込む
			var meetSetting = getTemplate( 'meet_setting', {data: data} );
			this.$scroller.html( meetSetting );
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
		var signature = getInternalParams('signature');
		callJsonRpc( 'ajax/get_friends.php', {
			auth_type: 'signature',
			signature: signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
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
		var signature = getInternalParams('signature');
		callJsonRpc( 'ajax/get_profile.php', {
			auth_type: 'signature',
			signature: signature
		}, callback );
	},
	callbackOpen: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var profile = getTemplate( 'profile', {data: data} );
			this.$scroller.html( profile );
			var $editNameButton = this.$scroller.find( 'section#editName p.editBtn a' );
			$editNameButton.bind( 'click', _.bind( this.clickEditName, this ) );
			var $editConmpanyEmailButton = this.$scroller.find( 'section#editCompanyEmail p.editBtn a' );
			$editConmpanyEmailButton.bind( 'click', _.bind( this.clickEditCompanyEmail, this ) );
			var $editPRButton = this.$scroller.find( 'section#editPR p.editBtn a' );
			$editPRButton.bind( 'click', _.bind( this.clickEditPR, this ) );
			var $editProfileTagsButton = this.$scroller.find( 'section#editProfileTags p.editBtn a' );
			$editProfileTagsButton.bind( 'click', _.bind( this.clickEditProfileTags, this ) );
			
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
	},
	clickEditName: function ( event ) {
		var $textArea = this.$scroller.find( 'section#editName p.text' );
		if ( this.editNameFlag ) {
			var $input = $textArea.find( 'input' );
			var $txtHead = this.$scroller.find( 'section#editName h2.txtHead' );
			$txtHead.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditProfile( _.bind( this.callbackEditName, this ), {member_name: $input.val()} );
		} else {
			var text = getInputText( $textArea.text(), 40 );
			$textArea.html('');
			$textArea.append(text);
			this.editNameFlag = true;
		}
	},
	callbackEditName: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var $input = this.$scroller.find( 'section#editName p.text input' );
			var $textArea = this.$scroller.find( 'section#editName p.text' );
			this.menu.setMemberName( data.member_name );
			$textArea.text( data.member_name );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
		this.editNameFlag = false;
	},
	clickEditCompanyEmail: function ( event ) {
		var $textArea = this.$scroller.find( 'section#editCompanyEmail p.text' );
		if ( this.editCompanyEmailFlag ) {
			var $input = $textArea.find( 'input' );
			var $txtHead = this.$scroller.find( 'section#editCompanyEmail h2.txtHead' );
			$txtHead.append( this.$loadingImage );
			this.$loadingImage.show();
			this.callEditProfile( _.bind( this.callbackEditCompanyEmail, this ),
				{company_email_address: ( $input.val() ) ? $input.val() : ''}
			);
		} else {
			var text = getInputText( $textArea.text(), 40 );
			$textArea.html('');
			$textArea.append(text);
			this.editCompanyEmailFlag = true;
		}
	},
	callbackEditCompanyEmail: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var $input = this.$scroller.find( 'section#editCompanyEmail p.text input' );
			var $textArea = this.$scroller.find( 'section#editCompanyEmail p.text' );
			$textArea.text( data.company_email_address );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
		this.editCompanyEmailFlag = false;
	},
	clickEditPR: function ( event ) {
		var $txtHead = this.$scroller.find( 'section#editPR h2.txtHead' );
		$txtHead.append( this.$loadingImage );
		this.$loadingImage.show();
		this.callEditProfile( _.bind( this.callbackEditPR, this ), {member_pr: 'update'});
	},
	callbackEditPR: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var $textArea = this.$scroller.find( 'section#editPR p.text' );
			$textArea.text( data.member_pr );
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	clickEditProfileTags: function ( event ) {
		var $section = this.$scroller.find( 'section#editProfileTags' );
		if ( this.editProfileTagsFlag ) {
			var $list = $section.find( 'ul li input' );
			var $txtHead = $section.find( 'h2.txtHead' );
			$txtHead.append( this.$loadingImage );
			this.$loadingImage.show();
			var tags = [];
			_.each( $list, function ( value, key ) {
				tags[key] = $(value).val();
			});
			this.callEditProfile( _.bind( this.callbackEditProfileTags, this ), {profile_tags: tags});
		} else {
			var $list = $section.find( 'ul li' );
			_.each( $list, function ( value ) {
				var $input = getInputText( $(value).text(), 10 );
				$(value).html( $input );
			});
			this.editProfileTagsFlag = true;
		}
	},
	callbackEditProfileTags: function ( isSuccess, data ) {
		if ( isSuccess ) {
			var $section = this.$scroller.find( 'section#editProfileTags' );
			var $list = $section.find( 'ul li' );
			_.each( $list, function ( value ) {
				var $input = $(value).find( 'input' );
				$(value).text( $input.val() );
			});
			this.editProfileTagsFlag = false;
		} else {
			console.log('TODO: Error handling');
		}
		this.$loadingImage.hide();
	},
	callEditProfile: function ( callback, profile ) {
		var signature = getInternalParams('signature');
		callJsonRpc( 'ajax/set_profile.php', {
			auth_type: 'signature',
			signature: signature,
			profile: profile
		}, callback );
	}
});
