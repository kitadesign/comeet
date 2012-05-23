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
			throw new RuntimeException( 'Invalid call!' );
		}
		$profile = $self->getPostData( 'profile' );
		Logger::debug( 'set_profile', $profile );

		if ( isset( $profile['member_name'] ) ) {
			if( !Validate::isValidMemberName( $profile['member_name'] ) )
				throw new RuntimeException( 'Param member_name is invalid['.$profile['member_name'].']' );

			$res = $dao->updateMemberName( $memberId, trim( $profile['member_name'] ) );
			if ( !$res ) throw new Exception( 'Update member_name error['.$profile['member_name'].']' );
			$self->setData( 'member_name', $profile['member_name'] );
			$updateFlag = true;
		}

		if ( isset( $profile['company_email_address'] ) ) {
			if( !Validate::isValidCompanyEmailAddress( $profile['company_email_address'] ) )
				throw new RuntimeException( 'Param company_email_address is invalid['.$profile['company_email_address'].']' );

			$res = $dao->updateCompanyEmailAddress( $memberId, $profile['company_email_address'] );
			if ( !$res ) throw new Exception( 'Update company_email_address error['.$profile['company_email_address'].']' );
			$self->setData( 'company_email_address', $profile['company_email_address'] );
			$updateFlag = true;
		}

		if ( isset( $profile['member_pr'] ) ) {
			if( !Validate::isValidMemberPR( $profile['member_pr'] ) )
				throw new RuntimeException( 'Param member_pr is invalid['.$profile['member_pr'].']' );
			$res = $dao->updateMemberPR( $memberId, trim( $profile['member_pr'] ) );
			if ( !$res ) throw new Exception( 'Update member_pr error['.$profile['member_pr'].']' );
			$self->setData( 'member_pr', html( $profile['member_pr'] ) );
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
				$updateFlag = true;
			} else {
				throw new RuntimeException( 'ProfileTags are empty!' );
			}
		}

		if ( isset( $profile['company_info'] ) ) {
			if ( is_array( $profile['company_info'] ) && count( $profile['company_info'] ) > 0 ) {

				$res = $dao->replaceCompanyInfo( $memberId, $profile['company_info'] );
				if ( !$res ) throw new Exception( 'Update companyInfo error['.var_export($profile['company_info'],true).']' );

				foreach ( $profile['company_info'] as $key => $companyInfo ){
					$self->setData( 'company_name'.$key, trim( $companyInfo['name'] ) );
					$self->setData( 'company_url'.$key,  trim( $companyInfo['url'] ) );
					$self->setData( 'company_tel'.$key,  trim( $companyInfo['tel'] ) );
				}
				$updateFlag = true;
			} else {
				throw new RuntimeException( 'CompanyInfo are empty!' );
			}
		}

		if ( $updateFlag ) {
			$self->setData( 'result', 'OK' );
		} else {
			$self->setData( 'result', 'NG' );
		}
	} catch( FacebookApiException $fae ) {
		Logger::debug( 'set_profile', $fae->getMessage() );
		$self->setData( 'error', 'Facebook login error.' );
		return;
	} catch( RuntimeException $re ) {
		Logger::debug( 'set_profile', $re->getMessage() );
		$self->badRequestError();
		return;
	} catch( PDOException $pe ) {
	} catch ( Exception $e ) {
		Logger::error( 'set_profile', $e->getMessage() );
		throw $e;
	}
});
?>