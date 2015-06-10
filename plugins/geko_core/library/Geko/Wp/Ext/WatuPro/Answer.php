<?php

//
class Geko_Wp_Ext_WatuPro_Answer extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'content', 'answer' )
			->setEntityMapping( 'correct', 'correct' )
		;
		
		return $this;
	}


}




