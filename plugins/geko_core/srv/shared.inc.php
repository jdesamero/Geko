<?php

if ( is_file( '../../../../wp-load.php' ) ) {

	ini_set( 'display_errors', 1 );
	// ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
	error_reporting( E_ALL ^ E_NOTICE ^ E_WARNING );
	// error_reporting( E_ALL ^ E_NOTICE );
	// error_reporting( E_ALL );
	
	// Wordpress deployment

	// hack to fix NextGEN Gallery 2.0.17
	$_GET[ 'display_gallery_iframe' ] = TRUE;
	
	require_once( realpath( '../../../../wp-load.php' ) );
	require_once( realpath( '../../../../wp-admin/includes/admin.php' ) );
	
	
} else {

	// Standalone deployment
	
	// TO DO: !!!
	// set this GEKO_IMAGE_THUMB_CACHEDIR
	
}

