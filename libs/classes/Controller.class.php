<?php

class Controller
{

	/**
	 * テンプレート名
	 */
	protected $_template = '';
	
	/**
	 * HTTP GETにより取得したデータ
	 */
	protected $_get      = array();
	
	/**
	 * HTTP POSTにより取得したデータ
	 */
	protected $_post     = array();
	
	/**
	 * HTTP REQUEST(POST値とGET値)により取得したデータ
	 */
	protected $_request  = array();
	
	/**
	 * COOKIEより取得したデータ
	 */
	protected $_cookie   = array();
	
	/**
	 * テンプレートに渡す値
	 */
	protected $_datum    = array();

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
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage() );
			$this->setData( 'code', $e->getCode() );
			$this->setData( 'message', $e->getMessage() );
			$this->setTemplate( 'error' );
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

	public function getPostData ( $key ) {
		if ( !isset($this->_post[$key]) ) return null;
		return $this->_post[$key];
	}

	public function getGetData ( $key ) {
		if ( !isset($this->_get[$key]) ) return null;
		return $this->_get[$key];
	}

	public function getRequestData ( $key ) {
		if ( !isset($this->_request[$key]) ) return null;
		return $this->_request[$key];
	}

	public function getCookieData ( $key ) {
		if ( !isset($this->_cookie[$key]) ) return null;
		return $this->_cookie[$key];
	}

	/**
	 * テンプレート名をセットする
	 * @params string $templateName
	 */
	public function setTemplate ( $templateName ) {
		$this->_template = $templateName;
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
