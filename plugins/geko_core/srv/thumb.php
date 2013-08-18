<?php

// Version 1.04

// includes

require_once realpath( '../../../../wp-load.php' );
require_once realpath( '../../../../wp-admin/includes/admin.php' );

//
Geko_Image_Thumb::setCacheDir( realpath( ABSPATH . '/wp-content/cache/' ) );
// Geko_Image_Thumb::setLogging( TRUE );

$oThumb = new Geko_Image_Thumb( $_REQUEST );
$oThumb->output();
// $oThumb->debug();


