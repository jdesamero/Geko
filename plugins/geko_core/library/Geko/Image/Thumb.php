<?php

// Based on TimThumb script created by Tim McDaniels and Darren Hoyt with tweaks by Ben Gillbanks for the Mimbo Pro theme
// May be re-used pending permission of the authors, email cutout@gmail.com

// Further Tweaks by Joel Desamero

// Copyright 2008

/*

// request parameters that can be sent to this class

$aParams = array(
	'src|source'							=> [absolute location of file],
	'w|wdt|width'							=> [width],
	'h|hgt|height'							=> [height],
	'zc|zmcp|zoomcrop|zoomorcrop'			=> [zoom crop (0 or 1)],
	'cva|cropvalign|cropverticalalign'		=> [(t|top)|(m|middle)|(b|bottom) default is middle],
	'cha|crophalign|crophorizontalalign'	=> [(l|left)|(c|center)|(r|right) default is center],
	'q|qlty|quality'						=> [quality (default is 75 and max is 100)],
	'scl|scale'								=> [scale (percentage), eg: 50 = 50%],
	'mtime|modificationtime'				=> [date modification timestamp],
	'rmt|remote'							=> [TRUE or FALSE]
);

*/

// either width or height can be used
// example: <img src="/resizeImage.php?src=images/image.jpg&h=150" alt="some image" />

class Geko_Image_Thumb extends Geko_Image_CachedAbstract
{
	
