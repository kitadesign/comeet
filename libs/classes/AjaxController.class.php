<?php
require_once( CLASSES_DIR . 'Controller.class.php' );

class AjaxController extends Controller
{
	/**
	 * コンストラクタ
	 * ここで全てが実行される
	 * @params function $action
	 */
	public function __construct ( $action ) {
		parent::__construct ( $action );
		$this->_outputJson( $this->_datum );
	}

	private function _outputJson ( $datum ) {
		var_export( $datum );
	}
}
