<?php

class Geko_Wp_Form_ItemMetaValue extends Geko_Wp_Entity
{
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			// ->setEntityMapping( 'id', 'fmmd_id' )
			// ->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'meta_data_id', 'fmmd_id' )
		;
		
		return $this;
	}
	
	
	//
	public function getContextEntityId() {
		
		$iContextId = $this->getEntityPropertyValue( 'context_id' );
		
		if ( Geko_Wp_Form_MetaData::_getContextId( 'section' ) == $iContextId ) {
			return $this->getEntityPropertyValue( 'fmsec_id' );
		} elseif ( Geko_Wp_Form_MetaData::_getContextId( 'question' ) == $iContextId ) {
			return $this->getEntityPropertyValue( 'fmitm_id' );		
		} elseif ( Geko_Wp_Form_MetaData::_getContextId( 'choice' ) == $iContextId ) {
			return $this->getEntityPropertyValue( 'fmitm_id' ) . ':' . $this->getEntityPropertyValue( 'fmitmval_idx' );		
		}
		
		return NULL;
	}
	
	
}


