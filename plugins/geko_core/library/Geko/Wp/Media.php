<?php

//
class Geko_Wp_Media extends Geko_Wp_Post
{
	protected $aAttachmentMeta = NULL;
	
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
		return wp_mime_type_icon( $this->getMimeType() );
	}

	//
	public function getMimeTypeIconImg( $iWidth = 36, $iHeight = 36 ) {
		return '<img src="' . $this->getMimeTypeIconSrc() . '" width="' . $iWidth . '" height="' . $iHeight . '" border="0" />';
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
	
	//
	public function getAttachmentMeta( $sMetaKey = NULL ) {
		
		if ( NULL === $this->aAttachmentMeta ) {
			$this->aAttachmentMeta = wp_get_attachment_metadata( $this->getId() );
		}
		
		if ( NULL === $sMetaKey ) {
			return $this->aAttachmentMeta;
		} else {
			return $this->aAttachmentMeta[ $sMetaKey ];		
		}
	}
	
	// generates class string for use with jQuery metadata plugin
	protected function formatMeta( $aMeta ) {
		$aFmt = array();
		foreach ( $aMeta as $sKey => $mValue ) {
			if ( $mValue ) {
				if ( preg_match( '/[0-9]/', $mValue ) ) {
					$aFmt[] = $sKey . ': ' . $mValue;
				} else {
					$aFmt[] = $sKey . ": '" . $mValue . "'";			
				}
			}
		}
		return '{' . implode( ', ', $aFmt ) . '}';
	}

	
	
	// allows for quick auto-resizing of the image
	public function getTheImageUrl( $aParams ) {
		
		if (
			( $sImage = $this->getUrl() ) && 
			( $this->isImage() )
		) {
			// remove the http:// portion of the image path
			$sSrcDir = Geko_PhpQuery_FormTransform_Plugin_File::getDefaultFileDocRoot();
			$sImage = str_replace( Geko_Wp::getUrl(), '', $sImage );
			$sImage = $sSrcDir . '/' . trim( $sImage, "/" );
			
			$aParams[ 'src' ] = $sImage;
		}
		
		return Geko_Wp::getThumbUrl( $aParams );
	}
	
}


