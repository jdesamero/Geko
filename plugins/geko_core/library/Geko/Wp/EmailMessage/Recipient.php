<?php

//
class Geko_Wp_EmailMessage_Recipient extends Geko_Wp_Entity
{
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'rcpt_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'content', 'email' )
		;
		
		return $this;
	}
	
	
	
}



