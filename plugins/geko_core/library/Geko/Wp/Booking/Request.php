<?php

class Geko_Wp_Booking_Request extends Geko_Wp_Entity
{
		
	protected $_sEntityIdVarName = 'geko_bkreq_id';
	protected $_sEntitySlugVarName = 'geko_bkreq_slug';
	
	protected $_sEditEntityIdVarName = 'bkreq_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'bkreq_id' )
		;
		
		return $this;
	}
	
	
	
}



