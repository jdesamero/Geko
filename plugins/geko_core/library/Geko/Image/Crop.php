<?php

/*

// request parameters that can be sent to this class

$aParams = array(
	'src|source'							=> [absolute location of file],
	'w|wdt|width'							=> [width],
	'h|hgt|height'							=> [height],
	'x|xoff|xoffset'						=> [x-offset],
	'y|yoff|yoffset'						=> [y-offset],
	'o|om|offsetmethod'						=> [offset method, 'p' in pixels, 'u' for width/height units],
	'q|qlty|quality'						=> [quality (default is 75 and max is 100)],
	'mtime|modificationtime'				=> [date modification timestamp],
	'rmt|remote'							=> [TRUE or FALSE]
);

*/


class Geko_Image_Crop extends Geko_Image_CachedAbstract
{
	
	protected $_sImageSrc;
	protected $_iXOffset = 0;
	protected $_iYOffset = 0;
	protected $_sOffsetMethod = 'p';
	protected $_iQuality = 80;
	protected $_iModifiedTimestamp;
	protected $_bIsRemote = FALSE;
	
	
	
	////// static methods
	
	
	
	
	////// methods
	
	// constructor
	public function __construct( $aParams = array() ) {
		
		if ( $aResolveParams = self::resolveImageSrc( self::paramCoalesce( $aParams, 'src|source' ) ) ) {
			$aParams = array_merge( $aParams, $aResolveParams );
		}
		
		$this
			->arrSetImageSrc( $aParams, 'src|source' )
			->arrSetWidth( $aParams, 'w|wdt|width' )
			->arrSetHeight( $aParams, 'h|hgt|height' )
			->arrSetXOffset( $aParams, 'x|xoff|xoffset' )
			->arrSetYOffset( $aParams, 'y|yoff|yoffset' )
			->arrSetOffsetMethod( $aParams, 'o|om|offsetmethod' )
			->arrSetQuality( $aParams, 'q|qlty|quality' )
			->arrSetModifiedTimestamp( $aParams, 'mtime|modtime|modificationtime' )
			->arrSetIsRemote( $aParams, 'rmt|remote' )
		;
	}
	
	
	
	//// accessors
	
	//
	public function setImageSrc( $sImageSrc ) {
		$this->_sImageSrc = $sImageSrc;
		return $this;
	}
	
	//
	public function setXOffset( $iXOffset ) {
		$iXOffset = intval( preg_replace( "/[^0-9-]/", '', $iXOffset ) );		
		$this->_iXOffset = $iXOffset;
		return $this;
	}
	
	//
	public function setYOffset( $iYOffset ) {
		$iYOffset = intval( preg_replace( "/[^0-9-]/", '', $iYOffset ) );		
		$this->_iYOffset = $iYOffset;
		return $this;
	}
	
	//
	public function setXyOffset( $iXOffset, $iYOffset ) {
		return $this
			->setXOffset( $iXOffset )
			->setYOffset( $iYOffset )
		;
	}

	//
	public function setOffsetMethod( $sOffsetMethod ) {
		$sOffsetMethod = strtolower( $sOffsetMethod );
		if ( in_array( $sOffsetMethod, array( 'p', 'u' ) ) ) {
			$this->_sOffsetMethod = $sOffsetMethod;		
		}
		return $this;
	}
	
	//
	public function setQuality( $iQuality ) {
		
		$iQuality = intval( preg_replace( "/[^0-9]/", '', $iQuality ) );
		if ( !$iQuality ) $iQuality = 80;
		
		$this->_iQuality = $iQuality;
		return $this;
	}
	
	//
	public function setModifiedTimestamp( $iModifiedTimestamp ) {
		$this->_iModifiedTimestamp = $iModifiedTimestamp;
		return $this;
	}
	
	//
	public function setIsRemote( $bIsRemote ) {
		$this->_bIsRemote = $bIsRemote;
		return $this;
	}
	

	
	////
	
	//
	public function getMimeType() {
		
		$aAllowedMimeTypes = array(
			'image/jpeg',
			'image/png',
			'image/gif'
		);
		
		$sMime = Geko_File_MimeType::get( $this->_sImageSrc );
		
		// if mime type was not determined, use the file extension
		if ( !$sMime ) {
			$sExt = strtolower( pathinfo( $this->_sImageSrc, PATHINFO_EXTENSION ) );
			if ( 'jpg' == $sExt || 'jpeg' == $sExt ) $sMime = 'image/jpeg';
			elseif ( 'png' == $sExt ) $sMime = 'image/png';
			elseif ( 'gif' == $sExt ) $sMime = 'image/gif';
		}
		
		if ( in_array( $sMime, $aAllowedMimeTypes ) ) {
			return $sMime;
		} else {
			// mime type not allowed
			Geko_Debug::out( sprintf( 'Mime type not allowed: %s', $this->_sImageSrc ), __METHOD__ );
			return '';
		}
	}
	
