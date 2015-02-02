<?php

//
class Geko_Wp_Ext_WatuPro_Purchased extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'name', 'name' )
			->setEntityMapping( 'scheduled', 'is_scheduled' )
			->setEntityMapping( 'expiry', 'schedule_to_formatted' )
			->setEntityMapping( 'cap', 'times_to_take' )
			->setEntityMapping( 'attempts', 'times_taken' )

		;
		
		return $this;
		
	}
	
	//
	public function getExamUrl() {
		return get_permalink( intval( $this->getEntityPropertyValue( 'exam_post_id' ) ) );
	}

}

