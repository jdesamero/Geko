<?php

//
class Geko_PhpQuery_FormTransform_Plugin_File extends Geko_PhpQuery_FormTransform_Plugin_Abstract
{
	protected static $sDefaultFileDocRoot = '';
	protected static $sDefaultFileUrlRoot = '';
	
	
	//
	public static function setDefaultFileDocRoot( $sDefaultFileDocRoot ) {
		self::$sDefaultFileDocRoot = $sDefaultFileDocRoot;
	}

	//
	public static function getDefaultFileDocRoot() {
		return self::$sDefaultFileDocRoot;
	}	
	
	//
	public static function setDefaultFileUrlRoot( $sDefaultFileUrlRoot ) {
		self::$sDefaultFileUrlRoot = $sDefaultFileUrlRoot;
	}
	
	//
	public static function getDefaultFileUrlRoot() {
		return self::$sDefaultFileUrlRoot;
	}	
	
	
	
	//
	public static function modifyGroupedFormElem( $aElem, $sPrefixedGroupName ) {
		
		$sElemType = $aElem[ 'type' ];
		$sNodeName = $aElem[ 'nodename' ];
		$sSubType = $aElem[ 'subtype' ];
		$oPq = $aElem[ 'elem' ];
		
		if ( 'input:file' == $sElemType ) {
			
			$aElem[ 'doc_root' ] = Geko_String::coalesce( $oPq->attr( '_file_doc_root' ), self::$sDefaultFileDocRoot );
			$aElem[ 'url_root' ] = Geko_String::coalesce( $oPq->attr( '_file_url_root' ), self::$sDefaultFileUrlRoot );
			$aElem[ 'upload_dir' ] = $oPq->attr( '_file_upload_dir' );
			
			$aElem[ 'full_doc_root' ] = $aElem[ 'doc_root' ] . $aElem[ 'upload_dir' ];
			$aElem[ 'full_url_root' ] = $aElem[ 'url_root' ] . $aElem[ 'upload_dir' ];
			
		}
		
		return $aElem;
	}
	
	//
	public static function setElemDefaultValue( $aElem ) {
		
		$sElemType = $aElem[ 'type' ];
		$sNodeName = $aElem[ 'nodename' ];
		$sSubType = $aElem[ 'subtype' ];
		$oPq = $aElem[ 'elem' ];
		
		if ( 'input:file' == $sElemType ) {
		
			$sName = $oPq->attr( 'name' );
			$oDoc = $oPq->toRoot();
			
			// clean up
			$oDoc->find( '*[_bind_to=' . $sName . ']' )->remove();			
			
		}
	}
	
	//
	public static function setElemValue( $aElem, $mOptionVal ) {
		
		$sElemType = $aElem[ 'type' ];
		$sNodeName = $aElem[ 'nodename' ];
		$sSubType = $aElem[ 'subtype' ];
		$oPq = $aElem[ 'elem' ];
		
		if ( 'input:file' == $sElemType ) {
			
			$sFullDocRoot = $aElem[ 'full_doc_root' ];
			$sFullUrlRoot = $aElem[ 'full_url_root' ];
			
			$bIsImage = FALSE;
			$sMimeType;
			$iImgWidth = NULL;
			$iImgHeight = NULL;

			$sName = $oPq->attr( 'name' );
			$oDoc = $oPq->toRoot();
			
			if ( is_file( $sFullDocFile = $sFullDocRoot . '/' . $mOptionVal ) ) {
				
				$sFullUrlFile = $sFullUrlRoot . '/' . $mOptionVal;
				
				if ( $aImgInfo = getimagesize( $sFullDocFile ) ) {
					
					$bIsImage = TRUE;
					$iImgWidth = $aImgInfo[ 0 ];
					$iImgHeight = $aImgInfo[ 1 ];
					$sMimeType = $aImgInfo[ 'mime' ];
				} else {
					$sMimeType = Geko_File_MimeType::get( $sFullDocFile );
				}
				
				$aBindElems = $oDoc->find( '*[_bind_to=' . $sName . ']' );
				
				foreach ( $aBindElems as $oElem ) {
					
					$oPpq = pq( $oElem );
					
					if ( !$oPpq->html() ) {
						$sDisplay = ( $bIsImage ) ? '<img class="file_image" />' : '<span class="file_path"></span>';
						$oPpq->html('
							' . $sDisplay . '<br />
							<input type="checkbox" class="file_delete" /> <span class="delete">Delete</span>
						');
					}
					
					$oPpq->find( '.file_path' )->html( $sFullDocFile );
					$oPpq->find( '.file_url' )->html( $sFullUrlFile );
					$oPpq->find( 'a.file_download' )->attr( 'href', $sFullUrlFile );
					
					$oPpq
						->find( 'input.file_delete' )
						->attr( 'id', 'del-' . $sName )
						->attr( 'name', 'del-' . $sName )
					;
					
					if ( $bIsImage ) {
						
						// if "_thumb_width" or "_thumb_height" is specified, then apply thumbnailer
						$iThumbWidth = $oPpq->attr( '_thumb_width' );
						$iThumbHeight = $oPpq->attr( '_thumb_height' );
						
						if ( $iThumbWidth || $iThumbHeight ) {
							
							// create clickable thumbnail
							$aThumbParams = array( 'src' => $sFullDocFile, 'zc' => 1 );
							if ( $iThumbWidth ) $aThumbParams[ 'w' ] = $iThumbWidth;
							if ( $iThumbHeight ) $aThumbParams[ 'h' ] = $iThumbHeight;
							
							$sThumbFile = Geko_Uri::getUrl( 'geko_thumb' ) . '?' . http_build_query( $aThumbParams );
							
							$oPpq
								->find( 'img.file_image' )
								->after( sprintf( '<br /><a href="%s" target="_blank">View Full Image (%d x %d)</a>', $sFullUrlFile, $iImgWidth, $iImgHeight ) )
							;
							
							$oPpq
								->find( 'img.file_image' )
								->attr( 'src', $sThumbFile )
								->attr( 'width', $iThumbWidth )
								->attr( 'height', $iThumbHeight )
								->wrap( sprintf( '<a href="%s" target="_blank"></a>', $sFullUrlFile ) )
							;
							
						} else {
							
							// show unresized image
							$oPpq
								->find( 'img.file_image' )
								->attr( 'src', $sFullUrlFile )
								->attr( 'width', $iImgWidth )
								->attr( 'height', $iImgHeight )
							;
							
						}
						
					} else {
						$oPpq->find( 'img.file_image' )->remove();
					}
					
					// clean up known magical attributes
					$oPpq
						->removeAttr( '_bind_to' )
						->removeAttr( '_thumb_width' )
						->removeAttr( '_thumb_height' )
					;
					
				}
				
			} else {
				
				// clean up
				$oDoc->find( '*[_bind_to=' . $sName . ']' )->remove();
				
			}
			
		}
		
	}
	
	// clean-up non-html tags
	public static function cleanUpNonHtml( $oDoc ) {
		$oDoc->find( 'input[_file_doc_root]' )->removeAttr( '_file_doc_root' );
		$oDoc->find( 'input[_file_url_root]' )->removeAttr( '_file_url_root' );
		$oDoc->find( 'input[_file_upload_dir]' )->removeAttr( '_file_upload_dir' );
	}
	
}


