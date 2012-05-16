<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * MeetFeed情報を取得
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
			Logger::debug( 'get_meetfeed', 'Auth Error' );
			throw new Exception( 'Invalid call!' );
		}

		$count = $dao->getMemberLikeCount( $memberId );
		if ( $count > 0 ) {
			$meetfeed = $dao->getMeetFeed( $memberId );
		} else {
			$friends = $facebook->getFriends();
			$friends = ( isset($friends['data'] ) ) ? $friends['data'] : array();
			$fbFriendIds = array();
			foreach ( $friends as $friend ) $fbFriendIds[] = $friend['id'];
			$meetfeed = $dao->getMeetFeedByNotLike( $memberId, $fbFriendIds );
		}
		usort( $meetfeed, function ( $a, $b ) {
			if ( isset( $a['like_count'] ) && isset( $b['like_count'] ) ) 
				return ( $a['like_count'] < $b['like_count'] ) ? 1 : -1;
			if ( isset( $a['like_count'] ) ) return -1;
			if ( isset( $b['like_count'] ) ) return 1;
			return 0;
		} );

		$self->setData( 'is_like_member', ( $count > 0 ) ? 1 : 0 );
		$self->setData( 'meetfeed', $meetfeed );
	} catch ( Exception $e ) {
		Logger::error( 'get_meetfeed', $e->getMessage() );
		throw $e;
	}
});
?>