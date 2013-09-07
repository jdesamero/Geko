<?php

ob_start();

require_once( 'shared.inc.php' );

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

