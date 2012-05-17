<?php

/**
 * バリデーション処理
 */
class Validate
{
	/**
	 * FacebookIDチェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidFacebookId ( $value ) {
		if ( empty( $value ) ) return false;
		return true;
	}

	/**
	 * MemberIDチェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidMemberId ( $value ) {
		if ( empty( $value ) ) return false;
		if ( !is_numeric( $value ) ) return false;
		if ( strlen( $value ) > 12 ) return false;
		return true;
	}

	/**
	 * LocationIDチェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidLocationId ( $value ) {
		if ( empty( $value ) ) return false;
		if ( !isset( Conf::$LOCATION_ID[$value] ) ) return false;
		return true;
	}

	/**
	 * メンバーの名前チェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidMemberName ( $value ) {
		if ( empty( $value ) ) return false;
		return true;
	}
	
	/**
	 * メンバーの自己紹介チェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidMemberPR ( $value ) {
		if ( empty( $value ) ) return true;
		if ( mb_strlen( $value, INTERNAL_ENCODING ) > 50 ) return false;
		return true;
	}

	/**
	 * 会社メールアドレスチェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidCompanyEmailAddress ( $value ) {
		if ( empty( $value ) ) return true;
		if ( strlen( $value, INTERNAL_ENCODING ) > 100 ) return false;
		if (preg_match('/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i', $value)) {
			return true;
		}
		return false;
	}

	/**
	 * ミーティング内容のチェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidMeetingProfile ( $value ) {
		if ( empty( $value ) ) return true;
		if ( strlen( $value, INTERNAL_ENCODING ) > 3000 ) return false;
		return true;
	}

	/**
	 * ミーティングTagのチェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function isValidMeetingTags ( $values ) {
		if ( !is_array( $values ) ) return false;
		if ( count($values) > 3 ) return false;
		foreach ( $values as $value ) {
			if ( empty( $value ) ) continue;
			if ( mb_strlen( $value, INTERNAL_ENCODING ) > 50 ) return false;
		}
		return true;
	}

	/**
	 * 推奨取締役のチェック
	 * @params Mix $vslue
	 * @return Boolean
	 */
	public static function inValidFriends ( $values ) {
		if ( !is_array( $values ) ) return false;
		if ( count($values) > 3 ) return false;
		foreach ( $values as $value ) {
			if ( empty( $value ) ) continue;
			if ( mb_strlen( $value, INTERNAL_ENCODING ) > 50 ) return false;
		}
		return true;
	}

	/**
	 * 推奨取締役の名前チェック
	 */
	public static function inValidFriendNames ( $values ) {
		if ( !is_array( $values ) ) return false;
		if ( count($values) > 3 ) return false;
		foreach ( $values as $value ) {
			if ( empty( $value ) ) continue;
			if ( mb_strlen( $value, INTERNAL_ENCODING ) > 100 ) return false;
		}
		return true;
	}
}
