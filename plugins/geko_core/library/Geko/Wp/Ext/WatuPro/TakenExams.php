<?php

//
class Geko_Wp_Ext_WatuPro_TakenExams extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'content', 'percent_correct' )
			->setEntityMapping( 'name', 'name' )
			->setEntityMapping( 'cap', 'times_to_take' )			
		;
		
		return $this;
		
	}
	
	//
	public function getExamUrl() {
		return get_permalink( intval( $this->getEntityPropertyValue( 'exam_post_id' ) ) );
	}
	
	
	
	// return either: "complete", "paused" or "running"
	public function getStatus() {
		return Geko_Wp_Ext_WatuPro_Timing::calculateStatus( $this );
	}
	
	
	// calculate the elapsed test time, in seconds
	public function getElapsedTime() {
		return Geko_Wp_Ext_WatuPro_Timing::calculateElapsedTime( $this );
	}
	
	
	
}

