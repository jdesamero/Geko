<?php

//
class Geko_Wp_Ext_WatuPro_Timing extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
		;
		
		return $this;
	}
	
	
	// return either: "paused" or "running"
	public function getStatus() {
		
		$sRes = '';
		
		if ( !$this->getEntityPropertyValue( 'num_timings' ) ) {
			
			// no timings have been set, so we're running
			$sRes = 'running';
		
		} else {
			
			// timings have been set
			
			if ( !$this->getEntityPropertyValue( 'max_resume_time_ts' ) ) {
				
				// the max_resume_time of the last timing is not set
				// which means we're paused
				$sRes = 'paused';
				
			} else {
				
				// the last timing was resumed, so we're running
				$sRes = 'running';
			}
			
		}
		
		return $sRes;
	}
	

}




