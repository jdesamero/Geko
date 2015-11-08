<?php

class Gloc_Page extends Geko_Wp_Page
{
	
	//
	public function init() {
		$this->addMetaHandler( 'Gloc_Page_Meta' );
		return parent::init();
	}
	
	
	
}



