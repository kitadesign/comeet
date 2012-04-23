<?php
namespace DAO;
/**
 * DAO::MemberDAO
 * MemberDBへのSQLを実行する
 */
class MemberDAO extends BaseDAO
{
	/**
	 * FacebookIDのバインド変数
	 */
	const BIND_FACEBOOK_ID = ':FACEBOOK_ID';

	/**
	 * ログインしているか確認する
	 */
	public function getLoginMemberId($facebook_id){
		$sql = 'SELECT member_id FROM member WHERE facebook_id = ' . self::BIND_FACEBOOK_ID;
		$res = $this->query($sql, array(self::BIND_FACEBOOK_ID => $facebook_id));
		if ( !isset( $res[0] ) ) return false;
		if ( !isset( $res[0]['member_id'] ) ) return false;
		return $res[0]['member_id'];
	}
}
