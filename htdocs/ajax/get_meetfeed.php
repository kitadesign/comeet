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
			throw new RuntimeException( 'Invalid call!' );
		}

		$count = $dao->getMemberLikeCount( $memberId );
		if ( $count > 0 ) {
			$meetfeed    = $dao->getMeetFeed( $memberId );
			$tmpSubMeetfeed = $dao->getMeetFeed( $memberId, 1 );
			$self->setData( 'meetfeed', $meetfeed );

			$meetIds = array();
			foreach ( $meetfeed as $row ) $meetIds[$row['member_id']] = $row['member_id'];

			$subMeetfeed = array();
			foreach ( $tmpSubMeetfeed as $row )
				if ( !isset( $meetIds[ $row['member_id'] ] ) ) $subMeetfeed[] = $row;

			$self->setData( 'sub_meetfeed', $subMeetfeed );
		} else {
			$friends = $facebook->getFriends();
			$friends = ( isset($friends['data'] ) ) ? $friends['data'] : array();
			$fbFriendIds = array();
			foreach ( $friends as $friend ) $fbFriendIds[] = $friend['id'];
			$meetfeed = $dao->getMeetFeedByNotLike( $memberId, $fbFriendIds );
			$self->setData( 'meetfeed', $meetfeed );
		}

		$locationIds = $dao->getLocationIdByMemberId( $memberId );
		$self->setData( 'localtion_label', Conf::$LOCATION_ID[ $locationIds[0] ] );
		$meetingTag = $dao->getMeetingTags( $memberId, true );
		$self->setData( 'meeting_tag', ( !empty( $meetingTag ) ) ? $meetingTag->tag_text : '' );

		$self->setData( 'is_like_member', ( $count > 0 ) ? 1 : 0 );
	} catch( RuntimeException $re ) {
		Logger::info( 'get_member_detail', $re->getMessage() );
		$self->badRequestError();
		return;
	} catch ( Exception $e ) {
		Logger::error( 'get_meetfeed', $e->getMessage() );
		throw $e;
	}
});
?>