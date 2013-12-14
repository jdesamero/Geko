<?php

class Geko_Wp_Form_MetaValue extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'fmmd_id:fmmv_idx';
	// protected $_sEntitySlugVarName = '';
	
	protected $_sEditEntityIdVarName = 'fmmd_id:fmmv_idx';
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'title', 'label' )
			->setEntityMapping( 'content', 'help' )
			->setEntityMapping( 'meta_data_id', 'fmmd_id' )
			->setEntityMapping( 'meta_value_index', 'fmmv_idx' )
		;
		
		return $this;
	}
	
	
	
	
	
}


