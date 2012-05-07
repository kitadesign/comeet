<?php
require_once( CLASSES_DIR . 'fb_sdk/facebook.php' );

class FacebookAPI
{
	private static $_instance;
	private $_facebook;

	public static function getInstance () {
		if ( !isset( self::$_instance ) ) {
			self::$_instance = new FacebookAPI(array(
				'appId'  => Conf::FACEBOOK_APP_ID,
				'secret' => Conf::FACEBOOK_APP_SECRET
			));
		}

		return self::$_instance;
	}

	private function __construct () {
		$this->_facebook = new Facebook(array(
				'appId'  => Conf::FACEBOOK_APP_ID,
				'secret' => Conf::FACEBOOK_APP_SECRET
			));
	}

	/**
	 * FacebookIDを取得する
	 */
	public function getFacebookId () {
		return $this->_facebook->getUser();
	}

	/**
	 * メンバー情報を取得する
	 */
	public function getUserInfo () {
		return $this->_facebook->api('/me');
	}

	/**
	 * 友人一覧を取得する
	 */
	public function getFriends () {
		return $this->_facebook->api('/me/friendlists');
	}

	/**
	 * メンバーのアクセストークンを取得する
	 */
	public function getAccessToken () {
		return $this->_facebook->getAccessToken();
	}

	/**
	 * ログインURLを取得する
	 */
	public function getLoginUrl () {
		$params = array(
			'scope' => 'publish_stream,read_friendlists,user_about_me,user_online_presence,offline_access'
//			'scope' => 'publish_stream,read_friendlists,user_about_me,user_online_presence'
		);
		return $this->_facebook->getLoginUrl( $params );
	}
}
