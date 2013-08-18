<?php

class Geko_Wp_Form_MetaData extends Geko_Wp_Entity
{
	
	//// static functions
	
	//
	public static function _getContextId( $sContext ) {
		$aContext = Geko_Wp_Enumeration_Query::getSet( 'geko-form-context' );
		return $aContext->getValueFromSlug( 'geko-form-context-' . $sContext );
	}
	
	//
	public static function _getContextIds( $sContext ) {
		$aIds = array();
		$aContext = Geko_Wp_Enumeration_Query::getSet( 'geko-form-context' );
		foreach ( $aContext as $oContext ) {
			$aIds[] = $oContext->getValue();
		}
		return $aIds;
	}
	
	//// object oriented functions
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'fmmd_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'item_type_id', 'fmitmtyp_id' )
		;
		
		return $this;
	}
	
	
	
}


