<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * 推奨取締役の登録
 */
new AjaxController(function($self){
	$dao = MemberUpdateDAO::getInstance();
	$facebook = FacebookAPI::getInstance();
	$updateFlag = false;
	try {
		$facebookId = $facebook->getFacebookId();
		if ( !Validate::isValidFacebookId( $facebookId ) )
			throw new Exception( 'Don\'t login facebook!' );

		$memberId = $dao->getMemberId ( $facebookId );

		if ( !$self->isValidCall( 'memberId', $memberId ) ) {
			Logger::debug(__METHOD__, 'Auth Error');
			throw new Exception( 'Invalid call!' );
		}

		$friends  = $self->getPostData( 'friends' );
		if ( $friends == null ) $friends = array();
		$nodeName = $self->getPostData( 'node_name' );
		Logger::debug(__METHOD__, $friends);
		if ( !Validate::inValidFriends( $friends ) )
			throw new Exception( 'Param friends is invalid['.var_export( $friends, true ).']' );

		if ( $nodeName == Conf::FACEBOOK_ID_NODE ) {
			$res = $dao->updateMemberLikeByFacebookIds( $memberId, $friends );
			if ( !$res ) throw new Exception( 'Create friends error['.var_export( $friends, true ).']' );
			$updateFlag = true;
		}

		if ( $updateFlag ) {
			$self->setData( 'result', 'OK' );
		} else {
			$self->setData( 'result', 'NG' );
		}
	} catch( FacebookApiException $fae ) {
		Logger::debug( 'set_friends', $fae->getMessage() );
		$self->setData( 'error', 'Facebook login error.' );
		return;
	} catch( RuntimeException $re ) {
		Logger::debug( 'set_friends', $re->getMessage() );
		$self->setData( 'error', 'Input error.' );
		return;
	} catch( PDOException $e ) {
	} catch ( Exception $e ) {
		Logger::error( __METHOD__, $e->getMessage() );
		throw $e;
	}
});
?>