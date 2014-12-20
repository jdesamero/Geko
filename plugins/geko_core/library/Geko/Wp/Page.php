<?php

//
class Geko_Wp_Page extends Geko_Wp_Post
{
	protected $_sEntityIdVarName = 'page_id';
	protected $_sEntitySlugVarName = 'pagename';
	
	
	//
	public function getTitle() {
		
		// if page aliasing is activated, return the apparent page
		$oPageAlias = Geko_Wp_Page_Alias::getInstance();
		if (
			( $oPageAlias->getCalledInit() ) && 
			( is_page() ) && 
			( $this->getId() == $oPageAlias->getApparentPage()->getId() )
		) {
			// can't use getTitle() since it will cause an infinite loop
			return $oPageAlias->getApparentPage()->getEntityPropertyValue( 'title' );
		}
		
		return parent::getTitle();
	}
	
	
	//
	public function getDefaultEntityValue() {
		
		// if page aliasing is activated, return the actual page
		$oPageAlias = Geko_Wp_Page_Alias::getInstance();
		if (
			( $oPageAlias->getCalledInit() ) && 
			( is_page() ) && 
			( $this->getId() == $oPageAlias->getApparentPage()->getId() )
		) {
			return $oPageAlias->getActualPage()->getRawEntity();
		}
		
		return parent::getDefaultEntityValue();
	}
	
	//
	public function getParent() {
		if ( $iPostParent = $this->getPostParent() ) {
			return new $this->_sEntityClass( $iPostParent );
		}
		return NULL;
	}
	
	//
	public function getChildren( $aParams = array() ) {
		
		$aParams = array_merge(
			array( 'post_parent' => $this->getId() ),
			$aParams
		);
		
		return new $this->_sQueryClass( $aParams, FALSE );		
	}
	
	//
	protected function modifyGetAttachmentParams( $aParams ) {
		
		$aParams = parent::modifyGetAttachmentParams( $aParams );
		
		unset( $aParams[ 'p' ] );
		$aParams[ 'page_id' ] = $this->getId();
		
		return $aParams;
	}
	
	//
	public function getEntityFromId( $iEntityId ) {
		return get_page( $iEntityId );
	}
	
	
	//
	public function getPageTemplate() {
		
		if ( $sTemplate = $this->getEntityPropertyValue( '_wp_page_template' ) ) {
			return $sTemplate;
		}
		
		return $this->getMeta( '_wp_page_template' );
	}
	
}


