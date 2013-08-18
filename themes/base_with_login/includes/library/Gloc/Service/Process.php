<?php

//
class Gloc_Service_Process extends Geko_Wp_Service
{

	const STAT_SUCCESS = 1;
	const STAT_ERROR = 2;
	
	
	//
	public function process() {
		
		global $wpdb;
		
		if ( 'some_action' == $_REQUEST[ 'subaction' ] ) {
			
			// do DB stuff
			$aAjaxResponse[ 'status' ] = self::STAT_SUCCESS;
			
		}
		
		if ( !$aAjaxResponse[ 'status' ] ) {
			$aAjaxResponse[ 'status' ] = self::STAT_ERROR;
		}
		
		$this->aAjaxResponse = $aAjaxResponse;
		
		return $this;
	}
	
	
}



