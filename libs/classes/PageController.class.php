<?php
require_once( CLASSES_DIR . 'Controller.class.php' );

/**
 * 全てのページの基礎機能を提供する
 * @author yukihiro.kitazawa
 */
class PageController extends Controller
{
	/**
	 * コンストラクタ
	 * ここで全てが実行される
	 * @params function $action
	 */
	public function __construct ( $action ) {
		parent::__construct ( $action );
		Template::show( $this->_template, $this->_datum );
	}

	/**
	 * リダイレクトを行う
	 * @params string $url
	 */
	public function redirect ( $url ) {
		header( 'Location: '.$url );
		exit( 0 );
	}

}
