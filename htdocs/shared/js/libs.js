
function loaded () {
	executeIScroll();
	attendMenu();
}
document.addEventListener( 'DOMContentLoaded', loaded, false );

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

function attendMenu () {
	var $viewport = $('#viewport');
	var $pageContent = $('#pageContent');
	var $main = $('.main');
	var $menu = $('#menu');
	var $menuButton = $('#headerMenu');
	var openFlag = false;
	
	$menuButton.bind('click', function () {
		if (openFlag) {
			$main.removeClass('paddingTop50');
			$viewport.removeClass('viewport');
			$pageContent.removeClass('pageContent');
			$menu.hide();
			openFlag = false;
		} else {
			$main.addClass('paddingTop50');
			$viewport.addClass('viewport');
			$pageContent.addClass('pageContent');
//			$menu.animate({left:'260px'}, 2);
			$menu.show();
			openFlag = true;
		}
	});
}

