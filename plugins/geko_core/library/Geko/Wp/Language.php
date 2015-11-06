<?php
/*
 * "geko_core/library/Geko/Wp/Language.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Language extends Geko_Wp_Entity
{
	protected $_sEntityIdVarName = 'geko_lang_id';
	protected $_sEntitySlugVarName = 'geko_lang_code';
	
	protected $_sEditEntityIdVarName = 'lang_id';
	
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'lang_id' )
			->setEntityMapping( 'slug', 'code' )
		;
		
		return $this;
	}
	
}


