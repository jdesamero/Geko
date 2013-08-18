<?php

//
class Geko_Wp_Language_String extends Geko_Wp_Entity
{
	
	//// object oriented functions
		
	//
	public function init()
	{
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'str_id' )
			->setEntityMapping( 'content', 'val' )
			->setEntityMapping( 'value', 'val' )
			->setEntityMapping( 'key_id', 'mkey_id' )
		;
		
		return $this;
	}
	
	
}



