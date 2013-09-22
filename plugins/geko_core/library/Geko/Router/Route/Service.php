<?php

//
class Geko_Router_Route_Service extends Geko_Router_Route
{

	//// functionality
	
	//
	public function isMatch() {
		
		$oRouter = $this->_oRouter;
		
		$aPathItems = $aPathLeft = $oRouter->getPathItems();
		
		if ( 'srv' == $aPathItems[ 0 ] ) {
			
			if ( TRUE == $oRouter->getToken( 'AUTH_REQUIRED' ) ) {
				$this->_sBestMatch = $this->getBestMatch( 'Aux_Auth', 'Auth' );
				return TRUE;	// for security			
			}
			
			$sClass = '';
			$sBestMatch = '';
			$aBestMatch = array();
			
			$aLeftovers = array();
			
			foreach ( $aPathItems as $i => $sItem ) {
				
				if ( 0 == $i ) continue;
				
				array_shift( $aPathLeft );
				
				$sItem = str_replace( '-', '_', $sItem );
				$sItem = Geko_Inflector::camelize( $sItem );
				
				if ( $sClass ) $sClass .= '_';
				$sClass .= $sItem;
				
				if ( $sCheck = $this->getBestMatch( $sClass ) ) {
					
					if ( $this->skipClass( $sCheck ) ) break;
					
					$sBestMatch = $sCheck;
					$aBestMatch = $aPathLeft;
				}
				
			}
			
			$this->_sBestMatch = $sBestMatch;
			$this->_aLeftovers = $aBestMatch;
			
			return ( $this->_sBestMatch ) ? TRUE : FALSE ;
		}
		
		return FALSE;
	}
	
	
	//
	public function run() {
		
		$oRouter = $this->_oRouter;
		
		// TO DO: catch and format exceptions to JSON
		
		//// routed layout
		
		if ( $sBestMatch = $this->_sBestMatch ) {
			Geko_Singleton_Abstract::getInstance( $sBestMatch )
				// ->setLeftovers( $this->_aLeftovers )
				->process()
				->output()
			;
		} else {
			throw new Exception( 'A valid service class was not found!' );
		}
		
		$oRouter->setStopRunning( TRUE );
	}
	
	
	
}


