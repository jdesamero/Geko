<?php
/*
 * "geko_core/library/Geko/Wp/Comment.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_Comment extends Geko_Wp_Entity
{
	
	protected $_sEntityIdVarName = 'ID';
	// protected $_sEntitySlugVarName = ???;
	
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'comment_ID' )
			->setEntityMapping( 'content', 'comment_content' )
			->setEntityMapping( 'date_created', 'comment_date' )
			->setEntityMapping( 'date_modified', 'comment_date' )
			->setEntityMapping( 'date_created_gmt', 'comment_date_gmt' )
			->setEntityMapping( 'date_modified_gmt', 'comment_date_gmt' )
			->setEntityMapping( 'post_id', 'comment_post_ID' )
		;
		
		return $this;
	}
	
	
}


