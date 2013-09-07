<?php

require_once( 'shared.inc.php' );

// ---------------------------------------------------------------------------------------------- //

// do checks
if ( !is_user_logged_in() || !current_user_can( 'administrator' ) ) {
	die();
}

// ---------------------------------------------------------------------------------------------- //

// Set-up
$aParams = $_GET;

if ( $sEntity = $aParams[ 'entity' ] ) {
	unset( $aParams[ 'entity' ] );
}

$sExportMode = $aParams[ 'export_mode' ];
unset( $aParams[ 'export_mode' ] );

if ( $sQueryClass = $aParams[ 'entity_query' ] ) {
	unset( $aParams[ 'entity_query' ] );
}

if ( !$sQueryClass && $sEntity ) $sQueryClass = $sEntity . '_Query';

if ( $sHelperClass = $aParams[ 'entity_export_excel_helper' ] ) {
	unset( $aParams[ 'entity_export_excel_helper' ] );
}

if ( !$sHelperClass && $sEntity ) $sHelperClass = $sEntity . '_ExportExcelHelper';

if ( !@class_exists( $sQueryClass ) ) die();						// must be valid query class
if ( !@class_exists( $sHelperClass ) ) die();						// must be valid helper class

$aParams[ 'showposts' ] = -1;
unset( $aParams[ 'posts_per_page' ] );
unset( $aParams[ 'paged' ] );

// $aParams[ '__profile_query' ] = TRUE;
$aRes = new $sQueryClass( $aParams );
$oHelper = new $sHelperClass( $aParams );

/* /
print_r( $aRes );
print_r( $oHelper );

die();
/* */

// ---------------------------------------------------------------------------------------------- //

if ( 'csv' == $sExportMode ) {
	
	//// export CSV file

	$sFileName = str_replace( '.xls', '.csv', $oHelper->getExportedFileName() );
	
	function GekoCsvEscape( $sValue ) {
		return str_replace( '"', '""', stripslashes( $sValue ) );
	}
	
	$aColumns = $oHelper->getTitles();
	
	$sOutput = '"' . implode( '","', array_map( 'GekoCsvEscape', $aColumns ) ) . '"' . "\n";
	
	foreach ( $aRes as $oItem ) {
		$aOut = $oHelper->getValues( $oItem );
		$sOutput .= '"' . implode( '","', array_map( 'GekoCsvEscape', $aOut ) ) . '"' . "\n";
	}
	
	header( 'Content-Type: text/x-csv' );
	header( 'Content-Disposition: attachment; filename="' . $sFileName . '"' );
	header( 'Content-Length: ' . strlen( $sOutput ) );
	
	echo $sOutput;
	
} else {
	
	$oHelper->exportToExcel( $aRes );
	
}

