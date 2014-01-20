<?php

// decorator for ReflectionClass
class Geko_Reflection
{
	
	protected $_oSubject = NULL;
	protected $_oReflect = NULL;
	
	
	//
	public function __construct( $oSubject ) {
		
		$this->_oReflect = new ReflectionClass( $oSubject );
		$this->_oSubject = $oSubject;
		
	}
	
	//
	public function getPropertyValue( $sKey ) {
		
		$aProps = $this->_oReflect->getProperties();
		
		foreach ( $aProps as $oProp ) {
			if ( $oProp->getName() == $sKey ) {
				$oProp->setAccessible( TRUE );
				return $oProp->getValue( $this->_oSubject );
			}
		}
		
		return NULL;
	}
	

}
