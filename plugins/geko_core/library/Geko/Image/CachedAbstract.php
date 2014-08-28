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
	
	
	protected static $sCacheDir;
	
	protected $_iWidth;
	protected $_iHeight;
	
	
	
	////// static methods
	
	//// accessors
	
	//
	public static function setCacheDir( $sCacheDir ) {
		self::$sCacheDir = self::addTrailingDirSep( $sCacheDir );
	}
	
	
	//// utility methods
	
	//
	protected static function addTrailingDirSep( $sPath ) {
		
		if ( DIRECTORY_SEPARATOR != substr( $sPath, strlen( $sPath ) - 1 ) ) {
			// add a trailing '/'
			$sPath .= DIRECTORY_SEPARATOR;
		}
		
		return $sPath;
	}
	
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
		$this->_iWidth = $iWidth;
		return $this;
	}
	
	//
	public function setHeight( $iHeight ) {
		$iHeight = intval( preg_replace( "/[^0-9]/", '', $iHeight ) );		
		$this->_iHeight = $iHeight;
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
		
		if ( !is_dir( self::$sCacheDir ) ) {
			
			// attempt to create cache directory
			
			if ( !mkdir( self::$sCacheDir, 0744 ) ) {
				
				// failed to create cache directory
				return FALSE;			
			}
		}
		
		return TRUE;
	}
	
	
	//
	protected function generateCacheFile() { }
	
	
	//
	public function savePermanentFile( $sFilePath ) {
		
		if ( $sFilePath = trim( $sFilePath ) ) {
			
			$sFile = basename( $sFilePath );
			$sDir = dirname( $sFilePath );
			
			// check that directory exists
			if ( is_dir( $sDir ) ) {
				
				$this->generateCacheFile();
				
				if ( !is_file( $sFilePath ) ) {
					
					return copy(
						$this->getCacheFilePath(),
						$sFilePath
					);				
				}
				
			}
		}
		
		return FALSE;
	}
	
	
	
	//// file output methods
	
	abstract public function getMimeType();
	
	//
	protected function showBlankImage() {
		
		header( 'Content-Type: image/gif' );
		
		/* /
		$iWidth = ( '' == $this->_iWidth ) ? 10 : $this->_iWidth;
		$iHeight = ( '' == $this->_iHeight ) ? 10 : $this->_iHeight;
		
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
		$iWidth = ( '' == $this->_iWidth ) ? 10 : $this->_iWidth;
		$iHeight = ( '' == $this->_iHeight ) ? 10 : $this->_iHeight;
		
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
		
		$sCacheFilePath = $this->getCacheFilePath();
		
		// send headers then display image
		header( sprintf( 'Content-Type: %s', $this->getMimeType() ) );
		header( sprintf( 'Last-Modified: %sGMT', gmdate( 'D, d M Y H:i:s', filemtime( $sCacheFilePath ) ) ) );
		header( sprintf( 'Content-Length: %s', filesize( $sCacheFilePath ) ) );
		header( 'Cache-Control: max-age=9999' );
		header( sprintf( 'Expires: %sGMT', gmdate( 'D, d M Y H:i:s', time() + 99999 ) ) ); 
		
		readfile( $sCacheFilePath );
	}
	
	//
	abstract public function getCacheFileKey();
	
	//
	public function getCacheFilePath() {
		
		if ( $sCacheFileKey = $this->getCacheFileKey() ) {
			return sprintf( '%s%s', self::$sCacheDir, $sCacheFileKey );
		}
		
		return FALSE;
	}
	
	
	
	// clear the output buffer
	// output headers, cached file if possible, otherwise, show a blank gif
	// kill the script
	public function output() {
		
		// flush the output buffer
		ob_end_clean();
		
		$sCacheFilePath = $this->getCacheFilePath();
		
		if ( $sCacheFilePath ) {
			
			if ( FALSE == is_file( $sCacheFilePath ) ) {
				// attempt to generate the cache file if it does not exist
				$this->generateCacheFile();
			}
			
			if ( TRUE == is_file( $sCacheFilePath ) ) {
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
		$sCacheFilePath = $this->getCacheFilePath();
		
		if ( $sCacheFileKey ) {
			
			if ( !is_file( $sCacheFilePath ) ) {
				// attempt to generate the cache file if it does not exist
				$this->generateCacheFile();
			}
			
			if ( is_file( $sCacheFilePath ) ) {
				
				$aSize = getimagesize( $sCacheFilePath );
				
				return array(
					'fullpath' => $sCacheFilePath,
					'cachekey' => $sCacheFileKey,
					'width' => $aSize[ 0 ],
					'height' => $aSize[ 1 ],
					'mime' => $aSize[ 'mime' ],
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
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', get_class( $this ), $sMethod ) );
	}
	
	
}


