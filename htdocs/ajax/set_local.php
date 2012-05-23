<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * 活動場所登録
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
			throw new RuntimeException( 'Invalid call!' );
		}
		$locationId = $self->getPostData( 'location_id' );
		Logger::debug(__METHOD__, $locationId);
		if ( !Validate::isValidLocationId( $locationId ) )
			throw new RuntimeException( 'Param locationId is invalid['.$locationId.']' );

		$res = $dao->createMemberLocal( $memberId, $locationId );
		if ( !$res ) throw new Exception( 'Create location error['.$locationId.']' );
		$self->setData( 'result', 'OK' );
	} catch( FacebookApiException $fae ) {
		Logger::debug( 'set_profile', $fae->getMessage() );
		$self->setData( 'error', 'Facebook login error.' );
		return;
	} catch( RuntimeException $re ) {
		Logger::debug( 'set_profile', $re->getMessage() );
		$self->badRequestError();
		return;
	} catch( PDOException $e ) {
	} catch ( Exception $e ) {
		Logger::error( __METHOD__, $e->getMessage() );
		throw $e;
	}
});
?>