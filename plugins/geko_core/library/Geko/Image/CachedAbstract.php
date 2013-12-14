<?php

// abstract class for dynamically generated images by the GD library
abstract class Geko_Image_CachedAbstract
{
	/* /
	// single pixel gif
	const BLANK_IMAGE_DATA = 'R0lGODlhAQABAIAAAP///wAAACH5BAEUAAAALAAAAAABAAEAAAICRAEAOw==';
	const BLANK_IMAGE_SIZE = 43;
	/* */
	
	/* */
	// test file
	const BLANK_IMAGE_DATA = 'R0lGODlhZABkAMQBAAAAAP///54LD0ufze7u7t3d3czMzLu7u6qqqpmZmYiIiHd3d2ZmZlVVVURERDMzMyIiIhEREQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAEALAAAAABkAGQAAAX/4CCOZGmeaDoGbOu+cCzPsWrfuEjvfA/nwODKRyz+hMibcWlMOlPMaO9JLUmvs6p2gO2+tlWvOACmjr3l57mbdq6x7eT7GkfOpXXhPZoP7pl9QH9LgTmDTYVKh0SJiotTjSqPPpGSkzyVUJc7mSibnJ0mnzShoqMypVanNalDq1+tOq+wsbO0rbYusbK5ZLu9LLtcwMLAvrXEv8nIvcXLuM+pxs7NytXMudTZ1tvYttrG4eLj5OXm5+jp6uvs7e7v8PHy8/T19vf48QoA/P3+/QUCFPhHEICCFwoe+Ftw4EVBggwe7XvoL+BAivwOsjAQ4WHEFhj7fTw0MaTFkAZZ/xCAgFFjAJQARl5K0C/GRQABadDkt6DFAZYAIsgA6nLVTgA2Ae6Y+ODFAaUwiNo6mpRfzhklGVzlIXUWVRg3t8Yg0HGhAgI7ur76+uLmQwgunhZ8gGCGWqM1wYaE66IAg4dFXdw9xbbvXhkJFADt1zBqxql520JdUrJBjMGjCrcIS4NAv7ovFvBzcPmx18iGre5QCID0i7+tS6c8za8qzqX9GhhQCXv2C8yfNLNw+9D1SowR0Dr2jbe23pCuBS7+F2G37MCZUW9GGZ2FAgf+HGAXbDqf+fPo088QwL69+/cC1LOAT7+9/AD169/PT38///f+/WeffAK6F2CBBwqY4BV/C/LXYH4P6kdggexF2N+EFFoIXwgAOw==';
	const BLANK_IMAGE_SIZE = 664;
	/* */
	
	protected $sCacheFilePath;
	
	protected static $sCacheDir;
	protected static $bLogging = FALSE;
	
	
	
	////// static methods
	
	//// accessors
	
	//
	public static function setCacheDir( $sCacheDir ) {
		
		if ( DIRECTORY_SEPARATOR != substr( $sCacheDir, strlen( $sCacheDir ) - 1 ) ) {
			// add a trailing '/'
			$sCacheDir .= DIRECTORY_SEPARATOR;
		}
		
		self::$sCacheDir = $sCacheDir;
	}
	
	//
	public static function setLogging( $bLogging ) {
		self::$bLogging = $bLogging;
	}
	
	//// utility methods
	
	//
	public static function resolveLocalImageSrc( $sPath ) {
		
		if ( FALSE == is_file( $sPath ) ) {
			
			// try absolute path
			if ( DIRECTORY_SEPARATOR != substr( $sPath, 0, 1 ) ) {
				$sPath = DIRECTORY_SEPARATOR . $sPath;
			}
			
			$sPath = $_SERVER[ 'DOCUMENT_ROOT' ] . $sPath;
			
			if ( FALSE == is_file( $sPath ) ) {
				return FALSE;
			}
			
		}
		
		// $sPath is good
		return array(
			'src' => $sPath,
			'remote' => FALSE,
			'mtime' => filemtime( $sPath )
		);
	}
	
