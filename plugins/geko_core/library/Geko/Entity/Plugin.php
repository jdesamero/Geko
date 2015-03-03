<?php

//
class Geko_Entity_Plugin extends Geko_Singleton_Abstract
{
	
	
	// constructEnd action hook
	public function constructEnd( $oEntity, $oRawEntity, $oQuery, $aData, $aQueryParams, $oPrimaryTable ) {
		
		
		
	}
	
	
	// filter
	public function getEntityPropertyValue( $mValue, $sProperty, $sIndex, $oEntity ) {
		
		return $mValue;
	}
	
	// filter
	public function getRawMeta( $mValue, $sMetaKey, $oRawEntity, $oQuery, $aData, $aQueryParams, $oPrimaryTable, $oEntity ) {
		
		return $mValue;
	}
	
	
}



