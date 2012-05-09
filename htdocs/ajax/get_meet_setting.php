<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * MeetSetting情報を取得
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

		$meetingTags = $dao->getMeetingTags( $memberId );
		$self->setData( 'meeting_tags', $meetingTags );

		$profile = $dao->getMemberProfileForDetail( $memberId );
		$self->setData( 'mtg_profile', $profile->mtg_profile );

		$locationIds = $dao->getLocationIdByMemberId( $memberId );
		$values = array();
		foreach ( $locationIds as $locationId ){
			$values[] = Conf::$LOCATION_ID[$locationId];
		}
		$self->setData( 'location_ids', $values );

		$self->setData( 'location_selecter', Conf::$LOCATION_ID );

	} catch ( Exception $e ) {
		Logger::error( 'get_meetfeed', $e->getMessage() );
		throw $e;
	}
});
?>