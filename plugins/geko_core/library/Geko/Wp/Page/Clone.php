<?php

//
class Geko_Wp_Page_Clone extends Geko_Wp_Post_Clone
{

	protected $_sButtonTitle = 'Clone Page';
	protected $_sLinkTitle = 'Clone this page';
	

	//
	public function adminHooks() {
		add_filter( 'page_row_actions', array( $this, 'doCloneLink' ), 10, 2 );	
	}
	
	//
	public function isCorrectType( $iPageId ) {
		return ( 'page' == $this->getType( $iPageId ) ) ? TRUE : FALSE ;
	}
	
	//
	public function getMetaInstance( $iPageId ) {
		
		if ( class_exists( 'Gloc_Page_Meta' ) ) {
			
			$oMeta = Gloc_Page_Meta::getInstance();
			$oMeta->setPageId( $iPageId )->getCurPage();
			
			return $oMeta;
		}
		
		return NULL;
	}
	
}

