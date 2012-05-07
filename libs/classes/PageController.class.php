<?php
require_once( CLASSES_DIR . 'Controller.class.php' );

/**
 * HTMLページの基礎機能を提供するコントローラ
 * @Author yukihiro.kitazawa
 */
class PageController extends Controller
{
	private $_loadJavaScriptFile = array();

	/**
	 * コンストラクタ
	 * ここで全てが実行される
	 * @params function $action
	 */
	public function __construct ( $action ) {
		parent::__construct ( $action );
		if ( count( $this->_loadJavaScriptFile ) > 0 )
			$this->setData( '_header_loadJS_', $this->_loadJavaScriptFile );
		Template::show( $this->_template, $this->_datum );
	}

	/**
	 * 画面にどのJSを読み込むか設定する
	 * @params string $filename
	 */
	public function setJavaScript ( $filename ){
		$this->_loadJavaScriptFile[] = $filename;
	}

	/**
	 * 画面のタイトルを設定する
	 * @params string $title
	 */
	public function setTitle ( $title ) {
		$this->setData( '_header_title_', $title );
	}

	public function setSignature ( $signatureName, $signatureBase ) {
		$signature = getSignature( $signatureName, $signatureBase );
		$this->setData( '_footer_signature_', $signature );
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
