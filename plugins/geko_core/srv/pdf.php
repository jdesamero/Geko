<?php 

require_once realpath( '../../../../wp-load.php' );
require_once realpath( '../../../../wp-admin/includes/admin.php' );
require_once realpath( 'external/libs/mpdf/mpdf.php' );

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
ob_start();

if ( $oService ):
	$oService->output();
else:
	?>
	<h1>Invalid action was specified: <?php echo $sServiceClass; ?></h1>
	<?php
endif;

$sHtml = ob_get_contents();
ob_end_clean();

// generate pdf

$oPdf = new mPDF(); 

if ( $oService ) {
	$aParams = array( 'mpdf' => $oPdf );
	$aParams = $oService->modifyParams( $aParams );
	$oPdf = $aParams[ 'mpdf' ];
}

$oPdf->WriteHTML( $sHtml );
$oPdf->Output();

die();

