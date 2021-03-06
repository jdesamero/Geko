<?php

/*

// request parameters that can be sent to this class

$aParams = array(
	't|txt|text'							=> [text to be generated],
	'sz|size'								=> [point size of font],
	'k|kern|kerning'						=> [kerning value in pixels],
	'fgc|fgcol|fgcolor|foregroundcolor'		=> [hex value, eg: 777 or 777777],
	'bgc|bgcol|bgcolor|backgroundcolor'		=> [hex value, eg: 777 or 777777],
	'fnt|font|fontfile'						=> [path to font file],	
	'm|mgn|margin'							=> [margin in pixels, eg: (10: all sides, 10|20: vertical and horizontal, 10|5|3|6: top, right, bottom, left]
	'w|wdt|width'							=> [force image width],
	'h|hgt|height'							=> [force image height],
	'resmp|resample|resamplefactor'			=> [resampling factor, default is 4],
	'sh|shrp|sharpen'						=> [apply sharpening, default is 0],
	'vo|vertoff|verticaloffset'				=> [default is 1]
);

*/

class Geko_Image_KernedText extends Geko_Image_CachedAbstract
{
	
	public static $sFontDir;
	
	
	//// properties
	
	public $_sText = '{NO TEXT SPECIFIED}';
	public $_iFontSize = 9;
	public $_iKerning = 0;
	public $_mForegroundColor = 'fff';
	public $_mBackgroundColor = '000';
	public $_sFontFile;
	public $_mMargin = '0';
	public $_iResampleFactor = 4;
	public $_bSharpen;
	public $_fVerticalOffset = 1;
	
	
	
	
	////// static methods
	
	//// accessors
		
	//
	public static function setFontDir( $sFontDir ) {
		self::$sFontDir = self::addTrailingDirSep( $sFontDir );
	}
	
		
	
	
	
	////// methods
	
	// constructor
	public function __construct( $aParams = array() ) {
		
		$this
			->arrSetText( $aParams, 't|txt|text' )
			->arrSetFontSize( $aParams, 'sz|size' )
			->arrSetKerning( $aParams, 'k|kern|kerning' )
			->arrSetForegroundColor( $aParams, 'fgc|fgcol|fgcolor|foregroundcolor' )
			->arrSetBackgroundColor( $aParams, 'bgc|bgcol|bgcolor|backgroundcolor' )
			->arrSetFontFile( $aParams, 'fnt|font|fontfile' )
			->arrSetMargin( $aParams, 'm|mgn|margin' )
			->arrSetWidth( $aParams, 'w|wdt|width' )
			->arrSetHeight( $aParams, 'h|hgt|height' )
			->arrSetResampleFactor( $aParams, 'resmp|resample|resamplefactor' )
			->arrSetSharpen( $aParams, 'sh|shrp|sharpen' )
			->arrSetVerticalOffset( $aParams, 'vo|vertoff|verticaloffset' )
		;
	}
	
	
	//// accessors
	
	//
	public function setText( $sText ) {
		
		$this->_sText = $sText;
		
		return $this;
	}

	//
	public function setFontSize( $iFontSize ) {
		
		$this->_iFontSize = $iFontSize;

		return $this;
	}

	//
	public function setKerning( $iKerning ) {
		
		$this->_iKerning = $iKerning;

		return $this;
	}
	
	//
	public function setForegroundColor( $mForegroundColor ) {
		
		$this->_mForegroundColor = $mForegroundColor;		
		
		return $this;
	}

	//
	public function setBackgroundColor( $mBackgroundColor ) {
		
		$this->_mBackgroundColor = $mBackgroundColor;		
		
		return $this;
	}
	
	//
	public function setFontFile( $sFontFile ) {
		
		$this->_sFontFile = $sFontFile;

		return $this;
	}
	
	//
	public function setMargin( $mMargin ) {
		
		$this->_mMargin = $mMargin;

		return $this;
	}
	
	//
	public function setResampleFactor( $iResampleFactor ) {
		
		$this->_iResampleFactor = $iResampleFactor;

		return $this;
	}

	//
	public function setSharpen( $bSharpen ) {
		$this->_bSharpen = $bSharpen;

		return $this;
	}

