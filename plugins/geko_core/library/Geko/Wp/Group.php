<?php
/*
 * "geko_core/library/Geko/Wp/Group.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Group extends Geko_Wp_Entity
{

	protected $_sEntityIdVarName = 'geko_group_id';
	protected $_sEntitySlugVarName = 'geko_group_slug';
	
	protected $_sEditEntityIdVarName = 'group_id';
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'group_id' )
			->setEntityMapping( 'content', 'description' )
		;
		
		return $this;
	}
	
	
	
	//// specific to this type

	//
	public function getTypeCode() {
		return Geko_Wp_Options_MetaKey::getKey(
			$this->getEntityPropertyValue( 'grptype_id' )
		);
	}
	
	//
	public function getType() {
		return ucwords( str_replace( array( '-', '_' ), ' ', $this->getTypeCode() ) );
	}
	
	
	
}



