<?php

/**
 * DAO::MemberDAO
 * MemberDBへのSQLを実行する
 */
class MemberDAO extends LogDAO
{

	/**
	 * FacebookIDのバインド変数
	 */
	const BIND_FACEBOOK_ID           = ':FACEBOOK_ID';
	const BIND_LOCATION_ID           = ':LOCATION_ID';
	const BIND_MEMBER_ID             = ':MEMBER_ID';
	const BIND_TO_MEMBER_ID          = ':TO_MEMBER_ID';
	const BIND_FROM_MEMBER_ID        = ':FROM_MEMBER_ID';
	const BIND_FACEBOOK_ACCESS_TOKEN = ':FACEBOOK_ACCESS_TOKEN';
	const BIND_LANGUAGE              = ':LANGUAGE';
	const BIND_MEMBER_NAME           = ':MEMBER_NAME';
	const BIND_MEMBER_PR             = ':MEMBER_PR';
	const BIND_TO_FACEBOOK_ID        = ':TO_FACEBOOK_ID';
	const BIND_KEY_NUMBER            = ':KEY_NUMBER';
	const BIND_TAG_TEXT              = ':TAG_TEXT';
	const BIND_ENABLE_FLG            = ':ENABLE_FLG';
	const BIND_MTG_PROFILE           = ':MTG_PROFILE';
	const BIND_COMPANY_EMAIL_ADDRESS = ':COMPANY_EMAIL_ADDRESS';
	const BIND_PRIORITY              = ':PRIORITY';
	const BIND_COMPANY_NAME          = ':COMPANY_NAME';
	const BIND_COMPANY_URL           = ':COMPANY_URL';
	const BIND_COMPANY_TEL           = ':COMPANY_TEL';
	const BIND_DENY_FLG              = ':DENY_FLG';

	/**
	 * キャッシュ用キー
	 * TODO: キャッシュ処理を追記していく
	 */
	const CACHE_KEY_FACEBOOK_ID           = 'M:FACEBOOK_ID:%s';
	const CACHE_KEY_MEMBER_ID             = 'M:MEMBER_ID:%s';
	const CACHE_KEY_PROFILE               = 'M:PROFILE:%s';
	const CACHE_KEY_LOCATION_ID           = 'M:LOCATION_ID:A:%s';
	const CACHE_KEY_TOTAL_COUNT           = 'M:TOTAL_COUNT';
	const CACHE_KEY_MEETING_TAG           = 'M:MEETING_TAG:%s:%s';
	const CACHE_KEY_PROFILE_TAG           = 'M:PROFILE_TAG:%s';

	/**
	 * インスタンス変数
	 */
	private static $_instance = null;

