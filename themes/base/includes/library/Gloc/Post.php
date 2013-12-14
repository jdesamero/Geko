<?php

class Gloc_Post extends Geko_Wp_Post
{
	
	//
	public function init() {
		$this->addMetaHandler( 'Gloc_Post_Meta' );
		return parent::init();
	}
	
	
	
}



