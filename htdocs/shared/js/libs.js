(function(){
	function loaded () {
//		executeIScroll();
	}
	document.addEventListener( 'DOMContentLoaded', loaded, false );
})();
window.onload = function () {
	setTimeout( function() {
		window.scrollTo(0, 1);
	}, 100 );
};

var myScroll;
function executeIScroll () {
	var scroller = document.getElementById('scroller');
	myScroll = new iScroll('wrapper', {
		snap: scroller,
		momentum: false,
		hScrollbar: false,
		vScrollbar: false,
		useTransform: false,
		checkDOMChanges: true,
		onBeforeScrollStart: function (e) {
			var target = e.target;
			while (target.nodeType != 1) target = target.parentNode;
			if (target.tagName != 'SELECT' && target.tagName != 'INPUT' && target.tagName != 'TEXTAREA' && target.tagName != 'A')
				e.preventDefault();
		}
	});
}

// Get template from JS gateway
function getTemplate ( key, params ) {
	var $template = $( '#js_gateway_' + key );
	if ( !$template ) return false;
	if ( !$template.html() ) return false;

	var tempString = $template.html();
	tempString = tempString.replace( /<!--/, '' );
	tempString = tempString.replace( /-->/, '' );

	return _.template( tempString, params );
}

// Get Internal Params
function getInternalParams ( key ) {
	if ( key == '' ) return;
	var $internalParams = $( '#internal_params' );
	return $internalParams.data( key );
}

// Call RPC to Server By JSON data
function callJsonRpc ( url, params, callback ) {
	var unixtime = getUnixTime();
	$.ajax({
		type: 'POST',
		dataType: 'json',
		timeout: 10000,
		url: '/'+url+'?'+unixtime,
		data: params,
		success: function ( data ) {
			console.log(arguments);
			callback( true, data );
		},
		error: function(xhr, type){
			console.log(arguments);
			callback( false, xhr );
		}
	});
}

var $_loadingImg = $(document.createElement('img'));
$_loadingImg.attr( 'src', '/shared/images/ajax-loader.gif' );
$_loadingImg.attr( 'width', '16px' );
$_loadingImg.attr( 'height', '11px' );
var $_loadingImgDiv = $(document.createElement('span'));
$_loadingImgDiv.addClass('loadingImage');
$_loadingImgDiv.append($_loadingImg);
function getLoadingImage () {
	return $_loadingImgDiv.clone();
}

function getInputText ( text, size ) {
	var $input = $( document.createElement('input') );
	$input.attr( 'type', 'text' );
	$input.attr( 'size', size );
	$input.attr( 'value', text );
	$input.on( 'focus', function ( event ) {
		event.stopPropagation();
	} );
	return $input;
}

function getTextArea ( text, rows, cols ) {
	var $textarea = $(document.createElement('textarea'));
	$textarea.attr( 'rows', rows );
	$textarea.attr( 'cols', cols );
	$textarea.on( 'focus', function ( event ) {
		event.stopPropagation();
	} );
	$textarea.html( convertLineFeed( text, false ) );
	return $textarea;
}

function redirect ( url ) {
	window.location.replace( url );
}

function defineClass () {
	var properties = _.toArray(arguments);
	var klass = function(){
		this.initialize.apply( this,arguments );
	};
	for ( var i=0,l=properties.length;i<l;i++ ) {
		for ( var property in properties[i] ) {
			klass.prototype[property] = properties[i][property];
		}
	}
	if( !klass.prototype.initialize ){
		klass.prototype.initialize = function(){};
	}
	klass.prototype.constructor = klass;
	return klass;
}

function convertLineFeed ( text, flag ) {
	if ( !text ) return '';
	if ( flag ) {
		text = text.replace(/\r\n/g, '<br />');
		text = text.replace(/(\n|\r)/g, '<br />');
	} else {
		text = text.replace(/<br\ \/>/g, "\n");
		text = text.replace(/<br>/g, "\n");
	}
	return text;
}

var _toastOpenFlag = false;
var _toastOpenText = '';
function showToast ( text, mtime ) {
	if ( _toastOpenFlag ) {
		if ( _toastOpenText == text ) {
			return;
		} else {
			while( _toastOpenFlag ) {
				console.log('wait');
			}
		}
	}
	if ( !mtime ) mtime = 1500;
	$_toastTemplate = $( getTemplate( 'toast', {text:text} ) );
	$('#viewport').after( $_toastTemplate );
	_toastOpenFlag = true;
	_toastOpenText = text;
	setInterval( function () {
		$_toastTemplate.remove();
		_toastOpenFlag = false;
		_toastOpenText = '';
	}, mtime );
}

function sleep(callback, time){
	setTimeout(callback, time);
}

function getUnixTime () {
	return ~~(new Date/1000);
}