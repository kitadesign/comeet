<?php
require_once( CLASSES_DIR . 'Controller.class.php' );

/**
 * Ajax処理の基礎機能を提供するコントローラ
 * @Author yukihiro.kitazawa
 */
class AjaxController extends Controller
{
	/**
	 * コンストラクタ
	 * ここで全てが実行される
	 * @params function $action
	 */
	public function __construct ( $action, $type = 'json' ) {
		parent::__construct ( $action );
		$this->_output( $type );
	}

	/**
	 * 呼び出しが適切であるか認証を行う
	 */
	public function isValidCall ( $checkName, $checkBase, $authType = 'signature' ) {
		$postAuthType = $this->getPostData( 'auth_type' );
		if ( $authType !== $postAuthType ) return false;
		switch ( $authType ) {
			case 'signature':
			default:
				$signature = $this->getPostData( 'signature' );
				if ( isMatchSignature( $checkName, $checkBase, $signature ) ) return true;
				return false;
		}
	}

	/**
	 * 出力方法を指定してアウトプットする
	 */
	private function _output( $type ) {
		if ( $this->_template == 'error' ) {
			header( 'HTTP/1.0 500 Internal Server Error' );
			exit( 1 );
		}
		$datum = $this->_datum;
		$output = '';
		switch ( $type ) {
		case 'php':
			$output = $this->_getOutputPhp( $datum );
			break;
		case 'xml':
			$output = $this->_getOutputXml( $datum );
			break;
		case 'json':
		default:
			$output = $this->_getOutputJson( $datum );
			break;
		}
		header( 'pragma: no-cache' );
		header( 'cache-control: no-cache' );
		header( 'expires: 0' );
		echo( $output );
	}

	/**
	 * PHPシリアライズ形式のデータを取得する
	 */
	private function _getOutputPhp ( $datum ) {
		return serialize( $datum );
	}
	
	/**
	 * XML形式のデータを取得する
	 */
	private function _getOutputXml ( $datum ) {
		return $this->_getAsXml( $datum );
	}
	
	/**
	 * XML構造体を作る
	 */
	private function _getAsXml ( $datum, $rootNodeName = 'data', $xml = null ) {
		if ( $xml == null) {
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}

		foreach ( $datum as $key => $value ) {
			if ( is_numeric( $key ) ) {
				$key = "unknownNode_". (string) $key;
			}
			$key = preg_replace('/[^a-z]/i', '', $key);
			if ( is_array( $value ) ) {
				$node = $xml->addChild($key);
				$this->_outputXml( $value, $rootNodeName, $xml );
			} else {
				$value = htmlentities($value);
				$xml->addChild($key,$value);
			}
		}
		return $xml->asXML();
	}

	/**
	 * JSON形式のデータを取得する
	 */
	private function _getOutputJson ( $datum ) {
		header( 'Content-Type: text/javascript; charset=utf-8' );
		return json_encode( $datum );
	}

	/**
	 * バリデーションエラー
	 */
	protected function badRequestError () {
		$output = ob_get_contents();
		ob_end_clean();
		if ( !empty( $output ) ) error_log( $output );

		header( 'HTTP/1.0 400 Bad Request' );
		exit();
	}
}
