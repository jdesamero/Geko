<?php

class Geko_Wp_Booking_Schedule_Time extends Geko_Wp_Entity
{
		
	protected $_sEntityIdVarName = 'geko_bksctm_id';
	protected $_sEntitySlugVarName = 'geko_bksctm_slug';
	
	protected $_sEditEntityIdVarName = 'bksctm_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'bksctm_id' )
		;
		
		return $this;
	}
	
	
	
}



