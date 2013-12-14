<?php

class Geko_Wp_Form_ItemValue extends Geko_Wp_Entity
{	
	
	protected $_sEntityIdVarName = 'fmitm_id:fmitmval_idx';
	// protected $_sEntitySlugVarName = '';
	
	protected $_sEditEntityIdVarName = 'fmitm_id:fmitmval_idx';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'title', 'label' )
			->setEntityMapping( 'content', 'help' )
			->setEntityMapping( 'section_id', 'fmsec_id' )
			->setEntityMapping( 'item_id', 'fmitm_id' )
			->setEntityMapping( 'item_value_index', 'fmitmval_idx' )
			->setData( 'lang_meta_fields', array( 'label', 'help' ) )
		;
		
		return $this;
	}
	
	
	
	//// for use in rendering
	
	//
	public function getElemId() {
		return $this->getEntityPropertyValue( 'slug' );	
	}
	
	//
	public function getElemValue() {
		return $this->getEntityPropertyValue( 'slug' );
	}
	
	
	
}


