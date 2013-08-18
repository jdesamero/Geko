<?php

//
class Geko_Wp_Form_Response extends Geko_Wp_Entity
{
	
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'fmrsp_id' )
		;
		
		return $this;
		
	}
	
	
}


