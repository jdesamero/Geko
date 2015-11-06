<?php
/*
 * "geko_core/library/Geko/Wp/Pin.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Pin extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'pin_id';
	protected $_sEntitySlugVarName = 'pin';
	
	protected $_sListingTitle = 'PIN Id';
	protected $_sEditEntityIdVarName = 'pin_id';
	
	
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'pin_id' )
			->setEntityMapping( 'slug', 'pin' )
			->setEntityMapping( 'title', 'pin' )
		;
		
		return $this;
	}
	
	
	
	
	
	
}


