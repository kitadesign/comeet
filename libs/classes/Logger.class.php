<?php
// Logger class
class Logger
{

	const DEFAILT_LOG_NAME = 'debug';

	private static $_logger;

	private $_logFile;

	private function __construct($dir,$file){
		$this->_logFile = $dir . $file . '.txt';
	}

	public static function getInstance($logName = self::DEFAILT_LOG_NAME){
		if(!self::$_logger){
			$dir  = LOGS_DIR;
			$file = date('Ymd-').$logName;
			self::$_logger = new self($dir,$file);
		}
		return self::$_logger;
   }

	public function error($method, $str = ''){
		$self = self::getInstance();
		$method = $self->getObjectString($method);
		$str = $self->getObjectString($str);
		$message = $self->getStamp()."[Error]$method $str";
		$self->write($message);
	}

	public function warning($method, $str = ''){
		$self = self::getInstance();
		$method = $self->getObjectString($method);
		$str = $self->getObjectString($str);
		$message = $self->getStamp()."[Warning]$method $str";
		$self->write($message);
	}

	public function info($method, $str = ''){
		$self = self::getInstance();
		$method = $self->getObjectString($method);
		$str = $self->getObjectString($str);
		$message = $self->getStamp()."[Info]$method $str";
		$self->write($message);
	}

	public function debug($method, $str = ''){
		if(!defined('DEBUG_FLAG')) return;
		$self = self::getInstance();
		$method = $self->getObjectString($method);
		$str = $self->getObjectString($str);
		$message = $self->getStamp()."[Debug]$method $str";
		$self->write($message);
	}

	private function write($message){
		error_log($message."\n", 3, $this->_logFile);
	}

	private function getStamp(){
		return '['.date('Y/m/d H:i:s').']';
	}

	private function getObjectString($str){
		if(is_object($str)){
			$str = var_export($str, true);
		}else if(is_array($str)){
			$str = var_export($str, true);
		}

		return $str;
	}
}
