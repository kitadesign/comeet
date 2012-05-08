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

		// TODO: MeetFeed取得
		$list = array();
		$self->setData( 'meet_count', count( $list ) );
		$self->setData( 'meet_list', $list );

		$profile = $dao->getMemberProfileForDetail( $memberId );
		if ( empty( $profile ) ){
			// TODO: 本来エラーの挙動なのでErrorにしてしまっていいのでは？
			$name = ( isset( $fbUserProfile['name'] ) ) ? $fbUserProfile['name'] : '';
		} else {
			$name = $profile->member_name;
		}
		$self->setData( 'member_name', $name );

		$self->setData( 'facebook_id', $facebookId );

	} catch( FacebookApiException $fae ) {
		$self->redirect('/login.php');
	}
	$self->setJavaScript( 'index' );
	$self->setSignature( 'memberId', $memberId );
	$self->setTemplate( 'index' );
});
?>
