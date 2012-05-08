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
			throw new Exception( 'Don\'t login facebook!' );

		$memberId = $dao->getMemberId ( $facebookId );

		if ( !$self->isValidCall( 'memberId', $memberId ) ) {
			Logger::debug( 'set_profile', 'Auth Error' );
			throw new Exception( 'Invalid call!' );
		}
		$profile = $self->getPostData( 'profile' );
		Logger::debug( 'set_profile', $profile );

		if ( isset( $profile['member_name'] ) ) {
			if( !Validate::isValidMemberName( $profile['member_name'] ) )
				throw new Exception( 'Param member_name is invalid['.$profile['member_name'].']' );

			$res = $dao->updateMemberName( $memberId, $profile['member_name'] );
			if ( !$res ) throw new Exception( 'Update member_name error['.$profile['member_name'].']' );
			$self->setData( 'member_name', $profile['member_name'] );
			$updateFlag = true;
		}

		if ( isset( $profile['company_email_address'] ) ) {
			if( !Validate::isValidCompanyEmailAddress( $profile['company_email_address'] ) )
				throw new Exception( 'Param company_email_address is invalid['.$profile['company_email_address'].']' );

			$res = $dao->updateCompanyEmailAddress( $memberId, $profile['company_email_address'] );
			if ( !$res ) throw new Exception( 'Update company_email_address error['.$profile['company_email_address'].']' );
			$self->setData( 'company_email_address', $profile['company_email_address'] );
			$updateFlag = true;
		}

		if ( isset( $profile['member_pr'] ) && $profile['member_pr'] == 'update' ) {
			$fbUserProfile = $facebook->getUserInfo();
			$memberPR = ( isset($fbUserProfile['bio'] ) ) ? $fbUserProfile['bio'] : '';
			$res = $dao->updateMemberPR( $memberId, $memberPR );
			if ( !$res ) throw new Exception( 'Update member_pr error['.$memberPR.']' );
			$self->setData( 'member_pr', $memberPR );
			$updateFlag = true;
		}

		if ( isset( $profile['profile_tags'] ) ) {
			if ( is_array( $profile['profile_tags'] ) && count( $profile['profile_tags'] ) > 0 ) {
				$profileTags = array();
				foreach ( $profile['profile_tags'] as $tag ){
					$profileTags[] = $tag;
					if( count( $profileTags ) >= 3 ) break;
				}
				$res = $dao->replaceProfileTag( $memberId, $profileTags );
				if ( !$res ) throw new Exception( 'Update profileTags error['.var_export($profileTags,true).']' );
				$self->setData( 'profile_tags', $profileTags );
			} else {
				throw new Exception( 'ProfileTags are empty!' );
			}
		}

		if ( $updateFlag ) {
			$self->setData( 'result', 'OK' );
		} else {
			$self->setData( 'result', 'NG' );
		}
	} catch ( Exception $e ) {
		Logger::error( 'set_profile', $e->getMessage() );
		throw $e;
	}
});
?>