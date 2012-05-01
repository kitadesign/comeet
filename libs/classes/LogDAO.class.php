<?php

/**
 * DAO::LogDAO
 * SQL実行ログを取得するベース
 */
abstract class LogDAO extends BaseDAO
{
    /**
     * DAO::LogDAO::queryAll
     * ロギングしながら検索クエリ実行して全レコード取得
     */
    protected function queryAll ( $sql, $values = array(), $mode = 's' ) {
        Logger::debug( __METHOD__, 'Query[' . $sql . '] Values[' . var_export( $values, true ) . '] Mode[' . $mode . ']' );
        try {
            return parent::queryAll( $sql, $values, $mode );
        } catch ( Exception $e ) {
            Logger::error( __METHOD__, $e->getMessage() );
            throw $e;
        }
    }

    /**
     * DAO::LogDAO::queryRow
     * ロギングしながら検索クエリ実行して１レコード取得
     */
    protected function queryRow ( $sql, $values = array(), $mode = 's' ) {
        Logger::debug( __METHOD__, 'Query[' . $sql . '] Values[' . var_export( $values, true ) . '] Mode[' . $mode . ']' );
        try {
            return parent::queryRow( $sql, $values, $mode );
        } catch ( Exception $e ) {
            Logger::error( __METHOD__, $e->getMessage() );
            throw $e;
        }
    }

    /**
     * DAO::LogDAO::update
     * ロギングしながら更新系SQL実行
     */
    protected function update ( $sql, $values = array() ) {
        Logger::debug( __METHOD__, 'Query[' . $sql . '] Values[' . var_export( $values, true ) . ']' );
        try {
            return parent::update( $sql, $values );
        } catch ( Exception $e ) {
            Logger::error( __METHOD__, $e->getMessage() );
            throw $e;
        }
    }
}
