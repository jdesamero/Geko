<?php
/*
 * "geko_core/library/Geko/App/Service.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Service extends Geko_Service implements Geko_Router_Adjust
{
	
	protected $_bActionInPath = FALSE;
	
	protected $_aPrefixes = array( 'Gloc_', 'Geko_App_', 'Geko_' );
	
	
	
	//
	public function resolveAction() {
		
		if ( NULL === $this->_sAction ) {

			$sAction = trim( $_REQUEST[ 'section' ] );
			
			if ( !$sAction ) {
				$sAction = parent::resolveAction();
			}
			
			if ( !$sAction ) {
				
				// this is the "<url>/srv/some_class/some_proc" paradigm
				
				if ( $oPath = Geko_App::get( 'router.path' ) ) {
					
					$aLeftovers = $oPath->getLeftovers();
					
					$sAction = trim( $aLeftovers[ 0 ] );
					
					$this->_bActionInPath = TRUE;
				}
				
			}
			
			$this->_sAction = $sAction;
		}
		
		return $this->_sAction;
	}
	
	
	//
	public function adjustRouter( $oRouter ) {
		
		if ( $this->_bActionInPath ) {

			$oPath = $oRouter->getPath();
			$aLeftovers = $oPath->getLeftovers();
			
			// this normalizes the leftovers
			array_shift( $aLeftovers );
			
			$oPath->setLeftovers( $aLeftovers );
			
			$oPath->setSubTarget( $this->resolveActionMethod( $this->getAction() ) );
			
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


