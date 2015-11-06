<?php
/*
 * "geko_core/library/Geko/Wp/Navigation.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Navigation extends Geko_Wp_Entity
{
	protected $_sEntityIdVarName = 'geko_nav_id';
	protected $_sEntitySlugVarName = 'geko_nav_slug';
	
	protected $_sEditEntityIdVarName = 'nav_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'nav_id' )
			->setEntityMapping( 'slug', 'code' )
			->setEntityMapping( 'title', 'label' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}
	
	
	
		
}