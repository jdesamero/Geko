<?php

//
class Geko_PhpUnit_TestListener extends Geko_Entity
{
	
	//
	public function init() {
		
		parent::init();
		
		$this->setEntityMapping( 'id', 'idx' );
		
		return $this;
	}
	
	
	
}

