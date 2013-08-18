<?php

ob_start();

require_once realpath( '../../../../wp-load.php' );
require_once realpath( '../../../../wp-admin/includes/admin.php' );

/* /
ini_set( 'display_errors', 1 );
ini_set( 'scream.enabled', 1 );		// >= v.5.2.0
error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL );
/* */

// ---------------------------------------------------------------------------------------------- //

// resolve service/action

if (
	isset( $_REQUEST[ '_service' ] ) && 
	isset( $_REQUEST[ '_action' ] )
) {
	// new way of doing things
	$sServiceClass = $_REQUEST[ '_service' ];
} else {
	// old way
	$sServiceClass = $_REQUEST[ 'action' ];
}

if ( @class_exists( $sServiceClass ) ) {	
	$oService = Geko_Singleton_Abstract::getInstance( $sServiceClass )->process();
}

ob_end_clean();

if ( $oService ) {
	$oService->output();
} else {
	echo Zend_Json::encode( 'Invalid action was specified: ' . $sServiceClass );
}

