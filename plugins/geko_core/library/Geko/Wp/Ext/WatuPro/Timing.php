<?php

//
class Geko_Wp_Ext_WatuPro_Timing extends Geko_Wp_Entity
{
	
	//// start static methods
	
	// we're doing it this way so these can be re-used elsewhere
	
	//
	public static function calculateStatus( $oTiming ) {
		
		$iEndTs = intval( $oTiming->getEndTimeTs() );

		// if the end time is less than a certain value, it means the test was not finished
		if ( $iEndTs < 946684900 ) {
			
			$iMaxPauseTimeTs = $oTiming->getMaxPauseTimeTs();
			$iMaxResumeTimeTs = $oTiming->getMaxResumeTimeTs();
			
			// an incomplete "timing entry" means we're paused
			if ( $iMaxPauseTimeTs && !$iMaxResumeTimeTs ) {
				
				return 'paused';
			}
			
			// only other possibility is that the timer is running
			return 'running';
		}
		
		return 'complete';
	
	}
	
	//
	public static function calculateElapsedTime( $oTiming ) {
		
		$sStatus = $oTiming->getStatus();
		
		$iEndTs = 0;
		
		if ( 'complete' == $sStatus ) {
			$iEndTs = $oTiming->getEndTimeTs();
		} elseif ( 'paused' == $sStatus ) {
			$iEndTs = $oTiming->getMaxPauseTimeTs();		
		} else {
			// we're running
			$iEndTs = time();
		}
		
		$iStartTs = $oTiming->getStartTimeTs();
		
		$iPauseInterval = intval( $oTiming->getEntityPropertyValue( 'pause_interval' ) );
		
		return ( $iEndTs - $iStartTs ) - $iPauseInterval;
	}
	
	
	
	//// start regular methods
	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
		;
		
		return $this;
	}
	
	
	// return either: "complete", "paused" or "running"
	public function getStatus() {
		return self::calculateStatus( $this );
	}
	
	
	// calculate the elapsed test time, in seconds
	public function getElapsedTime() {
		return self::calculateElapsedTime( $this );
	}
	
	
}




