<?php

//
class GekoX_Test_MultiKey extends Geko_Entity
{

	protected $_sEntityIdVarName = 'sec_id:sec_idx';
	// protected $_sEntitySlugVarName = '';
	
	// protected $_sEditEntityIdVarName = 'sec_id:sec_idx';
	
	//
	public function init() {
		
		parent::init();
		
		$this->setEntityMapping( 'trait', 'color:size:age' );
		
		return $this;
	}
	
}

