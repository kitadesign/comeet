<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * メンバーのプロフィール変更
 */
new AjaxController(function($self){
	$dao = MemberUpdateDAO::getInstance();
	$facebook = FacebookAPI::getInstance();
	$updateFlag = false;

	try {
		$facebookId = $facebook->getFacebookId();
		if ( !Validate::isValidFacebookId( $facebookId ) )
			throw new RuntimeException( 'Don\'t login facebook!' );

		$memberId = $dao->getMemberId ( $facebookId );

		if ( !$self->isValidCall( 'memberId', $memberId ) ) {
			Logger::debug( 'set_meet_setting', 'Auth Error' );
			throw new RuntimeException( 'Invalid call!' );
		}
		$meet_setting = $self->getPostData( 'meet_setting' );
		Logger::debug( 'set_meet_setting', $meet_setting );

		if ( isset( $meet_setting['meeting_tags'] ) ) {
			if( !Validate::isValidMeetingTags( $meet_setting['meeting_tags'] ) )
				throw new RuntimeException( 'Param meeting_tags is invalid['.var_export( $meet_setting['meeting_tags'], true).']' );
			$res = $dao->replaceMeetingTag( $memberId, $meet_setting['meeting_tags'], $meet_setting['enable_flg'] );
			if ( !$res ) throw new Exception( 'Update meeting_tags error['.$meet_setting['meeting_tags'].']' );
			$self->setData( 'enable_flg', $meet_setting['enable_flg'] );
			$updateFlag = true;
		}

		if ( isset( $meet_setting['mtg_profile'] ) ) {
			if( !Validate::isValidMeetingProfile( $meet_setting['mtg_profile'] ) )
				throw new RuntimeException( 'Param mtg_profile is invalid['.$meet_setting['mtg_profile'].']' );
			$res = $dao->updateMeetingProfile( $memberId, html( $meet_setting['mtg_profile'] ) );
			if ( !$res ) throw new Exception( 'Update mtg_profile error['.$meet_setting['mtg_profile'].']' );
			$self->setData( 'mtg_profile', html( $meet_setting['mtg_profile'] ) );
			$updateFlag = true;
		}

		if ( isset( $meet_setting['location'] ) ) { // ２つ以上の活動場所を登録するときはこの辺りを修正する
			if( !Validate::isValidMeetingProfile( $meet_setting['location'] ) )
				throw new RuntimeException( 'Param location is invalid['.$meet_setting['location'].']' );
			$res = $dao->updateMemberLocal( $memberId, $meet_setting['location'] );
			if ( !$res ) throw new Exception( 'Update location error['.$meet_setting['location'].']' );
			$self->setData( 'location', @Conf::$LOCATION_ID[$meet_setting['location']] );
			$updateFlag = true;
		}

		if ( $updateFlag ) {
			$self->setData( 'result', 'OK' );
		} else {
			$self->setData( 'result', 'NG' );
		}
	
	} catch( FacebookApiException $fae ) {
		Logger::debug( 'set_meet_setting', $fae->getMessage() );
		$self->setData( 'error', 'Facebook login error.' );
		return;
	} catch( RuntimeException $re ) {
		Logger::debug( 'set_meet_setting', $re->getMessage() );
		$self->setData( 'error', 'Input error.' );
		return;
	} catch( PDOException $e ) {
	} catch ( Exception $e ) {
		Logger::error( 'set_meet_setting', $e->getMessage() );
		throw $e;
	}
});
?>