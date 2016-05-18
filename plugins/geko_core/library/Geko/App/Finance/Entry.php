<?php
/*
 * "geko_core/library/Geko/App/Finance/Entry.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Entry extends Geko_App_Entity
{
	
	protected $_sEntityIdVarName = 'id';
	
	
	//// instance methods
	
	//
	public function init() {
		
		parent::init();
		
		
		$this
			->setEntityMapping( 'content', 'details' )
		;
		
		return $this;
	}

	
	
}


