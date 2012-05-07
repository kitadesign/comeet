<?php
require_once('../libs/setup.php');

new PageController(function($self){
	$dao = MemberDAO::getInstance();
	$facebook = FacebookAPI::getInstance();
	try {
		$facebookId = $facebook->getFacebookId();
		if ( empty( $facebookId ) ) $self->redirect('/login.php');

		$memberId = $dao->getMemberId ( $facebookId );
		$fbUserProfile = $facebook->getUserInfo();

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

	} catch( FacebookApiException $fae ) {
		$self->redirect('/login.php');
	}
	$self->setJavaScript( 'index' );
	$self->setSignature( 'memberId', $memberId );
	$self->setTemplate( 'index' );
});
?>
