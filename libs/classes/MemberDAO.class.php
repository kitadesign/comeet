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
        $sql = 'SELECT member_id FROM member WHERE facebook_id = ' . self::BIND_FACEBOOK_ID . ';';
        $res = $this->queryRow( $sql, array( self::BIND_FACEBOOK_ID => $facebookId ), $mode );
		if ( !isset( $res->member_id ) ) return false;
        return $res->member_id;
    }

	/**
	 * MemberIdを元にFacebookIdを取得する
	 */
	public function getFacebookId ( $memberId ) {
        $sql = 'SELECT facebook_id FROM member WHERE member_id = ' . self::BIND_MEMBER_ID . ';';
        $res = $this->queryRow( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( !isset( $res->facebook_id ) ) return false;
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
        $sql = 'SELECT COUNT(DISTINCT from_member_id) AS COUNT FROM member_like WHERE to_member_id <> "";';
        $res = $this->queryRow( $sql );
        if ( !isset( $res->COUNT ) ) return 0;
        return $res->COUNT;
    }

    /**
     * 自分を推奨している取締役人数
     */
    public function getMemberLikeCount ($memberId) {
        $sql = 'SELECT COUNT(to_member_id) AS COUNT FROM member_like WHERE to_member_id = ' . self::BIND_MEMBER_ID . ';';
        $res = $this->queryRow( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
        if ( !isset( $res->COUNT ) ) return 0;
        return $res->COUNT;
    }

    /**
     * メンバーの推奨人数を取得する
     */
	public function getMemberLikeCountByMemberIds ( $memberIds ) {
		if ( !is_array( $memberIds ) || count( $memberIds ) == 0 ) return array();
		$sql = 'SELECT to_member_id, COUNT(from_member_id) AS COUNT FROM member_like WHERE to_member_id IN (' . implode( ',',$memberIds ) . ') GROUP BY to_member_id;';
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
        $sql = 'SELECT location_id FROM member_local WHERE member_id = ' . self::BIND_MEMBER_ID . ';';
        $res = $this->queryAll( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
		if ( empty( $res ) ) return array();
        $arr = array();
        foreach( $res as $row ){
            $arr[] = $row->location_id;
        }
        return $arr;
	}

    /**
     * プロフィールタグ一覧を取得する
     */
    public function getProfileTag ( $memberId ) {
        $sql = 'SELECT tag_text FROM member_profile_tag WHERE member_id = ' . self::BIND_MEMBER_ID . ' ORDER BY key_number;';
        $res = $this->queryAll( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
        if ( empty( $res ) ) return array();
        $arr = array();
        foreach( $res as $row ){
            $arr[] = $row->tag_text;
        }
        return $arr;
    }

    /**
     * ミーティングタグ一覧を取得する
     */
    public function getMeetingTags ( $memberId, $enable_flg = false ) {
        $plus_where = '';
        if( $enable_flg !== false ) $plus_where = ' AND enable_flg = ' . $enable_flg;
        $sql = 'SELECT key_number, tag_text, enable_flg FROM member_mtg_tag WHERE member_id = ' . self::BIND_MEMBER_ID . $plus_where . ' ORDER BY key_number;';
        $res = $this->queryAll( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
        if ( empty( $res ) ) return array();
        if( $enable_flg !== false ) return $res[0];
        $arr = array();
        foreach( $res as $key => $row ){
            $arr[$key]['number']   = $row->key_number;
            $arr[$key]['text']   = $row->tag_text;
            $arr[$key]['enable'] = ( $row->enable_flg ) ? 1 : 0;
        }
        return $arr;
    }

    /**
     * リストに表示するプロフィール情報を取得する
     */
    public function getMemberProfileForList ( $memberIds ) {
        if( !is_array( $memberIds ) || empty( $memberIds ) == 0 ) return array();
        $sql = 'SELECT member_id, mtg_profile member_name FROM member_profile WHERE member_id IN ('. implode( ',', $memberIds ) .');';
        $res = $this->queryAll( $sql );
        if ( empty( $res ) ) return array();
        return $res;
    }

    /**
     * 詳細ページに表示するプロフィール情報を取得する
     */
    public function getMemberProfileForDetail ( $memberId ) {
        $sql = 'SELECT member_name, company_email_address, member_pr, mtg_profile FROM member_profile WHERE member_id = ' . self::BIND_MEMBER_ID . ';';
        $res = $this->queryRow( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
        if ( empty( $res ) ) return '';
        return $res;
    }

    /**
     * 詳細ページに表示する企業情報を取得する
     */
	public function getMemberCompanyForDetail ( $memberId, $priority ) {
        if( !is_array($priority) || count($priority) == 0 ) return array();
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
        foreach( $res as $row ){
            $arr[] = $row->from_member_id;
        }
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
		foreach ( $profileTagInfos as $profileTagInfo ) $profileTagMember[$profileTagInfo->member_id] = $profileTagInfo->member_id;

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
		return $this->getMemberProfileForList( $meetLikeMemberIds );
	}
}
