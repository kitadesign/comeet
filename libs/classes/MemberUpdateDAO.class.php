<?php

/**
 * DAO::MemberDAO
 * MemberDBへのSQLを実行する
 */
class MemberUpdateDAO extends MemberDAO
{

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
	 * Facebookユーザを元に新規メンバーを登録する
	 */
	public function createMemberByFacebookId ( $facebookId, $facebookAccessToken, $language, $memberName, $memberPR ) {
		try {
			$this->begin();
			$sql = 'INSERT INTO member SET `facebook_id` = ' . self::BIND_FACEBOOK_ID . ', `facebook_access_token` = ' . self::BIND_FACEBOOK_ACCESS_TOKEN . ', `language` = ' . self::BIND_LANGUAGE . ', `create_at` = CURRENT_TIMESTAMP;';
			$res = $this->update( $sql, array(
				self::BIND_FACEBOOK_ID           => $facebookId,
				self::BIND_FACEBOOK_ACCESS_TOKEN => $facebookAccessToken,
				self::BIND_LANGUAGE              => $language,
			) );

			$memberId = $this->getMemberId( $facebookId, 'm' );

			$sql = 'INSERT INTO member_profile SET `member_id` = ' . self::BIND_MEMBER_ID . ', `member_name` = ' . self::BIND_MEMBER_NAME . ', `member_pr` = ' . self::BIND_MEMBER_PR . ';';
			$this->update( $sql, array(
				self::BIND_MEMBER_ID   => $memberId,
				self::BIND_MEMBER_NAME => $memberName,
				self::BIND_MEMBER_PR   => $memberPR,
			) );

			$sql = 'UPDATE member_like SET `to_member_id` = ' . self::BIND_TO_MEMBER_ID . ' WHERE `to_facebook_id` = ' . self::BIND_TO_FACEBOOK_ID . ';';
			$this->update( $sql, array(
				self::BIND_TO_MEMBER_ID   => $memberId,
				self::BIND_TO_FACEBOOK_ID => $facebookId,
			) );

			$this->commit();
			return true;
		} catch ( PDOException $e ) {
			$this->rollback();
			return false;
		}
	}

