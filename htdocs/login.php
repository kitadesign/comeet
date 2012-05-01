<?php
require_once('../libs/setup.php');

new PageController(function($self){
	$dao = MemberUpdateDAO::getInstance();
	$facebook = new FacebookAPI();

	try {
		$facebookId = $facebook->getFacebookId();
		if ( !empty( $facebookId ) ) {
			$memberId = $dao->getMemberId ( $facebookId );
			$fbUserProfile = $facebook->getUserInfo();
			if ( empty( $memberId ) ) {
				$res = $dao->createMemberByFacebookId (
					$fbUserProfile['id'],
					$facebook->getAccessToken(),
					( isset($fbUserProfile['locale'] ) ) ? $fbUserProfile['locale'] : '',
					( isset($fbUserProfile['name'] ) )   ? $fbUserProfile['name']   : '',
					( isset($fbUserProfile['bio'] ) )    ? $fbUserProfile['bio']    : ''
				);
			}
			$self->redirect('/');
		}
	} catch( FacebookApiException $fae ) {
		Logger::info(__METHOD__, $fae->getMessage());
	}

	$fbLoginUrl = $facebook->getLoginUrl();
	$self->setData( 'facebook_login_url', $fbLoginUrl );

	$count = $dao->getMemberLikeCountAll();
	$self->setData( 'facebook_app_id', Conf::FACEBOOK_APP_ID );
	$self->setData( 'count', $count );
	$self->setTemplate( 'login' );
});
?>