	//
	public function setVerticalOffset( $fVerticalOffset ) {
		
		$this->_fVerticalOffset = $fVerticalOffset;

		return $this;
	}
	
	
	//
	public function getMimeType() {
		return 'image/png';
	}
	
	//
	protected function generateCacheFile() {
		
		//// do checks

		// gd library
		if ( FALSE == function_exists( 'imagettfbbox' ) ) {
			
			// gd library is not installed
			Geko_Debug::out( 'GD image library with Freetype support is not installed.', __METHOD__ );
			return FALSE;
		}
		
		// make sure font exists
		
		$sFontFile = sprintf( '%s%s', self::$sFontDir, $this->_sFontFile );
		
		if ( FALSE == is_file( $sFontFile ) ) {
			
			// cannot find font file
			Geko_Debug::out( 'Cannot find font file.', __METHOD__ );
			return FALSE;
			
		} else {
			
			if ( 'ttf' != strtolower( pathinfo( $this->_sFontFile, PATHINFO_EXTENSION ) ) ) {
				
				// font specified is not a TrueType font
				Geko_Debug::out( 'Font file must be TrueType.', __METHOD__ );
				return FALSE;
			}
		}
		
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
		
		
		
		////// DO IT!!!
		
		$sText = $this->_sText;
		$iFontSize = $this->_iFontSize;
		$iKerningValue = $this->_iKerning;
		$aTextColor = Geko_Image_Color::getArray( $this->_mForegroundColor );
		$aBgColor = Geko_Image_Color::getArray( $this->_mBackgroundColor );
		$aMargin = Geko_Image_Margin::getArray( $this->_mMargin );
		$iForceHeight = $this->_iWidth;
		$iForceWidth = $this->_iHeight;
		$iResampleFactor = $this->_iResampleFactor;
		
		////
				
		$iKerningValue = $iKerningValue * $iResampleFactor;
		
		
		// getting bounding box 
		$aBBox = imagettfbbox( $iFontSize * $iResampleFactor, 0, $sFontFile, $sText );
		$iBoundedHeight = abs( $aBBox[ 7 ] - $aBBox[ 1 ] );
		$iBoundedY = $iBoundedHeight - abs( $aBBox[ 1 ] );
		// imagettfbbox returns very strange results 
		// so transforming them to plain width and height 
		
		$iWidth = abs( $aBBox[ 2 ] - $aBBox[ 0 ] ) + ( $iKerningValue * ( strlen( $sText ) - 1 ) );
		// width: right corner X - left corner X
		//print ' ';
		
		$iHeight = abs( $aBBox[ 7 ] - $aBBox[ 1 ] );
		// height: top Y - bottom Y
		
		$iX = -abs($aBBox[0]);
		$iY = $iHeight - abs($aBBox[1]);

		Geko_Debug::out(
			sprintf(
				'Info: $iFontSize: %d, $iHeight: %d, $iY: %d, $aBBox[1]: %s, $sFontFile: %s, $iResampleFactor: %d, $aBBox: (%s)',
				$iFontSize, $iHeight, $iY, $aBBox[ 1 ], $sFontFile, $iResampleFactor, implode( ', ', $aBBox )
			),
			__METHOD__
		);
		
		// ---- CREATE CANVAS + PALETTE
		$rCanvas = imagecreatetruecolor( $iWidth, $iHeight );
		
		$iBgColor = imagecolorallocate( $rCanvas, $aBgColor[ 'r' ], $aBgColor[ 'g' ], $aBgColor[ 'b' ] );
		$iTextColor = imagecolorallocate( $rCanvas, $aTextColor[ 'r' ], $aTextColor[ 'g' ], $aTextColor[ 'b' ] );
		
		$iBlackColor = imagecolorallocate( $rCanvas, 0, 0, 0 );
		$iWhiteColor = imagecolorallocate( $rCanvas, 255, 255, 255 );
		
		imagefill( $rCanvas, 0, 0, $iBgColor );
		
		// ---- DRAW
		
		$aChars = str_split( $sText );
		$iCharOffset = $iX;
		
		$iYVO = intval( $iY * $this->_fVerticalOffset );
		
		for ( $i = 0; $i < count( $aChars ); $i++ ) {
			$aBBox = imagettftext( $rCanvas, $iFontSize * $iResampleFactor, 0, $iCharOffset, $iYVO, $iTextColor, $sFontFile, $aChars[ $i ] );
			$iCharOffset = $aBBox[ 2 ] + $iKerningValue;    
		}
		
		// ---- SAMPLE DOWN & OUTPUT
		$iFinalWidth = ( $iForceWidth ) ? $iForceWidth : round( ( $iCharOffset - $iKerningValue ) / $iResampleFactor ) + ( $aMargin[ 1 ] + $aMargin[ 3 ] );
		$iFinalHeight = ( $iForceHeight ) ? $iForceHeight : round( $iBoundedHeight / $iResampleFactor ) + ( $aMargin[ 0 ] + $aMargin[ 2 ] );
		
		$rFinal = imagecreatetruecolor( $iFinalWidth, $iFinalHeight );
		imagefill( $rFinal, 0, 0, $iBgColor );
		
		// calculate height of ascender
		$iAscender = ( $iY - $iBoundedY ) / $iResampleFactor;
		
		imagecopyresampled( $rFinal, $rCanvas, $aMargin[ 3 ], round( $aMargin[ 0 ] - $iAscender ), 0, 0, round( $iWidth / $iResampleFactor ), round( $iHeight / $iResampleFactor ), $iWidth, $iHeight );
		
		if (
			( TRUE == class_exists( 'Geko_Image_Sharpen' ) ) &&
			( TRUE == $this->_bSharpen )
		) {
			// apply sharpening
			$rFinal = Geko_Image_Sharpen::unsharpMask( $rFinal, 50, 0.5, 3 );
		}
		
		imagepng( $rFinal, $sCacheFilePath );
		
		Geko_Debug::out( sprintf( 'Cache image created: %s', $sCacheFilePath ), __METHOD__ );
		
		// free up memory
		imagedestroy( $rCanvas );
		imagedestroy( $rFinal );
	}
	
