(function(){
	function loaded () {
		executeIScroll();
	}
	document.addEventListener( 'DOMContentLoaded', loaded, false );
})();
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
			if (target.tagName != 'SELECT' && target.tagName != 'INPUT' && target.tagName != 'TEXTAREA')
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
	console.log( key );
	console.log( $internalParams.data( key ) );
	return $internalParams.data( key );
}

// Call RPC to Server By JSON data
function callJsonRpc ( url, params, callback ) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		timeout: 10000,
		url: '/'+url,
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

function getLoadingImage () {
	var $img = $(document.createElement('img'));
	$img.attr( 'src', '/shared/images/ajax-loader.gif' );
	$img.attr( 'width', '16px' );
	$img.attr( 'height', '11px' );

	var $div = $(document.createElement('span'));
	$div.addClass('loadingImage');
	$div.append($img);
	return $div;
}

function getInputText ( text, size ) {
	var $input = $(document.createElement('input'));
	$input.attr( 'type', 'text' );
	$input.attr( 'size', size );
	$input.attr( 'value', text );
	return $input;
}

function getTextArea ( text, rows, cols ) {
	var $textarea = $(document.createElement('textarea'));
	$textarea.attr( 'rows', rows );
	$textarea.attr( 'cols', cols );
	$textarea.html( convertLineFeed( text, false ) );
	return $textarea;
}

function redirect ( url ) {
	window.location.href = url;
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
	if ( flag ) {
		text = text.replace(/\r\n/g, '<br />');
		text = text.replace(/(\n|\r)/g, '<br />');
	} else {
		text = text.replace(/<br\ \/>/g, "\n");
		text = text.replace(/<br>/g, "\n");
	}
	return text;
}
