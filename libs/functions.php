<?php

function html ($str) {
	$str = htmlspecialchars( $str );
	return $str;
}

function jsGateway ( $key, $template ) {
	$tempString = file_get_contents( TEMPLATES_DIR . $template );
	$head = '<div id="js_gateway_'.$key.'" style="display:none;"><!--';
	$foot = '--></div>';
	$tempString = preg_replace( '[\n|\r|\nr|\t]', '', $tempString );
	$tempString = preg_replace( '/<!--[\s\S]*?-->/', '', $tempString );
	echo( $head . $tempString . $foot );
}

function getSignature ( $name, $base ) {
	return base64_encode( sha1( $name . '_' . $base . Conf::SIGNATURE_SOLT ) );
}

function isMatchSignature ( $name, $base, $signature ) {
	if ( strlen( $name ) == 0 ) return false;
	if ( strlen( $base ) == 0 ) return false;
	if ( strlen( $signature ) == 0 ) return false;
	return ( getSignature( $name, $base ) == $signature );
}
