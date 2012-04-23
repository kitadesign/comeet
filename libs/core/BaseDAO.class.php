<?php
namespace DAO;
/**
 * DAO::BaseDAO
 * データアクセスオブジェクトのベース
 */
abstract class BaseDAO
{

	/**
	 * インスタンス変数
	 */
	private static $_instance = null;

	/**
	 * コネクション変数
	 */
	private static $_connect = array();

	/**
	 * DAOのインスタンスをシングルトンで取得する
	 */
	public static function getInstance(){
		if(!isset(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * DBのコネクションを取得する
	 */
	protected function _getConnection($mode = 's'){
		if(!isset(self::$_connect[$mode])){
			try {
				if ( $mode == 's' ) {
					self::$_connect = new PDO(Conf::MEMBER_DB_SLV_DSN, Conf::MEMBER_DB_USER, Conf::MEMBER_DB_PAWD);
				} else if ( $mode == 'b' ) {
					self::$_connect = new PDO(Conf::MEMBER_DB_BAK_DSN, Conf::MEMBER_DB_USER, Conf::MEMBER_DB_PAWD);
				} else if ( $mode == 'm' ) {
					self::$_connect = new PDO(Conf::MEMBER_DB_MST_DSN, Conf::MEMBER_DB_USER, Conf::MEMBER_DB_PAWD);
				} else {
					throw new Exception('Nothing DB connection['.$mode.']');
				}
				self::$_connect->(PDO::ATTR_CASE, PDO::CASE_UPPER);
				self::$_connect->(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$_connect->(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
				self::$_connect->(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
			} catch( Exception $e ) {
				Logger::info(__METHOD__, $e->getMessage());
				throw $e;
			}
			return self::$_connect;
		}
	}

	/**
	 * マスターDBのコネクションを取得する
	 */
	protected function getMasterDB(){
		return $this->_getConnection('m');
	}

	/**
	 * スレーブDBのコネクションを取得する
	 */
	protected function getSlaveDB(){
		return $this->_getConnection('s');
	}

	/**
	 * バックアップDBのコネクションを取得する
	 */
	protected function getBackUpDB(){
		return $this->_getConnection('b');
	}

	/**
	 * スレーブへの検索系クエリSQLを実行する
	 */
	protected function query($sql, $values = array()){
		$stmt = $this->getSlave()->prepare($sql);
		foreach ( $values as $key => $value ) {
			if ( is_numeric($value) ) {
				$stmt->bindParam($key, $value, PDO::PARAM_INT);
			} else {
				$stmt->bindParam($key, $value, PDO::PARAM_STR);
			}
		}
		return $stmt->fetchAll();
	}

	/**
	 * マスターへのDB更新系SQLを実行する
	 */
	protected function update($sql, $values = array()){
		$stmt = $this->getMaster()->prepare($sql);
		foreach ( $values as $key => $value ) {
			if ( is_numeric($value) ) {
				$stmt->bindParam($key, $value, PDO::PARAM_INT);
			} else {
				$stmt->bindParam($key, $value, PDO::PARAM_STR);
			}
		}
		return $stmt->execute();
	}

	/**
	 * データ投入後のIDを取得する
	 */
	protected function insertId(){
		$this->getMaster()->lastInsertId();
	}

	/**
	 * トランザクションのスタート
	 */
	protected function begin(){
		$this->getMaster()->beginTransaction();
	}

	/**
	 * トランザクションのコミット
	 */
	protected function commit(){
		$this->getMaster()->commit();
	}

	/**
	 * トランザクションのロールバック
	 */
	protected function rollback(){
		$this->getMaster()->rollBack();
	}
}

/**
 * DAO::LogDAO
 * SQL実行ログを取得するベース
 */
abstract class LogDAO exnteds BaseDAO
{
	/**
	 * DAO::LogDAO::query
	 * ロギングしながら検索クエリ実行
	 */
	protected function query($sql, $values = array()){
		Logger::debug(__METHOD__, 'Query['.$sql.'] Values['var_export($values, true)']');
		try {
			return parent::query($sql, $values);
		} catch ( Exception $e ) {
			Logger::error(__METHOD__, $e->getMessage());
			throw $e;
		}
	}

	/**
	 * DAO::LogDAO::update
	 * ロギングしながら更新系SQL実行
	 */
	protected function update($sql, $values = array()){
		Logger::debug(__METHOD__, 'Query['.$sql.'] Values['var_export($values, true)']');
		try {
			return parent::query($sql, $values);
		} catch ( Exception $e ) {
			Logger::error(__METHOD__, $e->getMessage());
			throw $e;
		}
	}
}
