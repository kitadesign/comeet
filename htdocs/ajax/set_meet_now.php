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
			throw new RuntimeException( 'Invalid call!' );
		}

		$toMemberId = $self->getPostData( 'to_member_id' );
		$meetText   = $self->getPostData( 'meet_text' );

		if ( !Validate::isValidMemberId( $toMemberId ) )
			throw new RuntimeException( 'Param to_member_id is invalid['.$toMemberId.']' );

		$fromMemberProfileTags = $dao->getProfileTag( $memberId );
		$fromMemberMeetingTag  = $dao->getMeetingTags( $memberId, true );
		$fromMemberProfile     = $dao->getMemberProfileForDetail( $memberId );
		$fromMemberCompany     = $dao->getMemberCompanyForDetail( $memberId, array(0) );
		$toMemberProfile       = $dao->getMemberProfileForDetail( $toMemberId );

		$res = $dao->createActionNow( $memberId, $toMemberId, $meetText );
		if ( !$res ) throw new Exception( 'Create action meet now error['.$toMemberId.']' );

		$res = $facebook->requestMeetNow( $dao->getFacebookId( $toMemberId ), $toMemberProfile->member_name, $fromMemberMeetingTag->tag_text );
		if ( !$res ) throw new Exception( 'Request meet now error['.$toMemberProfile->member_name.']' );

		$mailer = Mailer::getInstance();
		$res = $mailer->sendJPMeetNow(
			$facebookId,
			$fromMemberProfile->company_email_address,
			$fromMemberProfile->member_name,
			$fromMemberProfile->member_pr,
			( isset( $fromMemberCompany[0] ) )? $fromMemberCompany[0]->company_name : '',
			( isset( $fromMemberCompany[0] ) )? $fromMemberCompany[0]->company_url  : '',
			( isset( $fromMemberCompany[0] ) )? $fromMemberCompany[0]->company_tel  : '',
			$toMemberProfile->company_email_address,
			$toMemberProfile->member_name,
			$fromMemberMeetingTag->tag_text,
			$fromMemberProfileTags,
			html( trim( $meetText ) )
		);
		if ( !$res ) throw new RuntimeException( 'Send meet now error['.$toMemberProfile->member_name.']' );

		$self->setData( 'result', 'OK' );
	} catch( FacebookApiException $fae ) {
		Logger::debug( 'set_meet_now', $fae->getMessage() );
		$self->setData( 'error', 'Facebook login error.' );
		return;
	} catch( RuntimeException $re ) {
		Logger::debug( 'set_meet_now', $re->getMessage() );
		$self->badRequestError();
		return;
	} catch( PDOException $e ) {
	} catch ( Exception $e ) {
		Logger::error( __METHOD__, $e->getMessage() );
		throw $e;
	}
});
?>
