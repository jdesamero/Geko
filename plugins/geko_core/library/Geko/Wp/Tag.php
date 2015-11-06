<?php
/*
 * "geko_core/library/Geko/Wp/Tag.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * object oriented wrapper for a tag object
 */

//
class Geko_Wp_Tag extends Geko_Wp_Entity
{
	//// implement concrete methods for tag
	
	protected $_sEntitySlugVarName = 'slug';
	
	//
	public function getEntityFromId( $iEntityId ) {
		return get_tag( $iEntityId );
	}
	
	//
	public function init() {
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'term_id' )
			->setEntityMapping( 'title', 'name' )
			->setEntityMapping( 'content', 'description' )
			->setEntityMapping( 'excerpt', 'description' )
		;
		
		return $this;
	}
	
	//
	public function getPermalink() {
		return get_tag_link( $this->getId() );
	}
	
	
	//
	public function getDefaultEntityValue() {
		
		global $wp_query;
		
		if ( is_tag() ) {
			return $wp_query->query_vars[ 'tag' ];
		}
		
		return NULL;
	}
	
}


