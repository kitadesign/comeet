<?php

/**
 * 全てのページの基礎機能を提供する
 * @author yukihiro.kitazawa
 */
class PageController
{
	/**
	 * テンプレート名
	 */
	private $_template = '';
	
	/**
	 * HTTP GETにより取得したデータ
	 */
	private $_get      = array();
	
	/**
	 * HTTP POSTにより取得したデータ
	 */
	private $_post     = array();
	
	/**
	 * HTTP REQUEST(POST値とGET値)により取得したデータ
	 */
	private $_request  = array();
	
	/**
	 * COOKIEより取得したデータ
	 */
	private $_cookie   = array();
	
	/**
	 * ユーザセッションのデータ
	 */
	private $_session  = array();
	
	/**
	 * テンプレートに渡す値
	 */
	private $_datum    = array();

	/**
	 * コンストラクタ
	 * ここで全てが実行される
	 * @params function $action
	 */
	public function __construct ( $action ) {
		spl_autoload_register(array($this, '_loader'));

		$this->_get     = $_GET;
		$this->_post    = $_POST;
		$this->_request = $_REQUEST;
		$this->_cookie  = $_COOKIE;
		try {
			ob_start();
			$action( $this );
			$output = ob_get_contents();
			ob_end_clean();
			if ( !empty( $output ) ) error_log( $output );

			Template::show( $this->_template, $this->_datum );
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage() );
			Template::show( 'error',
				array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * データをテンプレートにセットする
	 * @params mix $key
	 * @params array $value
	 */
	public function setData ( $key, $value = array() ) {
		if ( is_array( $key ) ) {
			foreach ( $key as $k => $v ) {
				$this->_datum[$k] = $v;
			}
		} else {
			$this->_datum[$key] = $value;
		}
	}

	/**
	 * テンプレート名をセットする
	 * @params string $templateName
	 */
	public function setTemplate ( $templateName ) {
		$this->_template = $templateName;
	}

	/**
	 * リダイレクトを行う
	 * @params string $url
	 */
	public function redirect ( $url ) {
		header( 'Location: '.$url );
		exit( 0 );
	}

	/**
	 * オートローダー
	 */
	private function _loader ($className) {
		$path = CLASSES_DIR . $className . '.class.php';
		if ( !file_exists( $path ) ) {
			throw new RuntimeException( 'Not found class file['.$path.']', 500 );
		}
		require_once( $path );
	}
}
