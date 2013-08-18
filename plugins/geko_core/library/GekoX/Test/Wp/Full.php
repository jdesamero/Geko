<?php

class GekoX_Test_Wp_Full extends Geko_Wp_Entity
{
	protected $_sEntityIdVarName = 'geko_test_id';
	protected $_sEntitySlugVarName = 'geko_test_slug';
	
	protected $_sEditEntityIdVarName = 'test_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'test_id' )
			->setEntityMapping( 'title', 'title' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}

}


