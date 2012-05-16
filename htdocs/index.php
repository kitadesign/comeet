<?php
require_once('../libs/setup.php');
require_once( CLASSES_DIR . 'PageController.class.php' );

/**
 * トップ画面
 */
new PageController(function($self){
	$dao = MemberDAO::getInstance();
	$facebook = FacebookAPI::getInstance();
	try {
		$facebookId = $facebook->getFacebookId();
		if ( !Validate::isValidFacebookId( $facebookId ) ) $self->redirect('/login.php');

		$memberId = $dao->getMemberId ( $facebookId );
		$fbUserProfile = $facebook->getUserInfo(); // TODO: これ必要？

		$locationIds = $dao->getLocationIdByMemberId( $memberId );
		if ( empty( $locationIds ) || count( $locationIds ) == 0 ) {
			$likeCount = $dao->getMemberLikeCount ( $memberId );
			$self->setJavaScript( 'set_local' );
			$self->setSignature( 'memberId', $memberId );
			$self->setData( 'is_like', ( $likeCount > 0 ) ? true : false );
			$self->setData( 'location_ids', Conf::$LOCATION_ID );
			$self->setTemplate( 'set_local' );
			return;
		}

		$profile = $dao->getMemberProfileForDetail( $memberId );
		if ( empty( $profile ) ){
			// TODO: 本来エラーの挙動なのでErrorにしてしまっていいのでは？
			$name = ( isset( $fbUserProfile['name'] ) ) ? $fbUserProfile['name'] : '';
		} else {
			$name = $profile->member_name;
		}
		$self->setData( 'member_name', html( $name ) );
		$self->setData( 'facebook_id', $facebookId );

		$count = $dao->getMemberLikeCount( $memberId );
		$self->setData( 'like_count', $count );

	} catch( FacebookApiException $fae ) {
		$self->redirect('/login.php');
	}
	$self->setJavaScript( 'index' );
	$self->setSignature( 'memberId', $memberId );
	$self->setInternalParams( 'member-id', $memberId );
	$self->setInternalParams( 'facebook-id', $facebookId );
	$self->setInternalParams( 'edit-button', Conf::JA_EDIT_BUTTON );
	$self->setInternalParams( 'save-button', Conf::JA_SAVE_BUTTON );
	$self->setInternalParams( 'meet-now-button', Conf::JA_MEET_NOW_BUTTON );
	$self->setInternalParams( 'facebook-sync', Conf::JA_FACEBOOK_SYNC );
	$self->setInternalParams( 'image-url-s', Conf::FACEBOOK_IMAGE_URL_S );
	$self->setInternalParams( 'default-image-url', Conf::DEFAULT_IMAGE_URL );
	$self->setInternalParams( 'node-name', Conf::FACEBOOK_ID_NODE );
	$self->setInternalParams( 'toast-save-label', Conf::TOAST_SAVED );
	$self->setInternalParams( 'toast-not-save-label', Conf::TOAST_NOT_SAVE );
	$self->setInternalParams( 'toast-server-error-label', Conf::TOAST_SERVER_ERROR );
	$self->setInternalParams( 'toast-not-get-label', Conf::TOAST_NOT_GET );
	$self->setInternalParams( 'toast-not-connect-label', Conf::TOAST_NOT_CONNECT );
	$self->setInternalParams( 'toast-request-meet-now-label', Conf::TOAST_REQUEST_MEET_NOW );
	$self->setTemplate( 'index' );
});
?>