	/**
	 * 活動場所を初期登録する
	 */
	public function createMemberLocal ( $memberId, $locationId ) {
		try {
			$sql = 'INSERT INTO member_local SET `member_id` = ' . self::BIND_MEMBER_ID . ', `location_id` = ' . self::BIND_LOCATION_ID . ';';
			$this->update( $sql, array(
				self::BIND_MEMBER_ID   => $memberId,
				self::BIND_LOCATION_ID => $locationId,
			) );
			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_LOCATION_ID, $memberId ) );
			return true;
		} catch ( PDOException $e ) {
			return false;
		}
	}

	/**
	 * ミーティングタグを登録する
	 */
	public function replaceMeetingTag ( $memberId, $values, $enable = null ) {
		try {
			$this->begin();

			foreach ( $values as $key => $value ) {
				if ( strlen($value) > 0 ) {
					$sql = 'REPLACE INTO member_mtg_tag SET `member_id` = ' . self::BIND_MEMBER_ID . ', `key_number` = ' . self::BIND_KEY_NUMBER . ', `tag_text` = ' . self::BIND_TAG_TEXT . ', `enable_flg` = ' . self::BIND_ENABLE_FLG . ';';
					$this->update( $sql, array(
						self::BIND_MEMBER_ID => $memberId,
						self::BIND_KEY_NUMBER => $key,
						self::BIND_TAG_TEXT => $value,
						self::BIND_ENABLE_FLG => ($key == $enable) ? 1 : 0, 
					) );
				} else {
					$sql = 'DELETE FROM member_mtg_tag WHERE `member_id` = ' . self::BIND_MEMBER_ID . ' AND `key_number` = ' . self::BIND_KEY_NUMBER . ';';
					$this->update( $sql, array(
						self::BIND_MEMBER_ID => $memberId,
						self::BIND_KEY_NUMBER => $key,
					) );
				}
			}
			$this->commit();

			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_MEETING_TAG, $memberId, 0 ) );
			$cache->remove( sprintf( self::CACHE_KEY_MEETING_TAG, $memberId, 1 ) );

			return true;
		} catch ( PDOException $e ) {
			$this->rollback();
			return false;
		}
	}

	/**
	 * ミーティング内容を編集する
	 */
	public function updateMeetingProfile ( $memberId, $mtgProfile ) {
		try {
			$sql = 'UPDATE member_profile SET `mtg_profile` = ' . self::BIND_MTG_PROFILE . ' WHERE `member_id` = ' . self::BIND_MEMBER_ID . ';';
			$this->update( $sql, array(
				self::BIND_MTG_PROFILE => $mtgProfile,
				self::BIND_MEMBER_ID   => $memberId,
			) );

			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_PROFILE, $memberId ) );
			return true;
		} catch ( PDOException $e ) {
			return false;
		}
	}

	/**
	 * 活動場所を編集する
	 */
	public function updateMemberLocal ( $memberId, $locationIds ) {
		try {
			$this->begin();
			$sql = 'DELETE FROM member_local WHERE `member_id` = ' . self::BIND_MEMBER_ID . ';';
			$this->update( $sql, array( self::BIND_MEMBER_ID => $memberId ) );
			$sql = 'INSERT INTO member_local SET `member_id` = ' . self::BIND_MEMBER_ID . ', `location_id` = ' . self::BIND_LOCATION_ID . ';';
			if ( is_array( $locationIds ) ) {
				foreach ( $locationIds as $locationId ) {
					$this->update( $sql, array(
						self::BIND_MEMBER_ID   => $memberId,
						self::BIND_LOCATION_ID => $locationId,
					) );
				}
			} else {
				$this->update( $sql, array(
					self::BIND_MEMBER_ID   => $memberId,
					self::BIND_LOCATION_ID => $locationIds,
				) );
			}
			$this->commit();

			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_LOCATION_ID, $memberId ) );
			return true;
		} catch ( PDOExceiton $e ) {
			$this->rollback();
			return false;
		}
	}

	/**
	 * 推奨取締役の登録する
	 */
	public function updateMemberLikeByFacebookIds ( $memberId, $facebookIds ) {
		try {
			$this->begin();
			$sql = 'DELETE FROM member_like WHERE `from_member_id` = ' . self::BIND_FROM_MEMBER_ID . ';';
			$this->update( $sql, array( self::BIND_FROM_MEMBER_ID => $memberId ) );
			foreach ( $facebookIds as $facebookId ) {
				$sql = 'INSERT INTO member_like SET `from_member_id` = ' . self::BIND_FROM_MEMBER_ID . ', `to_member_id` = ' . self::BIND_TO_MEMBER_ID . ', `to_facebook_id` = ' . self::BIND_TO_FACEBOOK_ID . ', `create_at` = CURRENT_TIMESTAMP;';
				$this->update( $sql, array(
					self::BIND_FROM_MEMBER_ID => $memberId,
					self::BIND_TO_MEMBER_ID   => $this->getMemberId( $facebookId ),
					self::BIND_TO_FACEBOOK_ID => $facebookId,
				) );
			}
			$this->commit();
			return true;
		} catch ( PDOException $e ) {
			$this->rollback();
			return false;
		}
	}

	/**
	 * メンバーの名前を更新する
	 */
	public function updateMemberName ( $memberId, $memberName ) {
		try {
			$sql = 'UPDATE member_profile SET `member_name` = ' . self::BIND_MEMBER_NAME . ' WHERE `member_id` = ' . self::BIND_MEMBER_ID . ';';
			$this->update( $sql, array(
				self::BIND_MEMBER_ID   => $memberId,
				self::BIND_MEMBER_NAME => $memberName,
			) );

			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_PROFILE, $memberId ) );
			return true;
		} catch ( PDOException $e ) {
			return false;
		}
	}

	/**
	 * プロフィールタグを編集する
	 */
	public function replaceProfileTag ( $memberId, $profileTags ) {
		try {
			$this->begin();
			foreach ( $profileTags as $key => $profileTag ) {
				$sql = 'REPLACE INTO member_profile_tag SET `member_id` = ' . self::BIND_MEMBER_ID . ', `key_number` = ' . self::BIND_KEY_NUMBER . ', `tag_text` = ' . self::BIND_TAG_TEXT . ';';
				$this->update( $sql, array(
					self::BIND_MEMBER_ID  => $memberId,
					self::BIND_KEY_NUMBER => $key,
					self::BIND_TAG_TEXT   => $profileTag,
				) );
			}
			$this->commit();

			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_PROFILE_TAG, $memberId ) );

			return true;
		} catch ( PDOException $e ) {
			$this->rollback();
			return false;
		}
	}

	/**
	 * 会社メールアドレスを編集する
	 */
	public function updateCompanyEmailAddress ( $memberId, $companyEmailAddress ) {
		try {
			$sql = 'UPDATE member_profile SET `company_email_address` = ' . self::BIND_COMPANY_EMAIL_ADDRESS . ' WHERE `member_id` = ' . self::BIND_MEMBER_ID . ';';
			$this->update( $sql, array(
				self::BIND_MEMBER_ID             => $memberId,
				self::BIND_COMPANY_EMAIL_ADDRESS => $companyEmailAddress,
			) );

			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_PROFILE, $memberId ) );
			return true;
		} catch ( PDOException $e ) {
			return false;
		}
	}

	/**
	 * 自分のPRを編集する
	 */
	public function updateMemberPR ( $memberId, $memberPR ) {
		try {
			$sql = 'UPDATE member_profile SET `member_pr` = ' . self::BIND_MEMBER_PR . ' WHERE `member_id` = ' . self::BIND_MEMBER_ID . ';';
			$this->update( $sql, array(
				self::BIND_MEMBER_ID => $memberId,
				self::BIND_MEMBER_PR => $memberPR,
			) );

			$cache = MemberCache::getInstance();
			$cache->remove( sprintf( self::CACHE_KEY_PROFILE, $memberId ) );
			return true;
		} catch ( PDOException $e ) {
			return false;
		}
	}

	/**
	 * 会社情報を更新する
	 */
	public function replaceCompanyInfo ( $memberId, $companyInfos ) {
		try {
			$this->begin();
			foreach ( $companyInfos as $key => $companyInfo ) {
				$sql = 'REPLACE INTO member_company SET `member_id` = ' . self::BIND_MEMBER_ID . ', `priority` = ' . self::BIND_PRIORITY . ', `company_name` = ' . self::BIND_COMPANY_NAME . ', `company_url` = ' . self::BIND_COMPANY_URL . ', `company_tel` = ' . self::BIND_COMPANY_TEL . ';';
				$this->update( $sql, array(
					self::BIND_MEMBER_ID    => $memberId,
					self::BIND_PRIORITY     => $key,
					self::BIND_COMPANY_NAME => $companyInfo['name'],
					self::BIND_COMPANY_URL  => $companyInfo['url'],
					self::BIND_COMPANY_TEL  => $companyInfo['tel'],
				) );
			}
			$this->commit();

			return true;
		} catch ( PDOException $e ) {
			$this->rollback();
			return false;
		}
	}

	/**
	 * すぐにボタン押下登録
	 */
	public function createActionNow ( $fromMemberId, $toMemberId, $denyFlg ) {
		try {
			$sql = 'INSERT INTO action_now_history SET `from_member_id` = ' . self::BIND_FROM_MEMBER_ID . ', `to_member_id` = ' . self::BIND_TO_MEMBER_ID . ', `action_at` = CURRENT_TIMESTAMP, `read_flg` = 0, `deny_flg` = ' . self::BIND_DENY_FLG . ';';
			$this->update( $sql, array(
				self::BIND_FROM_MEMBER_ID => $fromMemberId,
				self::BIND_TO_MEMBER_ID   => $toMemberId,
				self::BIND_DENY_FLG       => $denyFlg,
			) );
			return true;
		} catch ( PDOException $e ) {
			return false;
		}
	}

	/**
	 * すぐに！対応で既読を登録する
	 */
	public function updateActionLead ( $toMemberId ) {
		try {
			$sql = 'UPDATE action_now_history SET `read_flg` = 1 WHERE `from_member_id` = ' . self::BIND_TO_MEMBER_ID . ';';
			$this->update( $sql, array(
				self::BIND_TO_MEMBER_ID => $toMemberId,
			) );
			return true;
		} catch ( PDOException $e ) {
			return false;
		}
	}
}
