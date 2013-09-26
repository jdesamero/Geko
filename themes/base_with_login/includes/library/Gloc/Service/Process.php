<?php

//
class Gloc_Service_Process extends Geko_Wp_Service
{

	const STAT_SUCCESS = 1;
	
	
	const STAT_ERROR = 999;
	
	
	//
	public function process() {
		
		global $wpdb;
		
		if ( $this->isAction( 'some_action' ) ) {
			
			// do DB stuff
			$this->setStatus( self::STAT_SUCCESS );
			
		}
		
		$this->setIfNoStatus( self::STAT_ERROR );
		
		return $this;
	}
	
	
}



