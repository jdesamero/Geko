<?php

// contains shared code/constants between various integration classes

class Geko_Integration
{
	
	const GLOBALS = 0;
	const SERVER = 1;
	const GET = 2;
	const POST = 3;
	const FILES = 4;
	const COOKIE = 5;
	const SESSION = 6;
	const REQUEST = 7;
	const ENV = 8;
	
	
	//
	public function _setSuperValue($iType, $sKey, $sValue) {
		switch ($iType) {
			case self::GLOBALS:
				$GLOBALS[$sKey] = $sValue;
				break;
			case self::SERVER:
				$_SERVER[$sKey] = $sValue;
				break;
			case self::GET:
				$_GET[$sKey] = $sValue;
				break;
			case self::POST:
				$_POST[$sKey] = $sValue;
				break;
			case self::FILES:
				$_FILES[$sKey] = $sValue;
				break;
			case self::COOKIE:
				$_COOKIE[$sKey] = $sValue;
				break;
			case self::SESSION:
				$_SESSION[$sKey] = $sValue;
				break;
			case self::REQUEST:
				$_REQUEST[$sKey] = $sValue;
				break;
			case self::ENV:
				$_ENV[$sKey] = $sValue;
				break;
		}
		return $this;
	}

	//
	public function _getSuperValue($iType, $sKey = '') {
		
		if ( '' == $sKey ) {
			
			switch ($iType) {
				case self::GLOBALS:
					return $GLOBALS;
				case self::SERVER:
					return $_SERVER;
				case self::GET:
					return $_GET;
				case self::POST:
					return $_POST;
				case self::FILES:
					return $_FILES;
				case self::COOKIE:
					return $_COOKIE;
				case self::SESSION:
					return $_SESSION;
				case self::REQUEST:
					return $_REQUEST;
				case self::ENV:
					return $_ENV;
			}		
			
		} else {
			
			switch ($iType) {
				case self::GLOBALS:
					return $GLOBALS[$sKey];
				case self::SERVER:
					return $_SERVER[$sKey];
				case self::GET:
					return $_GET[$sKey];
				case self::POST:
					return $_POST[$sKey];
				case self::FILES:
					return $_FILES[$sKey];
				case self::COOKIE:
					return $_COOKIE[$sKey];
				case self::SESSION:
					return $_SESSION[$sKey];
				case self::REQUEST:
					return $_REQUEST[$sKey];
				case self::ENV:
					return $_ENV[$sKey];
			}
			
		}
		
	}
	
	
}


