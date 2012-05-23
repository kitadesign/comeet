<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * プロフィール情報を取得する
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
			Logger::debug(__METHOD__, 'Auth Error');
			throw new RuntimeException( 'Invalid call!' );
		}

		$profile = $dao->getMemberProfileForDetail( $memberId );
		$self->setData( 'member_name', $profile->member_name );
		$self->setData( 'company_email_address', $profile->company_email_address );

		$member_pr = nl2br( html( $profile->member_pr ) );
		$member_pr = str_replace(array("\r\n","\r","\n"), '', $member_pr);
		$self->setData( 'member_pr', $member_pr );

		$likeCount = $dao->getMemberLikeCount( $memberId );
		$self->setData( 'like_count', $likeCount );

		$profileTags = $dao->getProfileTag( $memberId );
		foreach ( $profileTags as $key => $profileTag ) {
			$self->setData( 'profile_tag' . ($key + 1), $profileTag );
		}

		$company = $dao->getMemberCompanyForDetail( $memberId, array(0) );
		if ( count($company) > 0 ) {
			foreach ( $company as $key => $companyInfo ) {
				$self->setData( 'company_name'.$key, $companyInfo->company_name );
				$self->setData( 'company_url'.$key,  $companyInfo->company_url );
				$self->setData( 'company_tel'.$key,  $companyInfo->company_tel );
			}
		}
	} catch( RuntimeException $re ) {
		Logger::debug( 'get_profile', $re->getMessage() );
		$self->badRequestError();
		return;
	} catch ( Exception $e ) {
		Logger::error( __METHOD__, $e->getMessage() );
		throw $e;
	}
});
?>