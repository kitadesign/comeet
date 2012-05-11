<?php
require_once('../libs/setup.php');
require_once( CLASSES_DIR . 'PageController.class.php' );

/**
 * ログアウト処理
 */
new PageController(function($self){
	$facebook = FacebookAPI::getInstance();
	$facebookId = $facebook->getFacebookId();
	if ( $facebookId ) {
		session_destroy();
		$logoutUrl = $facebook->getLogoutUrl();
		$self->redirect( $logoutUrl );
	}
	$self->redirect('/');
});
?>
