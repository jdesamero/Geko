<?php

//
class Geko_Entity_Record_Plugin extends Geko_Singleton_Abstract
{

	// called right after addPlugin() is invoked on entity
	public function setupDelegate( $oDelegate, $aParams ) {
	
	}
	
	// filter hook
	public function modifyValidateException( $oRecordException, $aErrors, $aValues, $sMode, $oSubject, $oRecord ) {
		
		return $oRecordException;
	}
	
	// action
	public function throwValidate( $aValues, $sMode, $oSubject, $oRecord ) {
	
	}
	
	// action
	public function handleOtherValues( $aValues, $sMode, $oSubject, $oRecord ) {
	
	}
	
	
}


