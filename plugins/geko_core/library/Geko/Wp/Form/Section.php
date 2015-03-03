<?php

//
class Geko_Wp_Form_Section extends Geko_Wp_Entity
{
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			
			->addPlugin( 'Geko_Wp_Form_Plugin_LangMeta' )
			->addPlugin( 'Geko_Wp_Form_Plugin_Placeholder' )
			
			->setEntityMapping( 'id', 'fmsec_id' )
			->setEntityMapping( 'title', 'title' )
			->setEntityMapping( 'content', 'description' )
			
			->setData( 'lang_meta_fields', array( 'title', 'description' ) )
		;
		
		return $this;
	}
	
	
	
	//// for use in rendering
	
	//
	public function getElemId() {
		return $this->getEntityPropertyValue( 'slug' );
	}
		
	
	
}


