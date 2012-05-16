<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * Meetなう登録
 */
new AjaxController(function($self){
	$dao = MemberUpdateDAO::getInstance();
	$facebook = FacebookAPI::getInstance();
	try {
		$facebookId = $facebook->getFacebookId();
		if ( !Validate::isValidFacebookId( $facebookId ) )
			throw new Exception( 'Don\'t login facebook!' );

		$memberId = $dao->getMemberId ( $facebookId );

		if ( !$self->isValidCall( 'memberId', $memberId ) ) {
			Logger::debug(__METHOD__, 'Auth Error');
			throw new Exception( 'Invalid call!' );
		}
		$toMemberId = $self->getPostData( 'to_member_id' );
		Logger::debug(__METHOD__, $toMemberId);
		if ( !Validate::isValidMemberId( $toMemberId ) )
			throw new Exception( 'Param to_member_id is invalid['.$toMemberId.']' );

		$res = $dao->createActionNow( $memberId, $toMemberId, 0 );
		if ( !$res ) throw new Exception( 'Create action meet now error['.$toMemberId.']' );

		$profile = $dao->getMemberProfileForDetail( $toMemberId );
		$res = $facebook->requestMeetNow( $dao->getFacebookId( $toMemberId ), $profile->member_name );
		if ( !$res ) throw new Exception( 'Request meet now error['.$profile->member_name.']' );

		$self->setData( 'result', 'OK' );
	} catch( FacebookApiException $fae ) {
		Logger::debug( 'set_meet_now', $fae->getMessage() );
		$self->setData( 'error', 'Facebook login error.' );
		return;
	} catch( RuntimeException $re ) {
		Logger::debug( 'set_meet_now', $re->getMessage() );
		$self->setData( 'error', 'Input error.' );
		return;
	} catch( PDOException $e ) {
	} catch ( Exception $e ) {
		Logger::error( __METHOD__, $e->getMessage() );
		throw $e;
	}
});
?>
