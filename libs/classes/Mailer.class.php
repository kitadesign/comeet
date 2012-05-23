<?php

require_once( CLASSES_DIR . 'mailer/phpmailer.inc.php' );
require_once( CLASSES_DIR . 'mailer/jphpmailer.php' );

/**
 * メール送信機能
 */
class Mailer
{
	/**
	 * インスタンス
	 */
	private static $_instance;

	/**
	 * メーラーオブジェクト
	 */
	private $_jphpmailer;

	/**
	 * インスタンス取得
	 */
	public static function getInstance () {
		if ( !isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * コンストラクタ
	 * メーラーを生成する
	 */
	private function __construct () {
		$this->_jphpmailer = new JPHPMailer();
	}

	/**
	 * Meetなうをメール送信する
	 */
	public function sendJPMeetNow ( $fromFacebookId, $fromEmailAddress, $fromName, $fromPR, $fromCompanyName, $fromCompanyUrl, $fromCompanyTel, 
										$toEmailAddress, $toName, $meetingTag, $profileTags, $meetingContent = '' ) {
		Logger::info( __METHOD__, 'FROM:' . $fromEmailAddress . ' TO:' . $toEmailAddress );
		if ( empty( $toEmailAddress ) ) return 0;
//		$this->_jphpmailer->addTo( $toEmailAddress );
		$this->_jphpmailer->addTo( 'circumflex.inc@gmail.com' );
		$this->_jphpmailer->AddReplyTo( $fromEmailAddress );
		$this->_jphpmailer->setFrom( Conf::EMAIL_MEET_NOW_ADMIN, $fromName );

		$htmlbody = $this->_getHTMLMailTemplate( 'meet_now', array(
			'from_icon'         => sprintf( Conf::FACEBOOK_IMAGE_URL_S, $fromFacebookId ),
			'from_name'         => $fromName,
			'from_pr'           => $fromPR,
			'from_company_name' => $fromCompanyName,
			'from_company_url'  => $fromCompanyUrl,
			'from_company_tel'  => $fromCompanyTel,
			'to_name'           => $toName,
			'meeting_tag'       => $meetingTag,
			'meeting_content'   => $meetingContent,
			'request_url'       => REQUEST_URL . '/?__from=mail',
			'profile_tags'      => $profileTags,
		) );

		$this->_jphpmailer->setSubject( sprintf( Conf::EMAIL_MEET_NOW_SUBJECT, $fromName, $meetingTag ) );
		$this->_jphpmailer->setHtmlBody( $htmlbody );
		$this->_jphpmailer->setAltBody( strip_tags( $htmlbody ) );

		$this->_jphpmailer->send();

		$message = $this->getErrorMessage();
		if ( !empty( $message ) ) {
			Logger::error( __METHOD__, $message );
			return 0;
		}
		return 1;
	}

	/**
	 * エラーメッセージを取得する
	 */
	public function getErrorMessage () {
		return $this->_jphpmailer->getErrorMessage();
	}

	/**
	 * HTMLメールのテンプレートを取得する
	 */
	private function _getHTMLMailTemplate ( $templateName, $replace = array() ) {
		$output = ob_get_contents();
		ob_end_clean();
		if ( !empty( $output ) ) error_log( $output );

		ob_start();
		Template::show( 'email' . DS . $templateName, $replace );
		$output = ob_get_contents();
		ob_end_clean();
		ob_start();
		return $output;
	}

}
