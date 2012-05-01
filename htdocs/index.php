<?php
require_once('../libs/setup.php');

new PageController(function($self){
	$dao = MemberDAO::getInstance();
	$facebook = new FacebookAPI();
	try {
		$facebookId = $facebook->getFacebookId();
#		if ( empty( $facebookId ) ) $self->redirect('/login.php');

		$memberId = $dao->getMemberId ( $facebookId );
		$fbUserProfile = $facebook->getUserInfo();

	} catch( FacebookApiException $fae ) {
#		$self->redirect('/login.php');
	}
	$self->setTemplate( 'index' );
});
?>
