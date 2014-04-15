<?php

//
class Gloc_Bootstrap extends Geko_Wp_Bootstrap
{

	//
	public function start() {
		
		parent::start();
		
		// Gloc_User_Manage::getInstance()->init();
		Gloc_Post_Meta::getInstance()->init();
		Gloc_Page_Meta::getInstance()->init();
		
	}

}


Gloc_Bootstrap::getInstance()->init();


