<?php

// Version 0.50

// includes

require_once realpath( '../../../../loader.inc.php' );

define( 'GEKO_IMG_DIR', realpath( '../images/' ) );
define( 'GEKO_TILE_WDT', 256 );
define( 'GEKO_TILE_HGT', 256 );
define( 'GEKO_DEFAULT_IMG', GEKO_IMG_DIR . '/gmap_overlay_default.png' );

// ---------------------------------------------------------------------------------------------- //

function geko_show_default_img() {
	
	// show default
	$sOutput = file_get_contents( GEKO_DEFAULT_IMG );
	
	header( 'Content-Type: image/png' );
	header( 'Content-Disposition: inline');
	header( 'Content-Length: ' . strlen( $sOutput ) );
	
	echo $sOutput;
	
}

$aImages = scandir( GEKO_IMG_DIR );

// TO DO: caching mechanism
$sOverlayFile = '';
$iStartX = 0;
$iStartY = 0;

foreach ( $aImages as $sFile ) {
	$aRegs = array();
	if ( preg_match( "/^gmap_overlay_([0-9]+)_([0-9]+)_([0-9]+).png$/", $sFile, $aRegs ) ) {
		// 0: all, 1: z, 2: x, 3: y
		if ( $aRegs[1] == $_REQUEST['z'] ) {
			$aSize = getimagesize( GEKO_IMG_DIR . '/' . $sFile );
			$iXMin = $aRegs[2];
			$iYMin = $aRegs[3];
			$iXMax = $iXMin + ( $aSize[0] / GEKO_TILE_WDT );
			$iYMax = $iYMin + ( $aSize[1] / GEKO_TILE_HGT );
			if (
				( $_REQUEST['x'] >= $iXMin ) && ( $_REQUEST['x'] < $iXMax ) && 
				( $_REQUEST['y'] >= $iYMin ) && ( $_REQUEST['y'] < $iYMax )
			) {
				$sOverlayFile = GEKO_IMG_DIR . '/' . $sFile;
				$iStartX = $iXMin;
				$iStartY = $iYMin;
				break;
			}
		}
	}
}

if ( !$sOverlayFile ) {
	geko_show_default_img();
	die();
}

$_REQUEST['w'] = GEKO_TILE_WDT;
$_REQUEST['h'] = GEKO_TILE_HGT;
$_REQUEST['o'] = 'u';

$_REQUEST['src'] = $sOverlayFile;
$_REQUEST['x'] = $_REQUEST['x'] - $iStartX;
$_REQUEST['y'] = $_REQUEST['y'] - $iStartY;


Geko_Image_Thumb::setCacheDir( realpath( '../../../cache/' ) );
// Geko_Image_Thumb::setLogging( TRUE );

try {

	$oCropped = new Geko_Image_Crop( $_REQUEST );
	$oCropped->output();
	// $oCropped->debug();

} catch ( Exception $e ) {
	
	geko_show_default_img();
	
}

