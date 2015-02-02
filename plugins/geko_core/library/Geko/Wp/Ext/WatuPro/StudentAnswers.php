<?php

//
class Geko_Wp_Ext_WatuPro_StudentAnswers extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'content', 'answer' )
		;
		
		return $this;
		
	}


}

