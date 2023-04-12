<?php

/**
 * 定数関係
 */
class Conf
{
	/**
	 * システムが参照するFacebookアプリID
	 */
	const FACEBOOK_APP_ID = '***';
	
	/**
	 * システムが参照するFacebookアプリシークレット
	 */
	const FACEBOOK_APP_SECRET = '******';

	/**
	 * シグネチャのソルト
	 */
	const SIGNATURE_SOLT = '*********';

	const JA_EDIT_BUTTON           = '編集';
	const JA_SAVE_BUTTON           = '保存';
	const JA_MEET_NOW_BUTTON       = 'Meetなう';
	const JA_MEET_TEXTAREA_DEFAULT = '相談内容を記載してください';

	const TOAST_SAVED             = '保存しました';
	const TOAST_NOT_SAVE          = "保存できませんでした\n入力内容を見直してください";
	const TOAST_SERVER_ERROR      = "サーバが混雑しているため\n少し時間をあけて再度実行してください";
	const TOAST_NOT_GET           = "データが取得できませんでした";
	const TOAST_NOT_CONNECT       = 'サーバと通信できませんでした\n通信環境をお確かめください';
	const TOAST_REQUEST_MEET_NOW  = "ミーティングを提案しました\nアジェンダを作成して返答をお待ちください";

	const REQUEST_MEET_NOW_FOR_FB_MESSAGE        = "%sさんと%sについて話したい。";
	const REQUEST_LIKE_FRIEND_FOR_FB_MESSAGE     = "%sさんを最も一緒に仕事がしたい3名の取締役に選びました。";
	const REQUEST_LIKE_FRIEND_FOR_FB_DESCRIPTION = 'Comeetは、取締役のための招待制ソーシャルビジネスネットワーキングサービスです。';
	const REQUEST_UPDATE_MEETING_TAG_MESSAGE     = '%sについて話したい。';

	const EMAIL_MEET_NOW_SUBJECT = '%sさんが%sについてComeetからMeetなう提案がありました';

	const MEMBER_DB_MST_DSN = 'mysql:dbname=comeet;host=localhost';
	const MEMBER_DB_BAK_DSN = 'mysql:dbname=comeet;host=localhost';
	const MEMBER_DB_SLV_DSN = 'mysql:dbname=comeet;host=localhost';
	const MEMBER_DB_USER    = '*********';
	const MEMBER_DB_PSWD    = '*********';

	const FACEBOOK_ID_NODE     = 'facebook-id';
	const FACEBOOK_IMAGE_URL_S = '//graph.facebook.com/%s/picture?type=square';
	const DEFAULT_IMAGE_URL    = '/shared/images/dummy.jpg';

	public static $_MEMBER_CACHE_SERVERS = array(
		'localhost',
	);

	const CACHE_EXPIRE = 86400; // 1day

	const EMAIL_MEET_NOW_ADMIN = 'noreply@comeet.asia';

	/**
	 * 活動場所ID
	 */
	public static $LOCATION_ID = array(
		'1' => '北海道',
		'2' => '青森県',
		'3' => '岩手県',
		'4' => '宮城県',
		'5' => '秋田県',
		'6' => '山形県',
		'7' => '福島県',
		'8' => '茨城県',
		'9' => '栃木県',
		'10' => '群馬県',
		'11' => '埼玉県',
		'12' => '千葉県',
		'13' => '東京都',
		'14' => '神奈川県',
		'15' => '新潟県',
		'16' => '富山県',
		'17' => '石川県',
		'18' => '福井県',
		'19' => '山梨県',
		'20' => '長野県',
		'21' => '岐阜県',
		'22' => '静岡県',
		'23' => '愛知県',
		'24' => '三重県',
		'25' => '滋賀県',
		'26' => '京都府',
		'27' => '大阪府',
		'28' => '兵庫県',
		'29' => '奈良県',
		'30' => '和歌山県',
		'31' => '鳥取県',
		'32' => '島根県',
		'33' => '岡山県',
		'34' => '広島県',
		'35' => '山口県',
		'36' => '徳島県',
		'37' => '香川県',
		'38' => '愛媛県',
		'39' => '高知県',
		'40' => '福岡県',
		'41' => '佐賀県',
		'42' => '長崎県',
		'43' => '熊本県',
		'44' => '大分県',
		'45' => '宮崎県',
		'46' => '鹿児島県',
		'47' => '沖縄県',
		'99' => '海外',
	);
}

