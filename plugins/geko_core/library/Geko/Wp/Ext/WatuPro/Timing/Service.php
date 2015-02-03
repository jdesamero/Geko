<?php

//
class Geko_Wp_Ext_WatuPro_Timing_Service extends Geko_Wp_Service
{
	
	const STAT_PAUSED = 1;
	const STAT_RESUME = 2;
	
	const STAT_ERROR = 999;
	
	
	
	// obtain timing data
	public function getTiming() {
				
		$iTakingId = intval( $_REQUEST[ 'taking_id' ] );
		
		$oUser = $this->regGet( 'user' );
		
		if ( $iTakingId && $oUser ) {
			
			$iUserId = intval( $oUser->getId() );
			
			$oTiming = $this->oneExt_WatuPro_Timing_Query( array(
				'aggregate_mode' => TRUE,
				'add_taken_exams_fields' => TRUE,
				'taking_id' => $iTakingId
			), FALSE );
			
			
			if (
				( $oTiming->isValid() ) && 
				( $iUserId == intval( $oTiming->getUserId() ) )
			) {
				
				return $oTiming;
			}
			
		}
		
		return NULL;
	}
	
	
	//
	public function processToggleTiming() {
		
		if ( $oTiming = $this->getTiming() ) {

			$sStatus = $oTiming->getStatus();
			
			if ( 'running' == $sStatus ) {
				
				// create new record
				$this
					->insertPause( intval( $oTiming->getTakingId() ) )
					->setStatus( self::STAT_PAUSED )
				;
			
			} elseif ( 'paused' == $sStatus ) {
				
				// update existing record
				$this
					->updateResume( intval( $oTiming->getMaxId() ) )
					->setStatus( self::STAT_RESUME )
				;
				
			}
			
		}
		
	}
	
	
	// get the current timing status
	public function processGetStatus() {
		
		if ( $oTiming = $this->getTiming() ) {
			
			$sStatus = $oTiming->getStatus();
			
			if ( 'running' == $sStatus ) {
				
				$this->setStatus( self::STAT_RESUME );
				
			} elseif ( 'paused' == $sStatus ) {
				
				$this->setStatus( self::STAT_PAUSED );				
			}
			
		}
		
	}
	
	
	
	//// db helpers
	
	//
	public function insertPause( $iTakingId ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oDb->insert( '##pfx##watupro_timing', array(
			'taking_id' => $iTakingId,
			'pause_time' => current_time( 'mysql' )
		) );
		
		return $this;
	}
	
	//
	public function updateResume( $iTimingId ) {
		
		$oDb = Geko_Wp::get( 'db' );

		$oDb->update(
			'##pfx##watupro_timing',
			array( 'resume_time' => current_time( 'mysql' ) ),
			sprintf( 'ID = %d', $iTimingId )
		);
		
		return $this;
	}
	
	
}



