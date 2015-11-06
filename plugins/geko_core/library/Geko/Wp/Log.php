<?php
/*
 * "geko_core/library/Geko/Wp/Log.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Log extends Geko_Wp_Entity
{

	protected $_sEntityIdVarName = 'log_id';
	// protected $_sEntitySlugVarName = 'generic_slug';

	protected $_sEditEntityIdVarName = 'log_id';
	
	
	//
	public function init() {
		
		parent::init();
		
		$this->setEntityMapping( 'id', 'log_id' );
		
		return $this;
	}
	
	
}


