<?php
/*
 * "geko_core/library/Geko.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * static class container for form enumerations
 */

//
class Geko_Wp_Enumeration extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'geko_enum_id';
	protected $_sEntitySlugVarName = 'geko_enum_slug';
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'enum_id' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}
	
	
}


