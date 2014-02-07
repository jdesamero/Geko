<?php

//
class Geko_Browser
{
	
	const DEVICE_TV = 1;
	const DEVICE_TABLET = 2;
	const DEVICE_DESKTOP = 3;
	const DEVICE_MOBILE = 4;
	
	//
	protected static $_aDeviceHash = array(
		1 => 'tv',
		2 => 'tablet',
		3 => 'desktop',
		4 => 'mobile'
	);
	
	protected $_aBrowser = array();
	
	protected static $_oGlobal = NULL;
	
	
	
	//
	public function __construct( $sUserAgent = '' ) {
		$this->_aBrowser = self::detect( $sUserAgent );
	}
	
	
	// "is" methods
	
	//
	public function isOs( $sOs ) {
		return ( $this->_aBrowser[ 'os' ] == $sOs );
	}

	//
	public function isName( $sName ) {
		return ( $this->_aBrowser[ 'name' ] == $sName );
	}

	//
	public function isVersion( $sVersion ) {
		return ( $this->_aBrowser[ 'version' ] == $sVersion );
	}
	
	//
	public function isCode( $sCode ) {
		return ( $this->_aBrowser[ 'code' ] == $sCode );
	}
	
	//
	public function isDevice( $iDevice ) {
		return ( $this->_aBrowser[ 'device' ] == $iDevice );
	}
	
	// "get" methods
	
	//
	public function getBrowser( $bBodyClassFormat = FALSE ) {
		
		if ( $bBodyClassFormat ) {
			return self::bodyClassFormat( $this->_aBrowser );
		}
		
		return $this->_aBrowser;
	}
	
	//
	public function getOs() {
		return $this->_aBrowser[ 'os' ];
	}

	//
	public function getName() {
		return $this->_aBrowser[ 'name' ];	
	}

	//
	public function getVersion() {
		return $this->_aBrowser[ 'version' ];
	}
	
	//
	public function getCode() {
		return $this->_aBrowser[ 'code' ];		
	}
	
	//
	public function getDevice() {
		return $this->_aBrowser[ 'device' ];		
	}
	
	//
	public function getUa() {
		return $this->_aBrowser[ 'ua' ];
	}
	
	
	//// static methods
	
