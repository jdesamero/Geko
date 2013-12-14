<?php

//
class Geko_Wp_EmailMessage_Header extends Geko_Wp_Entity
{

	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'hdr_id' )
			->setEntityMapping( 'content', 'val' )
			->setEntityMapping( 'value', 'val' )
			->setEntityMapping( 'key', 'name' )
		;
		
		return $this;
	}


}

