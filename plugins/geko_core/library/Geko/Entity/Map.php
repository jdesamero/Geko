<?php

// field mapping class
class Geko_Entity_Map extends Geko_Singleton_Abstract
{
	
	protected $_bAutoInit = TRUE;
	
	// values in $this->_aFieldMappings can be in some notation that will be evaluated
	// to $this->_aFinalMappings
	
	protected $_aFieldMappings = array();
	protected $_aFinalMappings = array();
	
	protected $_sMetaPrefix = '';
	protected $_sDbFieldExpr = '';
	
	protected $_aDbFieldMappings = array();
	
	
	
	//
	public function start() {
		
		parent::start();
		
		foreach ( $this->_aFieldMappings as $sKey => $mValue ) {

			if ( is_string( $mValue ) ) {
				
				if ( 0 === strpos( $mValue, 'meta:' ) ) {
					
					$sDbFldIdx = substr( $mValue, 5 );
					
					$mValue = sprintf( '%s%s', $this->_sMetaPrefix, $sKey );
					
					$sDbFldExpr = sprintf( $this->_sDbFieldExpr, $sDbFldIdx );
					
					$this->_aDbFieldMappings[ $mValue ] = $sDbFldExpr;
					
				}
				
			}
			
			$this->_aFinalMappings[ $sKey ] = $mValue;
			
		}
		
	}
	
	
	//
	public function getFinalMappings() {
		return $this->_aFinalMappings;
	}
	
	//
	public function getDbFieldMappings() {
		return $this->_aDbFieldMappings;
	}
	
	//
	public function getKeyMapping( $sKey ) {
		return $this->_aFinalMappings[ $sKey ];
	}
	
	
	
}