	//
	protected function generateCacheFile() {
		
		//// do checks
		
		// gd library
		if ( FALSE == function_exists( 'imagecreatetruecolor' ) ) {
			// gd library is not installed
			Geko_Debug::out( 'GD image library is not installed.', __METHOD__ );
			return FALSE;
		}
		
		// file/mime type
		if ( '' == ( $sMimeType = $this->getMimeType() ) ) {
			// incorrect kind of file specified
			return FALSE;
		}
		
		Geko_Debug::out( sprintf( 'Attempting to create cropped file with mime type: %s', $sMimeType ), __METHOD__ );
		
		// make sure cache directory exists
		if ( FALSE == $this->assertCacheDir() ) {
			return FALSE;
		}
		
		// create cache file
		$sCacheFilePath = $this->getCacheFilePath();
		
		if ( FALSE == touch( $sCacheFilePath ) ) {
			Geko_Debug::out( sprintf( 'Failed to create cache file. %s', $sCacheFilePath ), __METHOD__ );
			return FALSE;
		}
		
		
		
		//// generate cache file
		
		// check if image is remote or local, then open it
		
		if ( TRUE == $this->_bIsRemote ) {
			
			// image is remote
			$rImage = imagecreatefromstring( Geko_RemoteFile::getContents( $this->_sImageSrc ) );
			
		} else {
			
			// image is local
			if ( TRUE == stristr( $sMimeType, 'gif' ) ) {
				$rImage = imagecreatefromgif( $this->_sImageSrc );
			} elseif ( TRUE == stristr( $sMimeType, 'png' ) ) {
				$rImage = imagecreatefrompng( $this->_sImageSrc );
			} else {
				// jpeg is default
				$rImage = imagecreatefromjpeg( $this->_sImageSrc );
			}		
		}
		
		if ( FALSE == $rImage ) {
			Geko_Debug::out( sprintf( 'GD failed to open image: %s', $this->_sImageSrc ), __METHOD__ );
			return FALSE;
		}
		
		Geko_Debug::out( sprintf( 'Attempting to create cropped file from source: %s', $this->_sImageSrc ), __METHOD__ );
		
		// Get original width and height
		$iCurWidth = imagesx( $rImage );
		$iCurHeight = imagesy( $rImage );

		$iWidth = ( $this->_iWidth ) ? $this->_iWidth : $this->_iHeight;
		$iHeight = ( $this->_iHeight ) ? $this->_iHeight : $this->_iWidth;
		
		if ( 'u' == $this->_sOffsetMethod ) {
			// use width/height as offset units
			$iXOffset = $iWidth * $this->_iXOffset;
			$iYOffset = $iHeight * $this->_iYOffset;			
		} else {
			// default, offset is in pixel values
			$iXOffset = $this->_iXOffset;
			$iYOffset = $this->_iYOffset;		
		}
		
		// if offsets go beyond width/height then throw an exception
		if (
			( $iXOffset < 0 ) || ( $iXOffset >= $iCurWidth ) || 
			( $iYOffset < 0 ) || ( $iYOffset >= $iCurHeight )
		) {
			unlink( $sCacheFilePath );
			throw new Exception( sprintf( 'Offset values are out of bounds for: %s', __METHOD__ ) );
			return FALSE;
		}
		
		// create a new true color image
		$rCanvas = imagecreatetruecolor( $iWidth, $iHeight );

		imagecopyresampled( $rCanvas, $rImage, 0, 0, $iXOffset, $iYOffset, $iWidth, $iHeight, $iWidth, $iHeight );
		
		// write the image to file
		if ( TRUE == stristr( $sMimeType, 'gif' ) ) {
			imagegif( $rCanvas, $sCacheFilePath );
		} elseif( TRUE == stristr( $sMimeType, 'png' ) ) {
			imagepng( $rCanvas, $sCacheFilePath, ceil( $this->_iQuality / 10 ) );
		} else {
			// jpeg is default
			imagejpeg( $rCanvas, $sCacheFilePath, $this->_iQuality );
		}
		
		Geko_Debug::out( sprintf( 'Cache image created: %s', $sCacheFilePath ), __METHOD__ );
		
		// free up memory
		imagedestroy( $rImage );
		imagedestroy( $rCanvas );
		
	}
	
	
	
	
	//
	public function getCacheFileKey() {
		
		if ( '' == $this->_sImageSrc ) {
			
			// image source given is empty
			Geko_Debug::out( 'Image source given is empty.', __METHOD__ );
			return FALSE;
			
		} else {

			// this should create a unique "signature" for the cached file
			return md5(
				'%s_%d_%d_%d_%d_%s_%d_%d_%d',
				$this->_sImageSrc,
				$this->_iWidth,
				$this->_iHeight,
				$this->_iXOffset,
				$this->_iYOffset,
				$this->_sOffsetMethod,
				$this->_iQuality,
				$this->_iModifiedTimestamp,
				intval( $this->_bIsRemote )
			);
			
		}
	}
	
	
	//
	public function buildThumbUrl( $sThumbUrl, $bRetObj = FALSE ) {
		
		$oUrl = new Geko_Uri( $sThumbUrl );
		$oUrl
			->setVar( 'src', $this->_sImageSrc, FALSE )
			->setVar( 'w', $this->_iWidth, FALSE )
			->setVar( 'h', $this->_iHeight, FALSE )
			->setVar( 'x', $this->_iXOffset, FALSE )
			->setVar( 'y', $this->_iYOffset, FALSE )
			->setVar( 's', $this->_sOffsetMethod, FALSE )
			->setVar( 'q', $this->_iQuality, FALSE )
			->setVar( 'mtime', $this->_iModifiedTimestamp, FALSE )
			->setVar( 'rmt', intval( $this->_bIsRemote ), FALSE )
		;
		
		return ( $bRetObj ) ? $oUrl : strval( $oUrl );
	}
	
	
	
}