	//
	public static function detect( $sUa = '', $bBodyClassFormat = FALSE ) {
		
		$aRet = array();
		
		if ( !$sUa ) $sUa = $_SERVER[ 'HTTP_USER_AGENT' ];
		
		$aRet[ 'ua' ] = $sUa;
		
		if (
			preg_match( '/bot/i', $sUa ) || 
			preg_match( '/crawl/i', $sUa ) || 
			preg_match( '/yahoo\!/i', $sUa )
		) {
			
			$aRet[ 'name' ] = 'Bot';
			$aRet[ 'version' ] = 'Unknown';
		
		} elseif ( preg_match( '/opera/i', $sUa ) ) {
			
			preg_match( '/Opera(\/| )([0-9\.]+)(u)?(\d+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Opera';
			unset( $aRegs[ 0 ], $aRegs[ 1 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
			
		} elseif ( preg_match( '/(msie|trident)/i', $sUa ) ) {

			$aRet[ 'name' ] = 'Internet Explorer';
			
			if ( preg_match( '/MSIE ([0-9\.]+)(b)?/i', $sUa, $aRegs ) ) {
				unset( $aRegs[ 0 ] );
				$aRet[ 'version' ] = implode( '', $aRegs );
			} else if ( preg_match( '/rv(:| )([0-9\.]+)(a|b)?/i', $sUa, $aRegs ) ) {
				unset( $aRegs[ 0 ], $aRegs[ 1 ] );
				$aRet[ 'version' ] = implode( '', $aRegs );
			}
			
		} elseif ( preg_match( '/omniweb/i', $sUa ) ) {
		
			preg_match( '/OmniWeb\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'OmniWeb';
			
			if ( isset( $aRegs[ 1 ] ) ) $aRet[ 'version' ] = $aRegs[ 1 ];
			else $aRet[ 'version' ] = 'Unknown';

		} elseif ( preg_match( '/chrome/i', $sUa ) ) {
			
			preg_match( '/Chrome\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Chrome';
			$aRet[ 'version' ] = $aRegs[ 1 ];
			
		} elseif ( preg_match( '/icab/i', $sUa ) ) {
		
			preg_match( '/iCab\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'iCab';
			$aRet[ 'version' ] = $aRegs[ 1 ];
			
		} elseif ( preg_match( '/safari/i', $sUa ) ) {

			$aRet[ 'name' ] = 'Safari';
			
			$aRegs2 = array();
			if ( preg_match( '/Version\/([0-9\.]+)/i', $sUa, $aRegs2 ) ) {
				
				$aRet[ 'version' ] = $aRegs2[ 1 ];
			
			} else {
			
				preg_match( '/Safari\/([0-9\.]+)/i', $sUa, $aRegs );
				$aRet[ 'version' ] = $aRegs[ 1 ];
				
				$aVers = explode( '.', $aRet[ 'version' ] );
				$iVers1 = intval( $aVers[ 0 ] );
				$iVers2 = intval( $aVers[ 1 ] );
				$iVers3 = intval( $aVers[ 2 ] );
				
				if ( 85 == $iVers1 ) {
					if ( 5 == $iVers2 ) {
						$aRet[ 'version' ] = '1.0';
					} elseif ( 7 == $iVers2 ) {
						$aRet[ 'version' ] = '1.0.2';				
					} elseif ( 8 == $iVers2 ) {
						$aRet[ 'version' ] = '1.0.3';				
					} else {
						$aRet[ 'version' ] = '1.0.x';				
					}
				} elseif ( 100 == $iVers1 ) {
					if ( 0 == $iVers2 ) {
						$aRet[ 'version' ] = '1.1';		
					} elseif ( 1 == $iVers2 ) {
						$aRet[ 'version' ] = '1.1.1';			
					} else {
						$aRet[ 'version' ] = '1.1.x';				
					}
				} elseif ( 125 == $iVers1 ) {
					if ( 7 == $iVers2 || 8 == $iVers2 ) {
						$aRet[ 'version' ] = '1.2.2';				
					} elseif ( 9 == $iVers2 ) {
						$aRet[ 'version' ] = '1.2.3';				
					} elseif ( 11 == $iVers2 || 12 == $iVers2 ) {
						$aRet[ 'version' ] = '1.2.4';				
					} else {
						$aRet[ 'version' ] = '1.2.x';					
					}
				} elseif ( 312 == $iVers1 ) {
					if ( 0 == $iVers2 ) {
						$aRet[ 'version' ] = '1.3';
					} elseif ( 3 == $iVers2 ) {
						$aRet[ 'version' ] = '1.3.1';
					} else {
						$aRet[ 'version' ] = '1.3.x';				
					}
				} elseif ( $iVers1 >= 412 && $iVers1 <= 416 ) {
					if ( 412 == $iVers1 ) {
						if ( $iVers2 >= 0 && $iVers2 <= 2 ) {
							$aRet[ 'version' ] = '2.0';				
						} elseif ( 5 == $iVers2 ) {
							$aRet[ 'version' ] = '2.0.1';				
						} else {
							$aRet[ 'version' ] = '2.0.x';					
						}
					} elseif ( 416 == $iVers1 ) {
						if ( 12 == $iVers2 || 13 == $iVers2 ) {
							$aRet[ 'version' ] = '2.0.2';					
						} else {
							$aRet[ 'version' ] = '2.0.x';					
						}
					} else {
						$aRet[ 'version' ] = '2.0.x';
					}
				}
				
			}
			
		} elseif ( preg_match( '/konqueror/i', $sUa ) ) {
		
			preg_match( '/Konqueror\/([0-9\.]+)(\-rc)?(\d+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Konqueror';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/Flock/i', $sUa) ) {
		
			preg_match( '/Flock\/([0-9\.]+)(\+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Flock';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/firebird/i', $sUa ) ) {
		
			preg_match( '/Firebird\/([0-9\.]+)(\+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Firebird';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/phoenix/i', $sUa ) ) {
			
			preg_match( '/Phoenix\/([0-9\.]+)(\+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Phoenix';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/firefox/i', $sUa ) ) {
		
			preg_match( '/Firefox\/([0-9\.]+)(\+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Firefox';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/chimera/i', $sUa ) ) {
		
			preg_match( '/Chimera\/([0-9\.]+)(a|b)?(\d+)?(\+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Chimera';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/camino/i', $sUa ) ) {
		
			preg_match( '/Camino\/([0-9\.]+)(a|b)?(\d+)?(\+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Camino';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/seamonkey/i', $sUa ) ) {
		
			preg_match( '/SeaMonkey\/([0-9\.]+)(a|b)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'SeaMonkey';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/galeon/i', $sUa ) ) {
		
			preg_match( '/Galeon\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Galeon';
			$aRet[ 'version' ] = $aRegs[ 1 ];
		
		} elseif ( preg_match( '/epiphany/i', $sUa ) ) {
		
			preg_match( '/Epiphany\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Epiphany';
			$aRet[ 'version' ] = $aRegs[ 1 ];
		
		} elseif (
			preg_match( '/mozilla\/5/i', $sUa) || 
			preg_match( '/gecko/i', $sUa )
		) {
			
			preg_match( '/rv(:| )([0-9\.]+)(a|b)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Mozilla';
			unset( $aRegs[ 0 ], $aRegs[ 1 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/mozilla\/4/i', $sUa ) ) {
		
			preg_match( '/Mozilla\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Netscape';
			$aRet[ 'version' ] = $aRegs[ 1 ];
		
		} elseif ( preg_match( '/lynx/i', $sUa ) ) {
		
			preg_match( '/Lynx\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Lynx';
			$aRet[ 'version' ] = $aRegs[ 1 ];
		
		} elseif ( preg_match( '/links/i', $sUa ) ) {
		
			preg_match( '/Links \(([0-9\.]+)(pre)?(\d+)?/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Links';
			unset( $aRegs[ 0 ] );
			$aRet[ 'version' ] = implode( '', $aRegs );
		
		} elseif ( preg_match( '/curl/i', $sUa ) ) {
		
			preg_match( '/curl\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'cURL';
			$aRet[ 'version' ] = $aRegs[ 1 ];
		
		} elseif ( preg_match( '/wget/i', $sUa ) ) {
		
			preg_match( '/Wget\/([0-9\.]+)/i', $sUa, $aRegs );
			$aRet[ 'name' ] = 'Wget';
			$aRet[ 'version' ] = $aRegs[ 1 ];
		
		} else {
		
			$aRet[ 'name' ] = 'Unknown-browser';
			$aRet[ 'version' ] = 'Unknown-version';
			
		}
		
		
		// detect os
		if ( preg_match( '/macintosh/i', $sUa ) ) {
			$aRet[ 'os' ] = 'macintosh';
		} elseif ( preg_match( '/windows/i', $sUa ) ) {
			$aRet[ 'os' ] = 'windows';
		} elseif ( preg_match( '/iphone/i', $sUa ) ) {
			$aRet[ 'os' ] = 'iphone';
		} elseif ( preg_match( '/ipad/i', $sUa ) ) {
			$aRet[ 'os' ] = 'ipad';		
		} else {
			$aRet[ 'os' ] = 'unkown-os';
		}
		
		
		// detect device type
		
		if (
			// Check if user agent is a smart TV - http://goo.gl/FocDk
			( preg_match( '/GoogleTV|SmartTV|Internet.TV|NetCast|NETTV|AppleTV|boxee|Kylo|Roku|DLNADOC|CE\-HTML/i', $sUa ) ) || 
			// Check if user agent is a TV Based Gaming Console
			( preg_match( '/Xbox|PLAYSTATION.3|Wii/i', $sUa ) )
		) {
			
			$aRet[ 'device' ] = self::DEVICE_TV;
			
		} else if (
			// Check if user agent is a Tablet
			( ( preg_match( '/iP(a|ro)d/i', $sUa ) ) || ( preg_match( '/tablet/i', $sUa ) ) && ( !preg_match( '/RX-34/i', $sUa ) ) || ( preg_match( '/FOLIO/i', $sUa ) ) ) || 
			// Check if user agent is an Android Tablet
			( ( preg_match( '/Linux/i', $sUa ) ) && ( preg_match( '/Android/i', $sUa ) ) && ( !preg_match( '/Fennec|mobi|HTC.Magic|HTCX06HT|Nexus.One|SC-02B|fone.945/i', $sUa ) ) ) || 
			// Check if user agent is a Kindle or Kindle Fire
			( ( preg_match( '/Kindle/i', $sUa ) ) || ( preg_match( '/Mac.OS/i', $sUa ) ) && ( preg_match( '/Silk/i', $sUa ) ) ) || 
			// Check if user agent is a pre Android 3.0 Tablet
			(
				( preg_match( '/GT-P10|SC-01C|SHW-M180S|SGH-T849|SCH-I800|SHW-M180L|SPH-P100|SGH-I987|zt180|HTC(.Flyer|\_Flyer)|Sprint.ATP51|ViewPad7|pandigital(sprnova|nova)|Ideos.S7|Dell.Streak.7|Advent.Vega|A101IT|A70BHT|MID7015|Next2|nook/i', $sUa ) ) || 
				( preg_match( '/MB511/i', $sUa ) ) && ( preg_match( '/RUTEM/i', $sUa ) )
			)
		) {
			
			$aRet[ 'device' ] = self::DEVICE_TABLET;
			
		} else if (
			// Check if user agent is unique Mobile User Agent
			( preg_match( '/BOLT|Fennec|Iris|Maemo|Minimo|Mobi|mowser|NetFront|Novarra|Prism|RX-34|Skyfire|Tear|XV6875|XV6975|Google.Wireless.Transcoder/i', $sUa ) ) || 
			// Check if user agent is an odd Opera User Agent - http://goo.gl/nK90K
			( (preg_match('/Opera/i', $sUa) ) && ( preg_match( '/Windows.NT.5/i', $sUa ) ) && ( preg_match( '/HTC|Xda|Mini|Vario|SAMSUNG\-GT\-i8000|SAMSUNG\-SGH\-i9/i', $sUa ) ) )
		) {
			
			$aRet[ 'device' ] = self::DEVICE_MOBILE;
			
		} else if (
			// Check if user agent is Windows Desktop
			( ( preg_match( '/Windows.(NT|XP|ME|9)/', $sUa ) ) && ( !preg_match( '/Phone/i', $sUa ) ) || ( preg_match( '/Win(9|.9|NT)/i', $sUa ) ) ) || 
			// Check if agent is Mac Desktop
			( ( preg_match( '/Macintosh|PowerPC/i', $sUa ) ) && ( !preg_match( '/Silk/i', $sUa ) ) ) || 
			// Check if user agent is a Linux Desktop
			( (preg_match( '/Linux/i', $sUa ) ) && ( preg_match( '/X11/i', $sUa ) ) ) || 
			// Check if user agent is a Solaris, SunOS, BSD Desktop
			( preg_match( '/Solaris|SunOS|BSD/i', $sUa ) ) || 
			// Check if user agent is a Desktop BOT/Crawler/Spider
			( (preg_match( '/Bot|Crawler|Spider|Yahoo|ia_archiver|Covario-IDS|findlinks|DataparkSearch|larbin|Mediapartners-Google|NG-Search|Snappy|Teoma|Jeeves|TinEye/i', $sUa ) ) && ( !preg_match( '/Mobile/i', $sUa ) ) )
		) {
			
			$aRet[ 'device' ] = self::DEVICE_DESKTOP;
			
		} else {
			
			// Otherwise assume it is a Mobile Device
			$aRet[ 'device' ] = self::DEVICE_MOBILE;
			
		}
		
		// normalize
		$aRet = array_map( array( __CLASS__, 'normalize' ), $aRet );
		
		
		$aCodeMap = array(
			'internet-explorer' => 'ie',
			'firefox' => 'ff',			
			'chrome' => 'ch',
			'safari' => 'sa'			
		);
		
		// special
		$sBrowserName = $aRet[ 'name' ];
		$sVersion = $aRet[ 'version' ];
		
		if ( array_key_exists( $sBrowserName, $aCodeMap ) ) {
			
			$sCodePfx = $aCodeMap[ $sBrowserName ];
			
			$iFirst = intval( substr( $sVersion, 0, strpos( $sVersion, '-' ) ) );
			
			$aRet[ 'code' ] = sprintf( '%s%d', $sCodePfx, $iFirst );
		}
		
		if ( $bBodyClassFormat ) {
			$aRet = self::bodyClassFormat( $aRet );
		}
		
		return $aRet;
		
	}
	
	
	
	//
	public static function bodyClass( $sUa = '' ) {
		
		$aRet = self::detect( $sUa, TRUE );
		
		return implode( ' ', $aRet );
	}
	
	
	//
	protected static function normalize( $sValue ) {
		return strtolower( str_replace( array( ' ', '.' ), '-', $sValue ) );
	}
	
	
	//
	protected static function bodyClassFormat( $aRet ) {
		
		// make $aRet values CSS friendly
		
		$aRet[ 'os' ] = sprintf( 'os_%s', $aRet[ 'os' ] );
		$aRet[ 'name' ] = sprintf( 'vendor_%s', $aRet[ 'name' ] );
		$aRet[ 'version' ] = sprintf( 'ver_%s', $aRet[ 'version' ] );
		$aRet[ 'device' ] = sprintf( 'dev_%s', self::$_aDeviceHash[ $aRet[ 'device' ] ] );
		
		unset( $aRet[ 'ua' ] );
		
		return array_values( $aRet );
	}
	
	
	//
	public static function getGlobal() {
		
		if ( !self::$_oGlobal ) {
			$sClass = __CLASS__;
			self::$_oGlobal = new $sClass();
		}
		
		return self::$_oGlobal;
	}
	
	
}


