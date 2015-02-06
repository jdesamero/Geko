<?php

//
class Geko_Wp_Ext_WatuPro_Timing_Service extends Geko_Wp_Service
{
	
	const STAT_PAUSED = 1;
	const STAT_RESUME = 2;
	
	const STAT_TAKING_ID = 3;
	
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
			
			$this->setResponseValue( 'elapsed_seconds', $oTiming->getElapsedTime() );
			
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
	
	//
	public function processGetTakingId() {
		
		$iExamId = intval( $_REQUEST[ 'exam_id' ] );
		
		$oUser = $this->regGet( 'user' );
		
		if ( $iExamId && $oUser ) {
		
			$oTaking = $this->oneExt_WatuPro_TakenExams_Query( array(
				'exam_id' => intval( $iExamId ),
				'user_id' => $iUserId,
				'orderby' => 't.ID',
				'order' => 'DESC',
				'limit' => 1
			), FALSE );
			
			$this
				->setResponseValue( 'taking_id', $oTaking->getId() )
				->setStatus( self::STAT_TAKING_ID )
			;
		}
		
	}
	
	
	// get the current timing status
	public function processGetStatus() {
		
		if ( $oTiming = $this->getTiming() ) {
			
			$sStatus = $oTiming->getStatus();
			
			$this->setResponseValue( 'elapsed_seconds', $oTiming->getElapsedTime() );
			
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
			'pause_time' => $oDb->getTimestamp()
		) );
		
		return $this;
	}
	
	//
	public function updateResume( $iTimingId ) {
		
		$oDb = Geko_Wp::get( 'db' );

		$oDb->update(
			'##pfx##watupro_timing',
			array( 'resume_time' => $oDb->getTimestamp() ),
			sprintf( 'ID = %d', $iTimingId )
		);
		
		return $this;
	}
	
	
}



