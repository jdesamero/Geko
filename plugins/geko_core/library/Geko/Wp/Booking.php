<?php

class Geko_Wp_Booking extends Geko_Wp_Entity
{
		
	protected $_sEntityIdVarName = 'geko_bkng_id';
	protected $_sEntitySlugVarName = 'geko_bkng_slug';
	
	protected $_sEditEntityIdVarName = 'bkng_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'bkng_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'slug', 'slug' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}
	
	
	
	
}



