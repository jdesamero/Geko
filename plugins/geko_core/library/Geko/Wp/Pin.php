<?php

class Geko_Wp_Pin extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'pin_id';
	protected $_sEntitySlugVarName = 'pin';
	
	protected $_sListingTitle = 'PIN Id';
	protected $_sEditEntityIdVarName = 'pin_id';
	
	
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'pin_id' )
			->setEntityMapping( 'slug', 'pin' )
			->setEntityMapping( 'title', 'pin' )
		;
		
		return $this;
	}
	
	
	
	
	
	
}


