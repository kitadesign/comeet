<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * ユーザの詳細情報を取得する
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

		$targetMemberId = $self->getPostData( 'member_id' );
		Logger::debug( __METHOD__, $targetMemberId );
		if ( !Validate::isValidMemberId( $targetMemberId ) )
			throw new RuntimeException( 'Invalid member_id.' );

		// Meetを既読済みにかえる
		$dao->updateActionLead( $memberId );

		$profile      = $dao->getMemberProfileForDetail( $targetMemberId );
		$profile_tags = $dao->getProfileTag( $targetMemberId );
		$meeting_tags = $dao->getMeetingTags( $targetMemberId );
		$toMemberIds  = $dao->getMemberLikeToMe( $targetMemberId );
		$company      = $dao->getMemberCompanyForDetail( $targetMemberId, array(0) );
		$likeMembers  = $dao->getMemberProfileForList( $toMemberIds );
		foreach ( $likeMembers as $likeMember )
			$likeMember->icon = sprintf( Conf::FACEBOOK_IMAGE_URL_S, $dao->getFacebookId( $likeMember->member_id ) );

		$self->setData( 'member_name', $profile->member_name );
		$self->setData( 'icon', sprintf( Conf::FACEBOOK_IMAGE_URL_S, $dao->getFacebookId( $targetMemberId ) ) );
		$self->setData( 'company_email_address', $profile->company_email_address );
		$self->setData( 'member_pr', html( $profile->member_pr ) );
		$self->setData( 'mtg_profile', html( $profile->mtg_profile ) );
		if ( count( $company ) > 0 ) {
			$self->setData( 'company_name', $company[0]->company_name );
			$self->setData( 'company_url', $company[0]->company_url );
			$self->setData( 'company_tel', parseTel( $company[0]->company_tel ) );
		}
		$self->setData( 'has_profile_tags', ( empty( $profile_tags ) ) ? 0 : 1 );
		$self->setData( 'profile_tags', $profile_tags );
		$self->setData( 'has_meeting_tags', ( empty( $meeting_tags ) ) ? 0 : 1 );
		$self->setData( 'meeting_tags', $meeting_tags );
		$self->setData( 'is_like_member', ( empty( $toMemberIds ) ) ? 0 : 1 );
		$self->setData( 'like_count', count( $toMemberIds ) );
		$self->setData( 'like_member', $likeMembers );
	} catch( RuntimeException $re ) {
		Logger::debug( 'get_member_detail', $re->getMessage() );
		$self->badRequestError();
		return;
	} catch ( Exception $e ) {
		Logger::error( __METHOD__, $e->getMessage() );
		throw $e;
	}
});
?>