	/**
	 * DAOのインスタンスをシングルトンで取得する
	 */
	public static function getInstance () {
		if( !isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * コンストラクタ 接続情報の設定
	 */
	public function __construct(){
		$this->_DB_MST_DSN = Conf::MEMBER_DB_MST_DSN;
		$this->_DB_BAK_DSN = Conf::MEMBER_DB_BAK_DSN;
		$this->_DB_SLV_DSN = Conf::MEMBER_DB_SLV_DSN;
		$this->_DB_USER    = Conf::MEMBER_DB_USER;
		$this->_DB_PAWD    = Conf::MEMBER_DB_PSWD;
	}

	/**
	 * ログインしているか確認する
	 */
	public function getMemberId( $facebookId, $mode = 's' ) {
		$cache = MemberCache::getInstance();
		$memberId = $cache->get( sprintf( self::CACHE_KEY_MEMBER_ID, $facebookId ) );
		Logger::debug( __METHOD__, $memberId );
		if ( $memberId ) return $memberId;

		$sql = 'SELECT member_id FROM member WHERE facebook_id = ' . self::BIND_FACEBOOK_ID . ';';
		$res = $this->queryRow( $sql, array( self::BIND_FACEBOOK_ID => $facebookId ), $mode );
		if ( !isset( $res->member_id ) ) return 0;

		$cache->set( sprintf( self::CACHE_KEY_MEMBER_ID, $facebookId ), $res->member_id );
		return $res->member_id;
	}

	/**
	 * MemberIdを元にFacebookIdを取得する
	 */
	public function getFacebookId ( $memberId ) {
		$cache = MemberCache::getInstance();
		$facebookId = $cache->get( sprintf( self::CACHE_KEY_FACEBOOK_ID, $memberId ) );
		Logger::debug( __METHOD__, $facebookId );
		if ( $facebookId ) return $facebookId;

		$sql = 'SELECT facebook_id FROM member WHERE member_id = ' . self::BIND_MEMBER_ID . ';';
		$res = $this->queryRow( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( !isset( $res->facebook_id ) ) return false;

		$cache->set( sprintf( self::CACHE_KEY_FACEBOOK_ID, $memberId ), $res->facebook_id );
		return $res->facebook_id;
	}

	/**
	 * FacebookIdsからMemberIdsを取得する
	 */
	public function getMembersByFacebookIds ( $facebookIds ) {
		if ( !is_array( $facebookIds ) || count( $facebookIds ) == 0 ) return array();
		$sql = 'SELECT member_id, facebook_id FROM member WHERE facebook_id in ('.implode( ',', $facebookIds ).');';
		$res = $this->queryAll( $sql );
		if ( empty( $res ) ) return array();
		return $res;
	}

	/**
	 * 登録されている推奨取締役人数
	 */
	public function getMemberLikeCountAll () {
		$cache = MemberCache::getInstance();
		$totalCount = $cache->get( self::CACHE_KEY_TOTAL_COUNT );
		Logger::debug( __METHOD__, $totalCount );
		if ( $totalCount ) return $totalCount;

		$sql = 'SELECT COUNT(DISTINCT from_member_id) AS COUNT FROM member_like WHERE to_member_id <> 0;';
		$res = $this->queryRow( $sql );
		if ( !isset( $res->COUNT ) ) return 0;
		$cache->set( self::CACHE_KEY_TOTAL_COUNT, $res->COUNT );
		return $res->COUNT;
	}

	/**
	 * 自分を推奨している取締役人数
	 */
	public function getMemberLikeCount ($memberId) {
		$sql = 'SELECT COUNT(from_member_id) AS COUNT FROM member_like WHERE to_member_id = ' . self::BIND_MEMBER_ID . ';';
		$res = $this->queryRow( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( !isset( $res->COUNT ) ) return 0;
		return $res->COUNT;
	}

	/**
	 * メンバーの推奨人数を取得する
	 */
	public function getMemberLikeCountByMemberIds ( $memberIds ) {
		if ( !is_array( $memberIds ) || count( $memberIds ) == 0 ) return array();
		$sql = 'SELECT to_member_id, COUNT(from_member_id) AS COUNT FROM member_like WHERE to_member_id IN (' . implode( ',',$memberIds ) . ') GROUP BY to_member_id ORDER BY COUNT(from_member_id) LIMIT 0, 1000;';
		$res = $this->queryAll( $sql );

		$arr = array();
		if ( !empty( $res ) )
			foreach ( $res as $data ) $arr[$data->to_member_id] = $data->COUNT;

		if ( count( $arr ) != count( $memberIds ) )
			foreach ( $memberIds as $memberId ) if ( !isset( $arr[$memberId] ) ) $arr[$memberId] = 0;

		return $arr;
	}

	/**
	 * 活動場所の同じ自分以外の取締役の人数
	 */
	public function getMemberByLocation ( $locationId, $memberId ) {
		$sql = 'SELECT COUNT(id) AS COUNT FROM member_local WHERE location_id = ' . self::BIND_LOCATION_ID . ' AND member_id <> ' . self::BIND_MEMBER_ID . ';';
		$res = $this->queryRow( $sql, array(
			self::BIND_LOCATION_ID => $locationId,
			self::BIND_MEMBER_ID   => $memberId,
		) );
		if ( !isset( $res->COUNT ) ) return 0;
		return $res->COUNT;
	}

	/**
	 * 登録した活動場所を取得する
	 */
	public function getLocationIdByMemberId ( $memberId ) {
		$cache = MemberCache::getInstance();
		$locationIds = $cache->get( sprintf( self::CACHE_KEY_LOCATION_ID, $memberId ) );
		Logger::debug( __METHOD__, $locationIds );
		if ( $locationIds ) return $locationIds;

		$sql = 'SELECT location_id FROM member_local WHERE member_id = ' . self::BIND_MEMBER_ID . ';';
		$res = $this->queryAll( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
		$arr = array();
		foreach( $res as $row ){
			$arr[] = $row->location_id;
		}
		$cache->set( sprintf( self::CACHE_KEY_LOCATION_ID, $memberId ), $arr );
		return $arr;
	}

	/**
	 * プロフィールタグ一覧を取得する
	 */
	public function getProfileTag ( $memberId ) {
		$cache = MemberCache::getInstance();
		$profileTag = $cache->get( sprintf( self::CACHE_KEY_PROFILE_TAG, $memberId ) );
		Logger::debug( __METHOD__, $profileTag );
		if ( $profileTag ) return $profileTag;

		$sql = 'SELECT tag_text FROM member_profile_tag WHERE member_id = ' . self::BIND_MEMBER_ID . ' ORDER BY key_number;';
		$res = $this->queryAll( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
		$arr = array();
		foreach( $res as $row ){
			$arr[] = $row->tag_text;
		}
		$cache->set( sprintf( self::CACHE_KEY_PROFILE_TAG, $memberId ), $arr );
		return $arr;
	}

	/**
	 * ミーティングタグ一覧を取得する
	 */
	public function getMeetingTags ( $memberId, $enableFlag = false ) {
		$cache = MemberCache::getInstance();
		$meetingTag = $cache->get( sprintf( self::CACHE_KEY_MEETING_TAG, $memberId, ( $enableFlag ) ? 1 : 0 ) );
		Logger::debug( __METHOD__, $meetingTag );
		if ( $meetingTag ) return $meetingTag;

		$plusWhere = '';
		if( $enableFlag !== false ) $plusWhere = ' AND enable_flg = ' . $enableFlag;
		$sql = 'SELECT key_number, tag_text, enable_flg FROM member_mtg_tag WHERE member_id = ' . self::BIND_MEMBER_ID . $plusWhere . ' ORDER BY key_number;';
		$res = $this->queryAll( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
		if( $enableFlag !== false ) {
			$cache->set( sprintf( self::CACHE_KEY_MEETING_TAG, $memberId, 1 ), $res[0] );
			return $res[0];
		}
		$arr = array();
		foreach( $res as $key => $row ){
			$arr[$key]['number']   = $row->key_number;
			$arr[$key]['text']   = $row->tag_text;
			$arr[$key]['enable'] = ( $row->enable_flg ) ? 1 : 0;
		}
		$cache->set( sprintf( self::CACHE_KEY_MEETING_TAG, $memberId, 0 ), $arr );
		return $arr;
	}

	/**
	 * リストに表示するプロフィール情報を取得する
	 */
	public function getMemberProfileForList ( $memberIds ) {
		if( !is_array( $memberIds ) || empty( $memberIds ) ) return array();
		$sql = 'SELECT member_id, member_name, company_email_address, member_pr, mtg_profile FROM member_profile WHERE member_id IN ('. implode( ',', $memberIds ) .');';
		$res = $this->queryAll( $sql );
		if ( empty( $res ) ) return array();
		return $res;
	}

	/**
	 * 詳細ページに表示するプロフィール情報を取得する
	 */
	public function getMemberProfileForDetail ( $memberId ) {
		if( empty( $memberId ) ) return;

		$cache = MemberCache::getInstance();
		$profile = $cache->get( sprintf( self::CACHE_KEY_PROFILE, $memberId ) );
		Logger::debug( __METHOD__, $profile );
		if ( $profile ) return $profile;

		$sql = 'SELECT member_id, member_name, company_email_address, member_pr, mtg_profile FROM member_profile WHERE member_id = ' . self::BIND_MEMBER_ID . ';';
		$res = $this->queryRow( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return;

		$cache->set( sprintf( self::CACHE_KEY_PROFILE, $memberId ), $res );
		return $res;
	}

	/**
	 * 詳細ページに表示する企業情報を取得する
	 */
	public function getMemberCompanyForDetail ( $memberId, $priority ) {
		if( !is_array( $priority ) || empty( $priority ) ) return array();
		$sql = 'SELECT priority, company_name, company_url, company_tel FROM member_company WHERE member_id = ' . self::BIND_MEMBER_ID . ' AND priority IN ('.implode( ',', $priority ).') ORDER BY priority;';
		$res = $this->queryAll( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
		return $res;
	}

	/**
	 * 推奨してくれている取締役一覧を取得する
	 */
	public function getMemberLikeToMe ( $memberId ) {
		$sql = 'SELECT from_member_id FROM member_like WHERE to_member_id = ' . self::BIND_TO_MEMBER_ID . ';';
		$res = $this->queryAll( $sql, array( self::BIND_TO_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
		$arr = array();
		foreach( $res as $row ) $arr[] = $row->from_member_id;
		return $arr;
	}

	/**
	 * 推奨している取締役一覧を取得する
	 */
	public function getMemberLikeFromMe ( $memberId ) {
		$sql = 'SELECT to_member_id, to_facebook_id FROM member_like WHERE from_member_id = ' . self::BIND_FROM_MEMBER_ID . ';';
		$res = $this->queryAll( $sql, array( self::BIND_FROM_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
		return $res;
	}

	/**
	 * 自分にミーティングを提案している人と提案件数を取得する
	 */
	public function getMeetNowMemberCount ( $memberId ) {
		$sql = 'SELECT DISTINCT from_member_id FROM action_now_history WHERE to_member_id = ' . self::BIND_TO_MEMBER_ID . ' AND read_flg = 0 AND deny_flg = 0;';
		$res = $this->queryAll( $sql, array( self::BIND_TO_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
		$arr = array();
		foreach( $res as $row ) $arr[$row->from_member_id] = $row->from_member_id;
		return $arr;
	}

	/**
	 * MeetFeedを取得する
	 * @params Int $memberId
	 * @params Int $type
	 */
	public function getMeetFeed ( $memberId, $type = 0 ) {
		$locationIds = $this->getLocationIdByMemberId( $memberId );
		if ( count( $locationIds ) == 0 ) return array();

		$meetingTag = $this->getMeetingTags( $memberId, true );
		if ( empty( $meetingTag ) ) return array();
		if ( is_array( $meetingTag ) ) return array();

		$sql = 'SELECT DISTINCT member_id FROM member_profile_tag WHERE tag_text = '.self::BIND_TAG_TEXT.';';
		$profileTagInfos = $this->queryAll( $sql, array( self::BIND_TAG_TEXT => $meetingTag->tag_text ) );
		if ( empty( $profileTagInfos ) ) return array();
		$profileTagMember = array();
		foreach ( $profileTagInfos as $profileTagInfo )
			$profileTagMember[$profileTagInfo->member_id] = $profileTagInfo->member_id;

		$sql = 'SELECT DISTINCT member_id FROM member_local WHERE location_id IN ('.implode( ',', $locationIds ).');';
		$localInfos = $this->queryAll( $sql );
		if ( empty( $localInfos ) ) return array();

		$meetMembers = array();
		foreach ( $localInfos as $localInfo ) {
			if ( isset( $profileTagMember[$localInfo->member_id] ) ) $meetMembers[] = $localInfo->member_id;
		}
		if ( empty( $meetMembers ) ) return array();

		$sql = 'SELECT DISTINCT to_member_id FROM member_like WHERE to_member_id IN ('.implode( ',', $meetMembers ).');';
		$likeInfos = $this->queryAll( $sql );
		if ( empty( $likeInfos ) ) return array();

		$meetLikeMemberIds = array();
		foreach ( $likeInfos as $likeInfo ) $meetLikeMemberIds[] = $likeInfo->to_member_id;
		$profiles = $this->getMemberProfileForList( $meetLikeMemberIds );
		$memberLikes = $this->getMemberLikeCountByMemberIds( $meetLikeMemberIds );
		$profileTags = $this->getProfileTag( $memberId );
		$meetNows = $this->getMeetNowMemberCount( $memberId );

		$meetFeed = array();
		foreach ( $profiles as $key => $profile ) {
			if ( $profile->member_id === $memberId ) {
				continue;
			}
			$meetFeed[] = array(
				'member_id'    => $profile->member_id,
				'icon'         => sprintf( Conf::FACEBOOK_IMAGE_URL_S, $this->getFacebookId( $profile->member_id ) ),
				'member_name'  => $profile->member_name,
				'profile_tags' => $profileTags,
				'like_count'   => ( isset( $memberLikes[$profile->member_id] ) )
									? $memberLikes[$profile->member_id] : 0,
				'meet_now'     => ( isset($meetNows[$profile->member_id]) ) ? 1 : 0,
			);
		}
		return $meetFeed;
	}

	/**
	 * LikeされていないメンバーのMeetFeedを取得する
	 * @params Int $memberId
	 * @params Array $friendIds
	 */
	public function getMeetFeedByNotLike ( $memberId, $friendIds ) {
		$members = $this->getMembersByFacebookIds( $friendIds );
		foreach ( $members as $member ) $memberIds[] = $member->member_id;
		$profiles = $this->getMemberProfileForList( $memberIds );
		$memberLikes = $this->getMemberLikeCountByMemberIds( $memberIds );
		$meetNows = $this->getMeetNowMemberCount( $memberId );

		$meetFeed = array();
		foreach ( $profiles as $key => $profile ) {
			if ( $profile->member_id === $memberId ) {
				continue;
			}
			$meetFeed[] = array(
				'member_id'    => $profile->member_id,
				'icon'         => sprintf( Conf::FACEBOOK_IMAGE_URL_S, $this->getFacebookId( $profile->member_id ) ),
				'member_name'  => $profile->member_name,
				'member_pr'    => $profile->member_pr,
				'like_count'   => ( isset( $memberLikes[$profile->member_id] ) )
									? $memberLikes[$profile->member_id] : 0,
				'meet_now'     => ( isset($meetNows[$profile->member_id]) ) ? 1 : 0,
			);
		}
		return $meetFeed;
	}
}
