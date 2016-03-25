<?php
/*
 * "geko_core/library/Geko/App/Taxonomy/Item.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Taxonomy_Item extends Geko_App_Entity
{
	
	protected $_sEntityIdVarName = 'id';
	
	
	//// instance methods
	
	//
	public function init() {
		
		parent::init();
		
		
		$this
			
			->addPlugin( 'Geko_App_Meta_Plugin_Entity' )
			
			->setEntityMapping( 'title', 'label' )
			
		;
		
		return $this;
	}
	
	

}