	//
	public static function resolveImageSrc( $sImageSrc ) {
		
		if ( '' != $sImageSrc ) {
			
			$sHost = parse_url( $sImageSrc, PHP_URL_HOST );
			
			if ( NULL != $sHost ) {
				
				if ( $sHost == $_SERVER[ 'SERVER_NAME' ] ) {
					
					$sPath = parse_url( $sImageSrc, PHP_URL_PATH );
					return self::resolveLocalImageSrc( $sPath );
					
				} else {
					
					// image source given is remote
					
					// security check, ensure referer and server are the same
					// otherwise, the script is probably being hijacked to generate an external image
					// by another site
					
					$sRefererName = parse_url( $_SERVER[ 'HTTP_REFERER' ], PHP_URL_HOST );
					
					if ( $sRefererName == $_SERVER[ 'SERVER_NAME' ] ) {
						
						// proceed
						
						$aRemoteFileInfo = Geko_RemoteFile::getInfo( $sImageSrc );
						
						if ( FALSE != $aRemoteFileInfo ) {
							
							// $aRemoteFileInfo is good
							return array(
								'src' => $sImageSrc,
								'remote' => TRUE,
								'mtime' => strtotime( $aRemoteFileInfo[ 'last-modified' ] )
							);
							
						} else {
							// giving up...
							return FALSE;
						}
						
					} else {
						// hijack in progress!!!
						return FALSE;
					}
				}
				
			} else {
				// image source given is local
				return self::resolveLocalImageSrc( $sImageSrc );
			}
			
		} else {
			return FALSE;
		}
	}
	
	
	
	////// methods
	
	//// accessors
	
	//
	public function setWidth( $iWidth ) {
		$iWidth = intval( preg_replace( "/[^0-9]/", '', $iWidth ) );		
		$this->iWidth = $iWidth;
		return $this;
	}
	
	//
	public function setHeight( $iHeight ) {
		$iHeight = intval( preg_replace( "/[^0-9]/", '', $iHeight ) );		
		$this->iHeight = $iHeight;
		return $this;
	}
	
	//
	public function setWidthHeight( $iWidth, $iHeight ) {
		return $this
			->setWidth( $iWidth )
			->setHeight( $iHeight )
		;
	}
	
	
	
	//// cache generation methods
	
	//
	protected function assertCacheDir() {
		
		if ( FALSE == is_dir( self::$sCacheDir ) ) {
			// attempt to create cache directory
			if ( FALSE == mkdir( self::$sCacheDir, 0744 ) ) {
				// failed to create cache directory
				return FALSE;			
			}
		}
		
		return TRUE;
	}
	
	//
	abstract protected function generateCacheFile();	
	
	
	
	//// file output methods
	
	abstract public function getMimeType();
	
	//
	protected function showBlankImage() {
		
		header( 'Content-Type: image/gif' );
		
		/* /
		$iWidth = ( '' == $this->iWidth ) ? 10 : $this->iWidth;
		$iHeight = ( '' == $this->iHeight ) ? 10 : $this->iHeight;
		
		$rCanvas = imagecreate( $iWidth, $iHeight );
		
		$rImage = imagecreatefromstring( base64_decode( self::BLANK_IMAGE_DATA ) );
		$iCurWidth = imagesx( $rImage );
		$iCurHeight = imagesy( $rImage ); 
		
		imagecopyresampled( $rCanvas, $rImage, 0, 0, 0, 0, $iWidth, $iHeight, $iCurWidth, $iCurHeight );

		imagegif( $rCanvas );
		
		imagedestroy( $rCanvas );
		imagedestroy( $rImage );
		/* */
		
		/* */
		$iWidth = ( '' == $this->iWidth ) ? 10 : $this->iWidth;
		$iHeight = ( '' == $this->iHeight ) ? 10 : $this->iHeight;
		
		$rCanvas = imagecreate( $iWidth, $iHeight );
		imagefill( $rCanvas, 0, 0, imagecolorallocate( $rCanvas, 0x77, 0x77, 0x77 ) );
		
		imagegif( $rCanvas );
		
		imagedestroy( $rCanvas );
		/* */
		
		/* /
		header( 'Content-Length:' . self::BLANK_IMAGE_SIZE );
		echo base64_decode( self::BLANK_IMAGE_DATA );
		/* */
	}
	
