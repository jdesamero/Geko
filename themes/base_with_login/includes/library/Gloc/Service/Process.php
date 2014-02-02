<?php

//
class Gloc_Service_Process extends Geko_Wp_Service
{

	const STAT_SUCCESS = 1;
	
	
	const STAT_ERROR = 999;
	
	
	//
	public function processSomeAction() {
		
		$bEverythingIsCool = TRUE;
		
		if ( $bEverythingIsCool ) {
			
			$this->setResponseValue( 'data', array(
				'foo' => 'Roses are Red',
				'bar' => 'Violets are Blue'
			) );
			
			$this->setStatus( self::STAT_SUCCESS );
		}
			
	}
	
	
}



