<?php
// Error show
error_reporting(E_ALL);
ini_set('display_errors','On');
define('DEBUG_FLAG', true);

// System Path
$dir = realpath(dirname(__FILE__));
define( 'DS', DIRECTORY_SEPARATOR );
define( 'LIBS_DIR', $dir . DS );
define( 'CLASSES_DIR', LIBS_DIR . 'classes' .DS );
define( 'TEMPLATES_DIR', LIBS_DIR . 'templates' . DS );
define( 'LOGS_DIR', dirname($dir) . DS . 'logs' . DS );

// Set up application.
$head   = ( isset( $_SERVER['HTTPS'] ) ) ? 'https://' : 'http://' ;
$domain = ( isset( $_SERVER['HTTP_HOST'] ) ) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] ;
define( 'REQUEST_URL', $head.$domain );
date_default_timezone_set('Asia/Tokyo');
require_once( LIBS_DIR . 'functions.php' );

// Charset UTF-8
ini_set( 'mbstring.language', 'Japanese' );
ini_set( 'mbstring.http_input', 'pass' );
ini_set( 'mbstring.http_output', 'pass' );
ini_set( 'mbstring.detect_order', 'UTF-8,eucJP-win,SJIS-win,JIS,ASCII' );
ini_set( 'mbstring.internal_encoding', 'UTF-8' );
define( 'INTERNAL_ENCODING', 'UTF-8' );

// Error Handle
set_error_handler(function ($errno, $errstr, $errfile, $errline){
	if (!(error_reporting() & $errno)) return;
	switch ($errno) {
	case E_USER_ERROR:
		Logger::error( __METHOD__, 'ErrNo['.$errno.'], ErrStr['.$errstr.'], ErrFile['.$errfile.'], ErrLine['.$errline.']' );
		Template::show( 'error',
			array(
				'code'    => $errno,
				'message' => $errstr,
			)
		);
		exit(1);
	}
});

// Web Application Cookie

// Web Application Session
if (!empty($_SERVER['SERVER_NAME'])) {
	ini_set('session.cookie_domain', $_SERVER['SERVER_NAME']);
	define('IS_SCRIPT', false);
} else {
	ini_set('session.cookie_domain', $_SERVER['HOSTNAME']);
	define('IS_SCRIPT', true);
}
$session_lifetime = 7776000; // 3 month
ini_set('session.name', 'COMEET_SESSION');
ini_set('session.cookie_lifetime', $session_lifetime);
ini_set('session.cache_expire', 180);

ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

session_save_path(dirname($dir) . DS . 'sessions' . DS);
session_set_cookie_params($session_lifetime);


