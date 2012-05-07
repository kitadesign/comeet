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

