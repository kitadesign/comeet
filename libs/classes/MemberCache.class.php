<?php

class MemberCache extends BaseCache
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
	 * コンストラクタ 接続情報の設定
	 */
	public function __construct(){
		$this->_CACHE_SERVER = Conf::$_MEMBER_CACHE_SERVERS;
	}
}
