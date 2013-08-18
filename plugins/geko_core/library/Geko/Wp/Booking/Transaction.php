<?php

//
class Geko_Wp_Booking_Transaction extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'geko_bktrn_id';
	protected $_sEntitySlugVarName = 'geko_bktrn_slug';
	
	protected $_sEditEntityIdVarName = 'bktrn_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'bktrn_id' )
		;
		
		return $this;
	}





}



