(function(){
	function loaded () {
		executeIScroll();
	}
	document.addEventListener( 'DOMContentLoaded', loaded, false );
})();

function executeIScroll () {
	var scroller = document.getElementById('scroller');
	myScroll = new iScroll('wrapper', {
		snap: scroller,
		momentum: false,
		hScrollbar: false,
		vScrollbar: false,
		useTransform: false,
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
	return $internalParams.attr( 'data-' + key );
}

// Call RPC to Server By JSON data
function callJsonRpc ( url, params, callback ) {
	$.ajax({
		type: 'POST',
		dataType: 'json',
		timeout: 300,
		url: '/'+url,
		data: params,
		success: function ( data ) {
			callback( true, data );
		},
		error: function(xhr, type){
			callback( false, xhr );
		}
	});
}
