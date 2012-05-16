<?php
require_once('../../libs/setup.php');
require_once( CLASSES_DIR . 'AjaxController.class.php' );

/**
 * 推奨取締役に選ぶ友人情報を取得
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
			Logger::debug( 'get_friends', 'Auth Error' );
			throw new Exception( 'Invalid call!' );
		}

		$friends = $facebook->getFriends();
		$friends = ( isset($friends['data'] ) ) ? $friends['data'] : array();

		$fbFriendIds = array();
		$fbFriends = array();
		foreach ( $friends as $friend ){
			$fbFriendIds[] = $friend['id'];
			$fbFriends[$friend['id']] = $friend;
		}
		$members = $dao->getMembersByFacebookIds( $fbFriendIds );

		foreach ( $members as $member ) $memberIds[] = $member->member_id;
		$likeList = $dao->getMemberLikeCountByMemberIds( $memberIds );

		foreach ( $members as $member ){
			if ( isset( $fbFriends[$member->facebook_id] ) ) {
				$fbFriends[$member->facebook_id]['like_count'] = $likeList[$member->member_id];
				$fbFriends[$member->facebook_id]['member_id']  = $member->member_id;
			}
		}

		usort( $fbFriends, function ( $a, $b ) {
			if ( isset( $a['member_id'] ) && isset( $b['member_id'] ) ) 
				return ( $a['like_count'] < $b['like_count'] ) ? 1 : -1;
			if ( isset( $a['member_id'] ) ) return -1;
			if ( isset( $b['member_id'] ) ) return 1;
			return 0;
		} );

		$myFrineds = array();
		$memberLikes = $dao->getMemberLikeFromMe( $memberId );
		foreach ( $memberLikes as $memberLike ) {
			if ( empty( $memberLike->to_facebook_id ) ) {
				$facebookId = $dao->getFacebookId( $memberLike );
			} else {
				$facebookId = $memberLike->to_facebook_id;
			}
			$friend = array(
				'id'   => $facebookId,
				'name' => $fbFriends[$facebookId]['name']
			);
			$myFrineds[] = $friend;
		}

		$self->setData( 'fb_friends', $fbFriends );
		$self->setData( 'my_frineds', $myFrineds );
	} catch ( Exception $e ) {
		Logger::error( 'get_friends', $e->getMessage() );
		throw $e;
	}
});
?>