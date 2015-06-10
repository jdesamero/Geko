<?php

//
class Geko_Wp_Ext_WatuPro_Qcats extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'name', 'name' )

		;
		
		return $this;
		
	}
	
	
	//
	public function getCorrectPercentage() {
		
		$iNumQuestions = intval( $this->getEntityPropertyValue( 'num_questions' ) );
		$iNumCorrectQuestions = intval( $this->getEntityPropertyValue( 'num_correct_questions' ) );
		
		return number_format( ( $iNumCorrectQuestions / $iNumQuestions ) * 100 );
	}
	
	
}

