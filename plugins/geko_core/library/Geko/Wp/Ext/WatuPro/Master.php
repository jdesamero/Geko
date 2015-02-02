<?php

//
class Geko_Wp_Ext_WatuPro_Master extends Geko_Wp_Entity
{

	
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'ID' )
			->setEntityMapping( 'cap', 'times_to_take' )
			->setEntityMapping( 'attempts', 'times_taken' )
			->setEntityMapping( 'date_schedule_from', 'schedule_from' )
			->setEntityMapping( 'date_schedule_to', 'schedule_to' )			
		;
		
		return $this;
		
	}
	
	//
	public function getRemainingAttempts() {
		
		return
			intval( $this->getEntityPropertyValue( 'times_to_take' ) ) - 
			intval( $this->getEntityPropertyValue( 'times_taken' ) )			
		;
	}
	
	// get a formatted blurb
	public function getAttemptsRemainingBlurb() {
		
		$sMsg = '';
		
		$iRemaining = $this->getRemainingAttempts();
		
		if ( $iRemaining ) {
			
			if ( $iRemaining > 0 ) {
			
				$sMsg = sprintf(
					'%d attempt%s remaining out of %d',
					$iRemaining,
					( ( $iRemaining > 1 ) ? 's' : '' ),
					$this->getTimesToTake()
				);
			
			}
			
		} else {
			
			$sMsg = 'You have no remaining attempts.';
		}
		
		return $sMsg;
	}
	
	//
	public function getExamUrl() {
		return get_permalink( intval( $this->getEntityPropertyValue( 'exam_post_id' ) ) );
	}
	
	//
	public function getInProgress() {
		return intval( $this->getEntityPropertyValue( 'in_progress' ) ) ? TRUE : FALSE ;
	}
	
	// possible return values are:
	// active, expired, scheduled
	public function getScheduleStatus() {
		
		// not scheduled, so treat it as active
		if ( !$this->getIsScheduled() ) {
			return 'active';
		}
		
		$iScheduleFromTs = intval( $this->getEntityPropertyValue( 'schedule_from_ts' ) );
		$iScheduleToTs = intval( $this->getEntityPropertyValue( 'schedule_to_ts' ) );
		
		$iCurrentTs = time();
		
		// the current ts is less than schedule from ts
		if ( $iCurrentTs < $iScheduleFromTs ) {
			return 'scheduled';
		}

		// the current ts is greater than schedule to ts
		if ( $iCurrentTs > $iScheduleToTs ) {
			return 'expired';
		}
		
		return 'active';
	}
	
	//
	public function getIsScheduled() {
		return intval( $this->getEntityPropertyValue( 'is_scheduled' ) ) ? TRUE : FALSE ;
	}
	
}


