<?php
/*
 * "geko_core/library/Geko/App/Service/Uploadifive.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Service_Uploadifive extends Geko_App_Service
{
	
	const STAT_SUCCESS = 1;
	
	const STAT_SUCCESS_UPLOAD = 101;
	
	const STAT_ERROR_UPLOAD_MOVE_FILE_FAILED = 201;
	const STAT_ERROR_UPLOAD_ERROR = 202;
	const STAT_ERROR_UPLOAD_NO_FILE_DATA = 203;
	
	const STAT_ERROR = 999;
	
	
	
	//
	protected $_aImgMimeTypes = array(
		'image/gif',
		'image/jpeg',
		'image/png',
		'image/x-xbitmap',
		'image/vnd.wap.wbmp'
	);
	
	
	
	//
	public function processUpload() {		
		
		//
		if ( is_array( $aFileData = $_FILES[ 'Filedata' ] ) ) {
			
			$iUploadError = $aFileData[ 'error' ];
			
			if ( UPLOAD_ERR_OK === $iUploadError ) {
				
				$sTempFileName = $aFileData[ 'tmp_name' ];
				$sOrigFileName = $sFileName = $aFileData[ 'name' ];
				$sType = $aFileData[ 'type' ];
				
				
				
				//// Handle uploads to sanctioned directories
				
				if (
					( $iPathId = intval( $_REQUEST[ 'path_id' ] ) ) && 
					( !$sTargetPath = Geko_App_File_Path::_getPath( $iPathId ) )
				) {
					// default
					$sTargetPath = GEKO_UPLOAD_PATH;
					$iPathId = Geko_App_File_Path::_getId( $sTargetPath );
				}
				
				$sTargetFile = sprintf( '%s/%s', $sTargetPath, $sFileName );
				
				
				
				//// determine the next available file name (unique)
				
				if ( $sAvailableFileName = Geko_File::getNextAvailableFullFilePath( $sTargetFile ) ) {
					
					$sTargetFile = $sAvailableFileName;
				}
				
				//
				if ( move_uploaded_file( $sTempFileName, $sTargetFile ) ) {
					
					
					//// upload was successful
					
					// see if we can get a better mime type
					// "application/octet-stream" is pretty generic
					
					if ( !$sType || ( 'application/octet-stream' == $sType ) ) {
						
						$sMaybeBetterType = Geko_File_MimeType::get( $sTargetFile );
						
						if (
							( $sMaybeBetterType ) && 
							( 'application/octet-stream' != $sMaybeBetterType )
						) {
							// assign better type
							$aFileData[ 'type' ] = $sType = $sMaybeBetterType;
						}
						
					}
					
					
					
					//// attempt to provide a valid file extension if missing
					
					$sExt = pathinfo( $sTargetFile, PATHINFO_EXTENSION );
					
					if (
						( $sType && ( 'application/octet-stream' != $sType ) ) &&
						( !Geko_File_MimeType::isValidExt( $sExt ) ) && 
						( $sNewExt = Geko_File_MimeType::getExtFromType( $sType ) )
					) {
						
						$sTargetFileWithNewExt = sprintf( '%s.%s', $sTargetFile, $sNewExt );
						$sExt = $sNewExt;
						
						//// determine the next available file name (unique)
						
						if ( $sAvailableFileNameWithNewExt = Geko_File::getNextAvailableFullFilePath( $sTargetFileWithNewExt ) ) {
							
							$sTargetFileWithNewExt = $sAvailableFileNameWithNewExt;
						}
						
						
						if ( rename( $sTargetFile, $sTargetFileWithNewExt ) ) {
							
							$sTargetFile = $sTargetFileWithNewExt;
						}
						
						// else: file renaming failed, do nothing for now
					}
					
					
					//// return json data
					
					$sExt = strtolower( $sExt );		// force normalization
					
					$sTargetFileName = pathinfo( $sTargetFile, PATHINFO_BASENAME );
					
					$aFileData[ 'target_file' ] = $sTargetFile;
					$aFileData[ 'target_file_name' ] = $sTargetFileName;
					$aFileData[ 'extension' ] = $sExt;
					
					
					//// create entity
					
					$oFile = $this->newFile();
					$oFile
						->setPathId( $iPathId )
						->setName( $sTargetFileName )
						->setOrigName( $sOrigFileName )
						->setExtension( $sExt )
						->setMimeType( $sType )
						->setSize( filesize( $sTargetFile ) )
					;


					//// get image info, if available
					
					if ( in_array( $sType, $this->_aImgMimeTypes ) ) {
						
						$aImgInfo = getimagesize( $sTargetFile );
						
						list( $iWidth, $iHeight ) = $aImgInfo;
						
						$this->setResponseValue( 'imgdata', array(
							'width' => $iWidth,
							'height' => $iHeight
						) );
						
						$oFile
							->setHasDimensions( 1 )
							->setWidth( $iWidth )
							->setHeight( $iHeight )
						;
						
					}
					
					
					// commit
					$oFile->save();
					
					
					$this
						->setResponseValue( 'file_id', intval( $oFile->getId() ) )
						->setStatus( self::STAT_SUCCESS_UPLOAD )
					;
					
				} else {
					
					// include for possible error handling
					
					$aFileData[ 'target_path' ] = $sTargetPath;
					
					$this->setStatus( self::STAT_ERROR_UPLOAD_MOVE_FILE_FAILED );
				}
				
				$this->setResponseValue( 'filedata', $aFileData );
				
			} else {
				
				$this
					->setResponseValue( 'upload_error', $iUploadError )
					->setStatus( self::STAT_ERROR_UPLOAD_ERROR )
				;
				
			}
			
		} else {
			
			$this->setStatus( self::STAT_ERROR_UPLOAD_NO_FILE_DATA );
		}
		
	}
	
	
	//
	public function processDelete() {
		
		// TO DO: add security measures
		
		if ( $iFileId = intval( $_REQUEST[ 'file_id' ] ) ) {
			
			$this->setResponseValue( 'file_id', $iFileId );
			
			$oFile = $this->newFile( $iFileId );
			$oFile->destroy();
			
		}
		
	}
	
	
}


