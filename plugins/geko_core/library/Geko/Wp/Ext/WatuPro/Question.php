<?php

//
class Geko_Wp_Ext_WatuPro_Question extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'content', 'question' )
			->setEntityMapping( 'order', 'sort_order' )
			->setEntityMapping( 'category', 'name' )
			->setEntityMapping( 'explanation', 'explain_answer' )
			->setEntityMapping( 'cat_id', 'cat_id' )
		;
		
		return $this;
	}


}




