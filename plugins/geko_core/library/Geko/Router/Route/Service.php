<?php

//
class Geko_Router_Route_Service extends Geko_Router_Route
{

	protected $_sBestMatch = '';
	
	
	
	//// functionality
	
	//
	public function isMatch() {
		
		$oRouter = $this->_oRouter;
		$oPath = $oRouter->getPath();
		
		$aPathItems = $aPathLeft = $oPath->getPathItems();
		
		if ( 'srv' == $aPathItems[ 0 ] ) {
			
			if ( TRUE == $oRouter->getToken( 'AUTH_REQUIRED' ) ) {
				
				$this->_sBestMatch = $this->getBestMatch( 'Aux_Auth', 'Auth' );
				
				return TRUE;	// for security			
			}
			
			
			$sClass = '';
			$sBestMatch = '';
			$aBestMatch = array();
			
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
			
			
			// do this because of "<url>/srv"
			array_shift( $aBestMatch );
			
			$oPath->setLeftovers( $aBestMatch );
			
			
			//
			if ( $this->_sBestMatch ) {
				
				$oRouter->setCurrentRoute( $this );
				
				return TRUE;
			}
			
			return FALSE;
		}
		
		return FALSE;
	}
	
	//
	public function getTarget() {
		
		return $this->_sBestMatch;
	}
	
	
	
	//
	public function run() {
		
		$oRouter = $this->_oRouter;
		
		try {
		
			//// routed service
			
			if ( $sBestMatch = $this->_sBestMatch ) {
				
				$oService = Geko_Singleton_Abstract::getInstance( $sBestMatch );
				
				$oService->init();
				
				if ( $oService instanceof Geko_Router_Adjust ) {
					
					$oService->adjustRouter( $oRouter );
				}
				
				$oService
					->process()
					->output()
				;
				
			} else {
				
				throw new Exception( 'A valid service class was not found!' );
			}
	
		} catch ( Exception $e ) {
			
			$this->outputException( $e );
		}
		
		$oRouter->setStopRunning( TRUE );
	}
	
	
	//// helpers
	
	// format exception for JSON output
	public function outputException( $e ) {
		
		echo Geko_Json::encode( array(
			'context' => 'exception',
			'error' => TRUE,
			'type' => get_class( $e ),
			'code' => $e->getCode(),
			'message' => $e->getMessage()
		) );
	}
	
	
	
}


