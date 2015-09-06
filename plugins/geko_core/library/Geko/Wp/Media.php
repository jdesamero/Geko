<?php

//
class Geko_Wp_Media extends Geko_Wp_Post
{
	
	protected $_aAttachmentMeta = NULL;
	
	
	
	//
	public function __construct( $mEntity = NULL, $oQuery = NULL, $aData = array(), $aQueryParams = NULL ) {
		
		if ( is_array( $mEntity ) ) {
			
			// format entity from ACF Pro
			if ( $aSizes = $mEntity[ 'sizes' ] ) {
				
				$aFmt = array(
					
					'file_id' => $mEntity[ 'id' ],
					'file_title' => $mEntity[ 'title' ],
					'file_name' => $mEntity[ 'name' ],
					'file_description' => $mEntity[ 'description' ],
					'file_desc_excerpt' => $mEntity[ 'caption' ],
					'file_created' => $mEntity[ 'date' ],
					'file_modified' => $mEntity[ 'modified' ],
					'file_mime_type' => $mEntity[ 'mime_type' ],
					'file_alt_text' => $mEntity[ 'alt' ],
					
					'file_url' => $mEntity[ 'url' ],
					'file_icon' => $mEntity[ 'icon' ]					
					
				);
				
				// not quite the same results as wp_get_attachment_metadata(), but close enough
				$this->_aAttachmentMeta = array(
					'width' => $mEntity[ 'width' ],
					'height' => $mEntity[ 'height' ],
					'file' => $mEntity[ 'filename' ],
					'sizes' => array(
						'thumbnail' => array(
							'file' => $mEntity[ 'sizes' ][ 'thumbnail' ],
							'width' => $mEntity[ 'sizes' ][ 'thumbnail-width' ],
							'height' => $mEntity[ 'sizes' ][ 'thumbnail-height' ]
						),
						'medium' => array(
							'file' => $mEntity[ 'sizes' ][ 'medium' ],
							'width' => $mEntity[ 'sizes' ][ 'medium-width' ],
							'height' => $mEntity[ 'sizes' ][ 'medium-height' ]
						)
					)
				);
				
				// re-format
				$mEntity = $aFmt;
			}
			
		}
		
		parent::__construct( $mEntity, $oQuery, $aData, $aQueryParams );
		
	}

	
	//
	public function init() {
		
		$mRet = parent::init();
		
		$this
			->setEntityMapping( 'id', 'file_id' )
			->setEntityMapping( 'title', 'file_title' )
			->setEntityMapping( 'slug', 'file_name' )
			->setEntityMapping( 'content', 'file_description' )
			->setEntityMapping( 'excerpt', 'file_desc_excerpt' )
			->setEntityMapping( 'date_created', 'file_created' )
			->setEntityMapping( 'date_modified', 'file_modified' )
			->setEntityMapping( 'date_created_gmt', 'file_created_gmt' )
			->setEntityMapping( 'date_modified_gmt', 'file_modified_gmt' )
			->setEntityMapping( 'parent_entity_id', 'ID' )
		;
		
		return $mRet;
	}
	
	//
	public function getPermalink() {
		
		if ( !$sFileUrl = $this->getEntityPropertyValue( 'file_url' ) ) {
			$sFileUrl = wp_get_attachment_url( $this->getId() );
		}
		
		if (
			( 'on' == $_SERVER[ 'HTTPS' ] ) && 
			( 0 === strpos( $sFileUrl, 'http://' ) )
		) {
			$sFileUrl = str_replace( 'http://', 'https://', $sFileUrl );
		}
		
		return $sFileUrl;
	}
	
	//
	public function getParentPermalink() {
		return get_permalink( $this->getParentEntityId() );
	}
	
	//
	public function getMimeTypeIconSrc() {

		if ( !$sIconUrl = $this->getEntityPropertyValue( 'file_icon' ) ) {
			$sIconUrl = wp_mime_type_icon( $this->getMimeType() );
		}
		
		return $sIconUrl;
	}

	//
	public function getMimeTypeIconImg( $iWidth = 36, $iHeight = 36 ) {
		return sprintf( '<img src="%s" width="%d" height="%d" border="0" />', $this->getMimeTypeIconSrc(), $iWidth, $iHeight );
	}
	
	//
	public function getParentUrl() {
		return $this->getParentPermalink();
	}	
	
	//
	public function getParentTitle() {
		return $this->getEntityPropertyValue( 'post_title' );
	}
	
	//
	public function getMimeType() {
		return $this->getEntityPropertyValue( 'file_mime_type' );
	}
	
	// create <a> tag using getUrl() and getTitle()
	public function getParentLink() {
		return sprintf(
			'<a href="%s">%s</a>',
			$this->getParentUrl(),
			$this->getParentTitle()			
		);
	}
	
	//
	public function isImage() {
		return ( 0 === strpos( $this->getMimeType(), 'image' ) );
	}
	
	
	
	//// attachment meta
	
	//
	public function getAttachmentMeta( $sMetaKey = NULL ) {
		
		if ( NULL === $this->_aAttachmentMeta ) {
			$this->_aAttachmentMeta = wp_get_attachment_metadata( $this->getId() );
		}
		
		if ( NULL === $sMetaKey ) {
			return $this->_aAttachmentMeta;
		} else {
			return $this->_aAttachmentMeta[ $sMetaKey ];		
		}
	}
	
	//
	public function getWidth() {
		$aMeta = $this->getAttachmentMeta();
		return $aMeta[ 'width' ];
	}
	
	//
	public function getHeight() {
		$aMeta = $this->getAttachmentMeta();
		return $aMeta[ 'height' ];
	}
	
	
	
	
	
	// generates class string for use with jQuery metadata plugin
	protected function formatMeta( $aMeta ) {
		
		$aFmt = array();
		
		foreach ( $aMeta as $sKey => $mValue ) {
			
			if ( $mValue ) {
				if ( preg_match( '/[0-9]/', $mValue ) ) {
					$aFmt[] = sprintf( '%s: %d', $sKey, $mValue );
				} else {
					$aFmt[] = sprintf( "%s: '%s'", $sKey, addslashes( $mValue ) );			
				}
			}
		}
		
		return sprintf( '{%s}', implode( ', ', $aFmt ) );
	}

	
	
	// allows for quick auto-resizing of the image
	public function getTheImageUrl( $aParams ) {
		
		if (
			( $sImage = $this->getUrl() ) && 
			( $this->isImage() )
		) {
			// resolve image url to absolute file system path
			$aParams[ 'src' ] = Geko_String_Path::getUrlToFile( $sImage );
		}
		
		return Geko_Wp::getThumbUrl( $aParams );
	}
	
}


