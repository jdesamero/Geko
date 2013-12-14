<?php 

// For use with Uploadify jQuery plugin for handling uploads
// (progress bar and multiple file uploads)
// http://www.uploadify.com/

require_once( 'shared.inc.php' );

// ---------------------------------------------------------------------------------------------- //

if ( !empty( $_FILES ) ) {
	
	$sTempFile = $_FILES[ 'Filedata' ][ 'tmp_name' ];
	
	// $sTargetPath = $_SERVER[ 'DOCUMENT_ROOT' ] . $_REQUEST[ 'folder' ] . '/';
	// $sTargetFile =  str_replace( '//', '/', $sTargetPath ) . $_FILES[ 'Filedata' ][ 'name' ];
	
	$sTargetPath = ( defined( 'GEKO_UPLOADIFY_TARGET_PATH' ) ) ? GEKO_UPLOADIFY_TARGET_PATH : '/tmp/';
	$sTargetFile = $sTargetPath . 'uploadify-' . basename( $sTempFile );
	
	$bRes = move_uploaded_file( $sTempFile, $sTargetFile );
	
	$aRet = $_FILES[ 'Filedata' ];
	$aRet[ 'tmp_name' ] = $sTargetFile;
	
	if ( !defined( 'GEKO_UPLOADIFY_SKIP_GEKO_MIME_CHECK' ) ) {
		$aRet[ 'type' ] = Geko_File_MimeType::get( $sTargetFile );
	}
	
	if (
		( 'application/octet-stream' == $aRet[ 'type' ] ) || 
		( !$aRet[ 'type' ] )
	) {
		$aCheck = wp_check_filetype_and_ext( $sTargetFile, $_FILES[ 'Filedata' ][ 'name' ] );
		$aRet[ 'type' ] = $aCheck[ 'type' ];
	}
	
	// echo str_replace( $_SERVER[ 'DOCUMENT_ROOT' ], '', $sTargetFile );
	echo Zend_Json::encode( $aRet );
	
}