	protected $sImageSrc;
	protected $bZoomOrCrop = TRUE;
	protected $sCropVerticalAlign = 'm';
	protected $sCropHorizontalAlign = 'c';
	protected $iQuality = 80;
	protected $iScale;
	protected $iModifiedTimestamp;
	protected $bIsRemote = FALSE;
	
	
	
	
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
			->arrSetZoomOrCrop( $aParams, 'zc|zmcp|zoomcrop|zoomorcrop' )
			->arrSetCropVerticalAlign( $aParams, 'cva|cropvalign|cropverticalalign' )
			->arrSetCropHorizontalAlign( $aParams, 'cha|crophalign|crophorizontalalign' )
			->arrSetQuality( $aParams, 'q|qlty|quality' )
			->arrSetScale( $aParams, 'scl|scale' )
			->arrSetModifiedTimestamp( $aParams, 'mtime|modtime|modificationtime' )
			->arrSetIsRemote( $aParams, 'rmt|remote' )
		;
	}
	
	
	
	//// accessors
	
	//
	public function setImageSrc( $sImageSrc ) {
		$this->sImageSrc = $sImageSrc;
		return $this;
	}
	
	//
	public function setZoomOrCrop( $bZoomOrCrop ) {
		
		$bZoomOrCrop = intval( preg_replace( "/[^0-9]/", '', $bZoomOrCrop ) );
		$bZoomOrCrop = ( $bZoomOrCrop ) ? TRUE : FALSE;
		
		$this->bZoomOrCrop = $bZoomOrCrop;
		return $this;
	}
	
	//
	public function setCropVerticalAlign( $sCropVerticalAlign ) {
		
		$sCropVerticalAlign = substr( strtolower( $sCropVerticalAlign ), 0, 1 );
		
		if ( !in_array( $sCropVerticalAlign, array( 't', 'm', 'b' ) ) ) {
			$sCropVerticalAlign = 'm';			// default middle
		}
		
		$this->sCropVerticalAlign = $sCropVerticalAlign;
		return $this;
	}
	
	//
	public function setCropHorizontalAlign( $sCropHorizontalAlign ) {
		
		$sCropHorizontalAlign = substr( strtolower( $sCropHorizontalAlign ), 0, 1 );
		
		if ( !in_array( $sCropHorizontalAlign, array( 'l', 'c', 'r' ) ) ) {
			$sCropHorizontalAlign = 'c';		// default center
		}
		
		$this->sCropHorizontalAlign = $sCropHorizontalAlign;
		return $this;
	}
	
	//
	public function setQuality( $iQuality ) {
		
		$iQuality = intval( preg_replace( "/[^0-9]/", '', $iQuality ) );
		if ( !$iQuality ) $iQuality = 80;
		
		$this->iQuality = $iQuality;
		return $this;
	}
	
	//
	public function setScale( $iScale ) {
		$iScale = intval( preg_replace( "/[^0-9]/", '', $iScale ) );
		$this->iScale = $iScale;
		return $this;
	}
	
	//
	public function setModifiedTimestamp( $iModifiedTimestamp ) {
		$this->iModifiedTimestamp = $iModifiedTimestamp;
		return $this;
	}
	
	//
	public function setIsRemote( $bIsRemote ) {
		$this->bIsRemote = $bIsRemote;
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
		
		$sMime = Geko_File_MimeType::get( $this->sImageSrc );
		
		// if mime type was not determined, use the file extension
		if ( !$sMime ) {
			$sExt = strtolower( pathinfo( $this->sImageSrc, PATHINFO_EXTENSION ) );
			if ( 'jpg' == $sExt || 'jpeg' == $sExt ) $sMime = 'image/jpeg';
			elseif ( 'png' == $sExt ) $sMime = 'image/png';
			elseif ( 'gif' == $sExt ) $sMime = 'image/gif';
		}
		
		if ( in_array( $sMime, $aAllowedMimeTypes ) ) {
			return $sMime;
		} else {
			// mime type not allowed
			Geko_Debug::out( sprintf( 'Mime type not allowed: %s', $this->sImageSrc ), __METHOD__ );
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
		
		Geko_Debug::out( sprintf( 'Attempting to create thumbnail file with mime type: %s', $sMimeType ), __METHOD__ );
		
		// make sure cache directory exists
		if ( FALSE == $this->assertCacheDir() ) {
			Geko_Debug::out( 'Failed to assert cache directory.', __METHOD__ );
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
		
		if ( TRUE == $this->bIsRemote ) {
			
			// image is remote
			$rImage = imagecreatefromstring( Geko_RemoteFile::getContents( $this->sImageSrc ) );
			
		} else {
			
			// image is local
			if ( TRUE == stristr( $sMimeType, 'gif' ) ) {
				$rImage = imagecreatefromgif( $this->sImageSrc );
			} elseif ( TRUE == stristr( $sMimeType, 'png' ) ) {
				$rImage = imagecreatefrompng( $this->sImageSrc );
			} else {
				// jpeg is default
				$rImage = imagecreatefromjpeg( $this->sImageSrc );
			}		
		}
		
		if ( FALSE == $rImage ) {
			Geko_Debug::out( sprintf( 'GD failed to open image: %s', $this->sImageSrc ), __METHOD__ );
			return FALSE;
		}
		
		Geko_Debug::out( sprintf( 'Attempting to create thumbnail file from source: %s', $this->sImageSrc ), __METHOD__ );
		
		// Get original width and height
		$iCurWidth = imagesx( $rImage );
		$iCurHeight = imagesy( $rImage );
		
		// calculate width/height values if not provided
		if ( ( '' == $this->iWidth ) || ( '' == $this->iHeight ) ) {
			
			if ( ( '' != $this->iHeight ) && ( '' == $this->iWidth ) ) {
				// height value given, but not width
				$iHeight = $this->iHeight;
				$iWidth = $iCurWidth * ( $this->iHeight / $iCurHeight );
			} elseif ( ( '' != $this->iWidth ) && ( '' == $this->iHeight ) ) {
				// width value given, but not height
				$iWidth = $this->iWidth;
				$iHeight = $iCurHeight * ( $this->iWidth / $iCurWidth );
			} else {
				// width and height values not given
				
				// check if scale factor was given
				if ( '' != $this->iScale ) {
					// scale image width and height
					$fScale = $this->iScale / 100;
					$iWidth = round( $iCurWidth * $fScale );
					$iHeight = round( $iCurHeight * $fScale );
				} else {
					// no scaling
					$iWidth = $iCurWidth;
					$iHeight = $iCurHeight;
				}
				
			}
			
		} else {
			$iWidth = $this->iWidth;
			$iHeight = $this->iHeight;
		}
		
		// create a new true color image
		$rCanvas = imagecreatetruecolor( $iWidth, $iHeight );
		
		if ( TRUE == $this->bZoomOrCrop ) {
			
			$iSrcX = $iSrcY = 0;
			$iSrcW = $iCurWidth;
			$iSrcH = $iCurHeight;
			
			$iCmpX = $iCurWidth  / $iWidth;
			$iCmpY = $iCurHeight / $iHeight;
			
			// calculate x or y coordinate and width or height of source
			if ( $iCmpX > $iCmpY ) {
				
				// left/right of image will be cropped
				$iSrcW = round( ( $iCurWidth / $iCmpX * $iCmpY ) );
				
				if ( 'l' == $this->sCropHorizontalAlign ) {
					// align top
					$iSrcX = 0;
				} elseif ( 'r' == $this->sCropHorizontalAlign ) {
					// align bottom
					$iSrcX = round( $iCurWidth - ( $iCurWidth / $iCmpX * $iCmpY ) );
				} else {
					// default middle
					$iSrcX = round( ( $iCurWidth - ( $iCurWidth / $iCmpX * $iCmpY ) ) / 2 );
				}
				
			} elseif ( $iCmpY > $iCmpX ) {
				
				// top/bottom of image will be cropped
				$iSrcH = round( ( $iCurHeight / $iCmpY * $iCmpX ) );
				
				if ( 't' == $this->sCropVerticalAlign ) {
					// align left
					$iSrcY = 0;
				} elseif ( 'b' == $this->sCropVerticalAlign ) {
					// align right
					$iSrcY = round( $iCurHeight - ( $iCurHeight / $iCmpY * $iCmpX ) );
				} else {
					// default center
					$iSrcY = round( ( $iCurHeight - ( $iCurHeight / $iCmpY * $iCmpX ) ) / 2 );
				}
				
			}
			
			imagecopyresampled( $rCanvas, $rImage, 0, 0, $iSrcX, $iSrcY, $iWidth, $iHeight, $iSrcW, $iSrcH );
			
		} else {
			
			// copy and resize part of an image with resampling
			imagecopyresampled( $rCanvas, $rImage, 0, 0, 0, 0, $iWidth, $iHeight, $iCurWidth, $iCurHeight );
			
		}
		
		// write the image to file
		if ( TRUE == stristr( $sMimeType, 'gif' ) ) {
			imagegif( $rCanvas, $sCacheFilePath );
		} elseif( TRUE == stristr( $sMimeType, 'png' ) ) {
			imagepng( $rCanvas, $sCacheFilePath, ceil( $this->iQuality / 10 ) );
		} else {
			// jpeg is default
			imagejpeg( $rCanvas, $sCacheFilePath, $this->iQuality );
		}
		
		Geko_Debug::out( sprintf( 'Cache image created: %s', $sCacheFilePath ), __METHOD__ );
		
		// free up memory
		imagedestroy( $rImage );
		imagedestroy( $rCanvas );
		
	}
	
	
	//
	public function getCacheFileKey() {
		
		if ( '' == $this->sImageSrc ) {
			
			// image source given is empty
			Geko_Debug::out( 'Image source given is empty.', __METHOD__ );
			return FALSE;
			
		} else {

			// this should create a unique "signature" for the cached file
			return md5(
				$this->sImageSrc . '_' .
				$this->iWidth . '_' .
				$this->iHeight . '_' .
				intval( $this->bZoomOrCrop ) . '_' .
				$this->sCropVerticalAlign . '_' .
				$this->sCropHorizontalAlign . '_' .
				$this->iQuality . '_' .
				$this->iScale . '_' .
				$this->iModifiedTimestamp . '_' .
				intval( $this->bIsRemote )
			);
			
		}
	}
	
	
	//
	public function buildThumbUrl( $sThumbUrl, $bRetObj = FALSE ) {
		
		$oUrl = new Geko_Uri( $sThumbUrl );
		$oUrl
			->setVar( 'src', $this->sImageSrc, FALSE )
			->setVar( 'w', $this->iWidth, FALSE )
			->setVar( 'h', $this->iHeight, FALSE )
			->setVar( 'zc', intval( $this->bZoomOrCrop ), FALSE )
			->setVar( 'cva', $this->sCropVerticalAlign, FALSE )
			->setVar( 'cha', $this->sCropHorizontalAlign, FALSE )
			->setVar( 'q', $this->iQuality, FALSE )
			->setVar( 'scl', $this->iScale, FALSE )
			->setVar( 'mtime', $this->iModifiedTimestamp, FALSE )
			->setVar( 'rmt', intval( $this->bIsRemote ), FALSE )
		;
		
		return ( $bRetObj ) ? $oUrl : strval( $oUrl );
	}
	
		
	
}