	//
	protected function showCachedImage() {
		// send headers then display image
		header( 'Content-Type:' . $this->getMimeType() );
		header( 'Last-Modified:' . gmdate( 'D, d M Y H:i:s', filemtime( $this->sCacheFilePath ) ) . 'GMT' );
		header( 'Content-Length:' . filesize( $this->sCacheFilePath ) );
		header( 'Cache-Control: max-age=9999' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 99999 ) . 'GMT' ); 
		readfile( $this->sCacheFilePath );
	}
	
	//
	abstract public function getCacheFileKey();
	
	// clear the output buffer
	// output headers, cached file if possible, otherwise, show a blank gif
	// kill the script
	public function output() {
		
		// flush the output buffer
		ob_end_clean();
		
		// generate a path to the cache file
		$sCacheFileKey = $this->getCacheFileKey();
		$this->sCacheFilePath = self::$sCacheDir . $sCacheFileKey;
		
		if ( FALSE != $sCacheFileKey ) {
			
			if ( FALSE == is_file( $this->sCacheFilePath ) ) {
				// attempt to generate the cache file if it does not exist
				$this->generateCacheFile();
			}
			
			if ( TRUE == is_file( $this->sCacheFilePath ) ) {
				// show the cached image file
				$this->showCachedImage();
			} else {
				// there were problems generating the cached image file
				$this->showBlankImage();
			}
			
		} else {
			// the orginal image src most likely does not exist
			$this->showBlankImage();
		}
		
		// kill the script
		die();
	}
	
	
	
	// return cache file path and dimensions, if it exists
	// otherwise, return FALSE
	public function get() {
		
		// generate a path to the cache file
		$sCacheFileKey = $this->getCacheFileKey();
		$this->sCacheFilePath = self::$sCacheDir . $sCacheFileKey;
		
		if ( FALSE != $sCacheFileKey ) {
			
			if ( FALSE == is_file( $this->sCacheFilePath ) ) {
				// attempt to generate the cache file if it does not exist
				$this->generateCacheFile();
			}
			
			if ( TRUE == is_file( $this->sCacheFilePath ) ) {
				
				$aSize = getimagesize( $this->sCacheFilePath );
				
				return array(
					'fullpath' => $this->sCacheFilePath,
					'cachekey' => $sCacheFileKey,
					'width' => $aSize[ 0 ],
					'height' => $aSize[ 1 ],
					'mime' => $aSize['mime'],
					'size' => $aSize
				);
				
			} else {
				// there were problems generating the cached image file
				return FALSE;
			}
			
		} else {
			// the orginal image src most likely does not exist
			return FALSE;
		}	
	}
	
	
	
	//// logging/debugging methods
	
	//
	public function logMessage( $sMethod, $sMessage ) {
		
		// make sure cache dir exists
		$this->assertCacheDir();
		
		// TO DO: make log file settable
		$sLogFilePath = self::$sCacheDir . 'logs.txt';
		
		if ( FALSE == is_file( $sLogFilePath ) ) {
			touch( $sLogFilePath );
		}
		
		//
		if ( TRUE == is_file( $sLogFilePath ) ) {
			$sLogMessage = gmdate( 'D, d M Y H:i:s' ) . ': ' . $sMethod . ': ' . $sMessage . "\n";
			file_put_contents( $sLogFilePath, $sLogMessage, FILE_APPEND );
		} else {
			echo $sLogMessage;
		}
	}
	
	
	// 
	public function debug() {
		
		echo '<pre>';
		
		print self::$sCacheDir . 'logs.txt';
		$this->logMessage( __METHOD__, 'Test.' );
		
		print_r( $this );
		var_dump( $this->getCacheFileKey() );
		
		echo '</pre>';
	}
	
	
	
	//// helpers
	
	//
	public static function paramCoalesce( $aParams, $sKeyList ) {
		
		$aArgs = array();
		$aKeyList = explode( '|', $sKeyList );
		
		foreach ( $aKeyList as $sKey ) {
			if ( isset( $aParams[ $sKey ] ) ) $aArgs[] = $aParams[ $sKey ];
		}
		
		return ( count( $aArgs ) > 0 ) ?
			call_user_func_array( array( 'Geko_String', 'coalesce' ), $aArgs ) : 
			NULL
		;
	}
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		//
		if ( 0 === strpos( $sMethod, 'arrSet' ) ) {
			
			// attempt to call set*() method if it exists
			$sCall = substr_replace( $sMethod, 'set', 0, 6 );
			if ( method_exists( $this, $sCall ) ) {
				$mRes = self::paramCoalesce( $aArgs[ 0 ], $aArgs[ 1 ] );
				if ( NULL !== $mRes ) {
					return $this->$sCall( $mRes );
				} else {
					return $this;
				}
			}
			
		}
		
		throw new Exception('Invalid method ' . get_class( $this ) . '::' . $sMethod . '() called.');
	}
	
	
}


