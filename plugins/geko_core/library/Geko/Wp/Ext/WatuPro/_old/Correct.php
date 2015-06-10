<?php

//
class Geko_Wp_Ext_WatuPro_Correct extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'c.ID' )
			->setEntityMapping( 'correct', 'COUNT( "c.is_correct" )' )
			
		;
		
		return $this;
		
	}


}

