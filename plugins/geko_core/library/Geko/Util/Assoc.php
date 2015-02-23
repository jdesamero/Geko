<?php

//
class Geko_Util_Assoc
{
	
	protected $_mValue;
	
	//
	public function __construct( $mValue ) {
		
		$this->_mValue = $mValue;
		
	}
	
	//
	public function get( $sKey ) {
		
		$mValue = $this->_mValue;
		
		if ( is_array( $mValue ) ) {
			
			return $mValue[ $sKey ];
			
		} elseif ( $mValue instanceof Geko_Entity ) {
		
			return $mValue->getValue( $sKey );
		}
		
		return NULL;
	}
	
	//
	public function getKeys() {

		$mValue = $this->_mValue;
		
		if ( is_array( $mValue ) ) {
			
			return array_keys( $mValue );;
			
		} elseif ( $mValue instanceof Geko_Entity ) {
			
			return $mValue->getRawEntityKeys();
		}
		
		return array();
	}

}



