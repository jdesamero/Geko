<?php

// Version 1.04

// includes

require_once( 'shared.inc.php' );

//
Geko_Image_Thumb::setCacheDir( GEKO_IMAGE_THUMB_CACHEDIR );
// Geko_Image_Thumb::setLogging( TRUE );

$oThumb = new Geko_Image_Thumb( $_REQUEST );
$oThumb->output();
// $oThumb->debug();


