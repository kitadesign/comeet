<?php

/**
 * DAO::BaseDAO
 * データアクセスオブジェクトのベース
 */
abstract class BaseDAO
{

	/**
	 * DB接続情報
	 */
	protected static $_DB_MST_DSN = '';
	protected static $_DB_BAK_DSN = '';
	protected static $_DB_SLV_DSN = '';
	protected static $_DB_USER    = '';
	protected static $_DB_PAWD    = '';

    /**
     * コネクション変数
     */
    private static $_connect = array();

    /**
     * DBのコネクションを取得する
     */
    protected function _getConnection ( $mode = 's' ) {
        if( !isset( $this->_connect[$mode] ) ) {
            try {
                if ( $mode == 's' ) {
                    $this->_connect[$mode] = new PDO( $this->_DB_SLV_DSN, $this->_DB_USER, $this->_DB_PAWD );
                } else if ( $mode == 'b' ) {
                    $this->_connect[$mode] = new PDO( $this->_DB_BAK_DSN, $this->_DB_USER, $this->_DB_PAWD );
                } else if ( $mode == 'm' ) {
                    $this->_connect[$mode] = new PDO( $this->_DB_MST_DSN, $this->_DB_USER, $this->_DB_PAWD );
                } else {
                    throw new Exception( 'Nothing DB connection[' . $mode . ']' );
                }
                $this->_connect[$mode]->setAttribute( PDO::ATTR_CASE, PDO::CASE_NATURAL );
                $this->_connect[$mode]->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                $this->_connect[$mode]->setAttribute( PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING );
                $this->_connect[$mode]->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
            } catch( Exception $e ) {
                Logger::info( __METHOD__, $e->getMessage() );
                throw $e;
            }
        }
		return $this->_connect[$mode];
    }

    /**
     * マスターDBのコネクションを取得する
     */
    protected function getMasterDB () {
        return $this->_getConnection( 'm' );
    }

    /**
     * スレーブDBのコネクションを取得する
     */
    protected function getSlaveDB () {
        return $this->_getConnection( 's' );
    }

    /**
     * バックアップDBのコネクションを取得する
     */
    protected function getBackUpDB () {
        return $this->_getConnection( 'b' );
    }

    /**
     * 検索系クエリSQLを実行して全データを取得する
     */
    protected function queryAll ( $sql, $values = array(), $mode = 's' ) {
		if ($mode == 's') {
	        $stmt = $this->getSlaveDB()->prepare( $sql );
		} else {
			$stmt = $this->getMasterDB()->prepare( $sql );
		}
        foreach ( $values as $key => $value ) {
			if ( is_numeric( $value ) ) {
                $stmt->bindValue( $key, $value, PDO::PARAM_INT );
            } else {
                $stmt->bindValue( $key, $value, PDO::PARAM_STR );
            }
        }
		$stmt->execute();
        return $stmt->fetchAll( PDO::FETCH_OBJ );
    }

    /**
     * 検索系クエリSQLを実行して１レコードを取得する
     */
    protected function queryRow ( $sql, $values = array(), $mode = 's' ) {
		if ($mode == 's') {
	        $stmt = $this->getSlaveDB()->prepare( $sql );
		} else {
			$stmt = $this->getMasterDB()->prepare( $sql );
		}
        foreach ( $values as $key => $value ) {
            if ( is_numeric( $value ) ) {
                $stmt->bindValue( $key, $value, PDO::PARAM_INT );
            } else {
                $stmt->bindValue( $key, $value, PDO::PARAM_STR );
            }
        }
		$stmt->execute();
        return $stmt->fetch( PDO::FETCH_OBJ );
    }

    /**
     * マスターへのDB更新系SQLを実行する
     */
    protected function update ( $sql, $values = array() ) {
        $stmt = $this->getMasterDB()->prepare( $sql );
        foreach ( $values as $key => $value ) {
            if ( is_numeric( $value ) ) {
                $stmt->bindValue( $key, $value, PDO::PARAM_INT );
            } else {
                $stmt->bindValue( $key, $value, PDO::PARAM_STR );
            }
        }
        return $stmt->execute();
    }

    /**
     * データ投入後のIDを取得する
     */
    protected function lastInsertId () {
        $this->getMasterDB()->lastInsertId();
    }

    /**
     * トランザクションのスタート
     */
    protected function begin () {
        $this->getMasterDB()->beginTransaction();
    }

    /**
     * トランザクションのコミット
     */
    protected function commit () {
        $this->getMasterDB()->commit();
    }

    /**
     * トランザクションのロールバック
     */
    protected function rollback () {
        $this->getMasterDB()->rollBack();
    }
}
