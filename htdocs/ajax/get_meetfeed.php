<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * MeetFeed情報を取得
 */
new AjaxController(function($self){
	$dao = MemberDAO::getInstance();
	$facebook = FacebookAPI::getInstance();
	try {
		$facebookId = $facebook->getFacebookId();
		if ( !Validate::isValidFacebookId( $facebookId ) )
			throw new Exception( 'Don\'t login facebook!' );

		$memberId = $dao->getMemberId ( $facebookId );

		if ( !$self->isValidCall( 'memberId', $memberId ) ) {
			Logger::debug( 'get_meetfeed', 'Auth Error' );
			throw new Exception( 'Invalid call!' );
		}

		// TODO: MeetFeed取得
		$meetfeed = array();
		$self->setData( 'meetfeed', $meetfeed );
	} catch ( Exception $e ) {
		Logger::error( 'get_meetfeed', $e->getMessage() );
		throw $e;
	}
});
?>