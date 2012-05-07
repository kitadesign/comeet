(function(){
function loaded () {
	executeIScroll();
	
	MenuContainer.attendMenu();
}
document.addEventListener( 'DOMContentLoaded', loaded, false );
$('body').bind('unload', function(){
	// TODO: ここも出来るか確認する　http://yabooo.org/archives/180
	alert('backtest');
});
})();

function executeIScroll () {
	var scroller = document.getElementById('scroller');
	myScroll = new iScroll('wrapper', {
		snap: scroller,
		momentum: false,
		hScrollbar: false,
		vScrollbar: false,
		y: 50,
		//add
		useTransform: false,
		onBeforeScrollStart: function (e) {
			var target = e.target;
			while (target.nodeType != 1) target = target.parentNode;
			if (target.tagName != 'SELECT' && target.tagName != 'INPUT' && target.tagName != 'TEXTAREA')
				e.preventDefault();
		}
	 });
}
var MenuContainer = {};
MenuContainer.init = function () {
	this.$viewport    = $('#viewport');
	this.$pageContent = $('#pageContent');
	this.$main        = $('.main');
	this.$menu        = $('#menu');
	this.$menuButton  = $('#headerMenu');
	this.$scroller    = $('#scroller');
	this.openFlag     = false;

	this.$menu_meet_setting = $('#menu_meet_setting');
	this.$menu_meet_feed    = $('#menu_meet_feed');
	this.$menu_friend       = $('#menu_friend');
	this.$menu_profile      = $('#menu_profile');
};
MenuContainer.attendMenu = function () {
	this.init();
	// TODO: アニメーション表示を作り込む
	// TODO: 右フィールドに触れると元に戻るようにする
	this.$menuButton.bind('click', function () {
		if (this.openFlag) {
			this.close();
		} else {
			this.open();
		}
	}.bind(this));
};
MenuContainer.close = function () {
	this.$main.removeClass('paddingTop50');
	this.$viewport.removeClass('viewport');
	this.$pageContent.removeClass('pageContent');
	this.$menu.hide();
	this.openFlag = false;
};
MenuContainer.open = function () {
	this.$main.addClass('paddingTop50');
	this.$viewport.addClass('viewport');
	this.$pageContent.addClass('pageContent');
	this.$menu.show();
	this.openFlag = true;
	this.setMenu();
};
MenuContainer.setMenu = function () {
	// TODO: メニューの挙動を作り込む
	this.$menu_meet_setting.bind('click', function () {
		var meetSetting = getTemplate('meet_setting', 
			{
				meetingTag1:1,
				meetingTag2:2,
				meetingTag3:3,
				meetingProfile:'aaa',
				location: 'eee'
			}
		);
		this.$scroller.html(meetSetting);
		this.close();
	}.bind(this));
	this.$menu_meet_feed.bind('click', function () {
		var meetFeed = getTemplate('meet_feed', 
			{　members: [　{　icon: '',　name: 'ddd',　company: 'ccc',　post: 'bbb',　commetn: 'aaa'}]}
		);
		this.$scroller.html(meetFeed);
		this.close();
	}.bind(this));
	this.$menu_friend.bind('click', function () {
		var friend = getTemplate('friend', {});
		this.$scroller.html(friend);
		this.close();
	}.bind(this));
	this.$menu_profile.bind('click', function () {
		var profile = getTemplate('profile', {});
		this.$scroller.html(profile);
		this.close();
	}.bind(this));
	this.$pageContent.tap(function () {
		// TODO: ここ出来たら対応する
		// http://zeptojs.com/#Touch events
		if (this.openFlag) {
			this.close();
		}
	});
}
// Get template from JS gateway
function getTemplate ( key, params ) {
	var $template = $('#js_gateway_' + key);
	if ( !$template ) return false;
	if ( !$template.html() ) return false;

	var tempString = $template.html();
	tempString = tempString.replace(/<!--/,'');
	tempString = tempString.replace(/-->/,'');

	return _.template( tempString, params );
}