	//
	public function getCacheFileKey() {
		// this should create a unique "signature" for the cached file
		return md5( sprintf(
			'%s_%d_%d_%s_%s_%s_%s_%d_%d_%d_%d_%f',
			$this->_sText,
			$this->_iFontSize,
			$this->_iKerning,
			implode( '_', Geko_Image_Color::getArray( $this->_mForegroundColor ) ),
			implode( '_', Geko_Image_Color::getArray( $this->_mBackgroundColor ) ),
			$this->_sFontFile,
			implode( '_', Geko_Image_Margin::getArray( $this->_mMargin ) ),
			$this->_iWidth,
			$this->_iHeight,
			$this->_iResampleFactor,
			intval( $this->_bSharpen ),
			$this->_fVerticalOffset
		) );
	}
	
	
	//
	public function buildKernedTextUrl( $sKernedTextUrl, $bRetObj = FALSE ) {
		
		$oUrl = new Geko_Uri( $sKernedTextUrl );
		$oUrl
			->setVar( 't', $this->_sText, FALSE )
			->setVar( 'sz', $this->_iFontSize, FALSE )
			->setVar( 'k', $this->_iKerning, FALSE )
			->setVar( 'fgc', Geko_Image_Color::getString( $this->_mForegroundColor ), FALSE )
			->setVar( 'bgc', Geko_Image_Color::getString( $this->_mBackgroundColor ), FALSE )
			->setVar( 'fnt', $this->_sFontFile, FALSE )
			->setVar( 'm', Geko_Image_Margin::getString( $this->_mMargin ), FALSE )
			->setVar( 'w', $this->_iWidth, FALSE )
			->setVar( 'h', $this->_iHeight, FALSE )
			->setVar( 'resmp', $this->_iResampleFactor, FALSE )
			->setVar( 'sh', $this->_bSharpen, FALSE )
			->setVar( 'vo', $this->_fVerticalOffset, FALSE )
		;
		
		return ( $bRetObj ) ? $oUrl : strval( $oUrl );
	}
	
	
}


