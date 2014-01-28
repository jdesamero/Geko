<?php

// entity
class Gloc_User extends Geko_Wp_User
{	
	
	//
	public function init() {
		$this->addMetaHandler( 'Gloc_User_Meta' );
		return parent::init();
	}
	
	
	
}

