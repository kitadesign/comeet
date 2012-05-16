<?php

/**
 * DAO::BaseCache
 * キャッシュオブジェクトのベース
 */
abstract class BaseCache
{

	protected $_CACHE_SERVER = array();
	protected $_CACHE_PORT   = 11211;

	/**
	 * Memcacheコネクション
	 */
	protected $_connect;

	/**
	 * コネクションを取得する
	 */
	protected function _getConnection(){
		if ( !isset( $this->_connect ) ) {
			$this->_connect = new Memcache;
			foreach ( $this->_CACHE_SERVER as $server) {
				$this->_connect->addServer( $server, $this->_CACHE_PORT );
			}
		}
		return $this->_connect;
	}

	/**
	 * キャッシュ処理のすべてを行う
	 */
	public function cache( $key, $data = null ) {
		Logger::debug( __METHOD__, $key );
		if ($data) Logger::debug( __METHOD__, $data );
		$connect = $this->_getConnection();
		if ( $data === null ) {
			return $connect->get( $key );
		} else {
			return $connect->add( $key, $data, MEMCACHE_COMPRESSED, Conf::CACHE_EXPIRE );
		}
	}

	/**
	 * キャッシュを取得する
	 */
	public function get ( $key ) {
		return $this->cache( $key );
	}

	/**
	 * キャッシュを設定する
	 */
	public function set ( $key, $data = null ) {
		return $this->cache( $key, $data );
	}

	/**
	 * キャッシュを削除する
	 */
	public function remove ( $key ) {
		$connect = $this->_getConnection();
		return $connect->delete( $key );
	}
}
