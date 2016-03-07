<?php

//
class Geko_App_Layout extends Geko_Layout implements Geko_Router_Adjust
{
	
	protected $_bAjaxSectionInPath = FALSE;
	
	protected $_sRenderer = 'Geko_App_Layout_Renderer';
	protected $_aPrefixes = array( 'Gloc_', 'Geko_App_', 'Geko_' );
	
	
	
	//// layout parts
	
	//
	public function echoHead() {
		
		Geko_Once::run( __METHOD__, function() {
			
			$this
				->renderStyleTags()
				->renderScriptTags()
			;
			
		} );
		
	}
	
	
	//
	public function getBodyClassCb() {
		
		$aBodyClass = array();
		
		if ( $oRouter = Geko_App::get( 'router' ) ) {
			
			$oPath = $oRouter->getPath();
			$aPathItems = $oPath->getPathItems();
			
			foreach ( $aPathItems as $i => $sItem ) {
				$aBodyClass[] = sprintf( 'path_lv%d_%s', $i + 1, $sItem );
			}
			
			$aBodyClass[] = sprintf( 'path_full_%s', implode( '_', $aPathItems ) );
		}
		
		
		return implode( ' ', $aBodyClass );
	}
	
	
	//
	public function getScriptUrls( $aOther = NULL ) {
		
		$aOther = parent::getScriptUrls( $aOther );
		
		return Geko_App::getScriptUrls( $aOther );
	}
	
	
	
	
	//
	public function resolveAjaxSection() {
		
		if ( NULL === $this->_sAjaxSection ) {
			
			$sAjaxSection = parent::resolveAjaxSection();
			
			if ( !$sAjaxSection ) {
				
				// this is the "<url>/some_class/some_section" paradigm
				
				if ( $oPath = Geko_App::get( 'router.path' ) ) {
					
					$aLeftovers = $oPath->getLeftovers();
					
					$sAjaxSection = trim( $aLeftovers[ 0 ] );
					
					$this->_bAjaxSectionInPath = TRUE;
				}
				
			}
			
			$this->_sAjaxSection = $sAjaxSection;
		}
		
		return $this->_sAjaxSection;
	}
	
	//
	public function adjustRouter( $oRouter ) {
		
		if ( $this->_bAjaxSectionInPath ) {

			$oPath = $oRouter->getPath();
			$aLeftovers = $oPath->getLeftovers();
			
			// this normalizes the leftovers
			array_shift( $aLeftovers );
			
			$oPath->setLeftovers( $aLeftovers );
			
			$oPath->setSubTarget( $this->resolveAjaxSectionMethod( $this->getAjaxSection() ) );
			
		}
		
		return $oRouter;
	}
	
	
	
	// convenience method
	public function getLeftovers() {
		
		if ( $oPath = Geko_App::get( 'router.path' ) ) {
			
			return $oPath->getLeftovers();
		}
		
		return array();
	}
	
	
}



