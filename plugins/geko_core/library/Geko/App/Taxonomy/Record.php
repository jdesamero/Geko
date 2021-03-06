<?php
/*
 * "geko_core/library/Geko/App/Taxonomy/Record.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * this is a Geko_Delegate
 */

//
class Geko_App_Taxonomy_Record extends Geko_Entity_Record
{

	//
	public function init() {
	
		$this->addPlugin( 'Geko_App_Meta_Plugin_Record' );
		
		parent::init();
		
		return $this;
	}
	
	
}

