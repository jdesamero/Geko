<?php

//
class Geko_Wp_Form_ResponseValue extends Geko_Wp_Entity
{
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'fmrv_id' )
		;
		
		return $this;
		
	}
	
	
}


