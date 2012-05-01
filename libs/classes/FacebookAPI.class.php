<?php
require_once( CLASSES_DIR . 'fb_sdk/facebook.php' );

class FacebookAPI extends Facebook
{
	private static $_instance;

	public static function getInstance () {
		if ( !isset( self::$_instance ) ) {
			self::$_instance = new FacebookAPI(array(
				'appId'  => Conf::FACEBOOK_APP_ID,
				'secret' => Conf::FACEBOOK_APP_SECRET
			));
		}

		return self::$_instance;
	}

	public function getFacebookId () {
		return '1234567890';
		return $this->getUser();
	}

	public function getUserInfo () {
		return $this->api('/me');
	}

	public function getFriends () {
		return $this->api('/me/friendlists');
	}

	public function getAccessToken () {
		return parent::getAccessToken();
	}

	public function getLoginUrl () {
		$params = array(
			'scope' => 'publish_stream,read_friendlists,user_about_me,user_online_presence,offline_access'
		);
		return parent::getLoginUrl( $params );
	}
}
