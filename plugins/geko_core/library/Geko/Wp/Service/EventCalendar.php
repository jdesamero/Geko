<?php

//
class Geko_Wp_Service_EventCalendar extends Geko_Wp_Service
{
	
	const STAT_SUCCESS = 1;
	
	const STAT_ERROR = 999;
	

	//
	public function processGetEvents() {
		
		
		// gather category ids
		$aCalCats = new Gloc_Category_Query( array( 'add_to_calendar' => 1 ) );
		
		$aParams = array(
			'cat' => $aCalCats->implode( array( '##Id##', ',' ) ),
			'add_start_date_field' => TRUE,
			'add_expiry_date_field' => TRUE
		);
		
		
		$iYear = intval( $_GET[ 'yr' ] );
		$iMon = intval( $_GET[ 'mon' ] );
		
		
		if ( $iYear && $iMon ) {
			
			$aParams[ 'showposts' ] = -1;
			
			
			$sMonthPart = sprintf( '%04d-%02d', $iYear, $iMon );
			
			$sMinDate = sprintf( '%s-01', $sMonthPart );
			
			$iMaxMonthDay = date( 't', strtotime( $sMinDate ) );
			$sMaxDate = sprintf( '%s-%s', $sMonthPart, $iMaxMonthDay );
			
			
			
			$aParams[ 'where_min_date' ] = $sMinDate;
			$aParams[ 'where_max_date' ] = $sMaxDate;
			$aParams[ 'add_min_date_field' ] = 1;
			$aParams[ 'add_max_date_field' ] = 1;
			
			$aParams[ 'orderby' ] = 'start_date';
			$aParams[ 'order' ] = 'ASC';
			
			
			
			$aEvents = array();
			$aData = array();
			
			$aPosts = new Gloc_Post_Query( $aParams, FALSE );
			
			foreach ( $aPosts as $i => $oPost ) {
				
				$aEntry = $this->formatEntry( $oPost );
				
				$aData[ $i ] = $aEntry;
				
				// find range of given min/max date
				
				$aMinDate = explode( '-', $oPost->getMinDate() );
				$aMaxDate = explode( '-', $oPost->getMaxDate() );
				
				$iMinMon = intval( $aMinDate[ 1 ] );
				$iMinDay = intval( $aMinDate[ 2 ] );

				$iMaxMon = intval( $aMaxDate[ 1 ] );
				$iMaxDay = intval( $aMaxDate[ 2 ] );
				
				$iStart = $iMinDay;
				$iEnd = $iMaxDay;
				
				if ( $iMinMon != $iMon ) {
					// min is in a different month
					$iStart = 1;
				}
				
				if ( $iMaxMon != $iMon ) {
					// min is in a different month
					$iEnd = $iMaxMonthDay;
				}
				
				for ( $j = $iStart; $j <= $iEnd; $j++ ) {
					$aEvents[ $j ][] = $i;
				}
				
			}
			
			$this
				->setResponseValue( 'events', $aEvents )
				->setResponseValue( 'data', $aData )
			;
			
			
		} else {
			
			$aRange = array();
			
			$aParams[ 'showposts' ] = 1;
			$aParams[ 'orderby' ] = 'expiry_date';
			$aParams[ 'order' ] = 'DESC';
			
			// max
			$aPosts = new Gloc_Post_Query( $aParams, FALSE );
			$oPost = $aPosts->getOne();
			
			$aRange[ 'max' ] = array(
				'day' => $oPost->getDateTimeExpired( 'j' ),
				'mon' => $oPost->getDateTimeExpired( 'n' ) - 1,		// compensate for javascript
				'year' => $oPost->getDateTimeExpired( 'Y' )
			);
			
			
			$aParams[ 'order' ] = 'ASC';
			
			// min
			$aPosts = new Gloc_Post_Query( $aParams, FALSE );
			$oPost = $aPosts->getOne();
			
			$aRange[ 'min' ] = array(
				'day' => $oPost->getDateTimeExpired( 'j' ),
				'mon' => $oPost->getDateTimeExpired( 'n' ) - 1,		// compensate for javascript
				'year' => $oPost->getDateTimeExpired( 'Y' )
			);
			
			
			$this->setResponseValue( 'range', $aRange );
			
		}
		
		
		$this->setStatus( self::STAT_SUCCESS );
		
	}
	
	
	//
	public function formatEntry( $oPost ) {
		return array(
			'title' => $oPost->getTitle(),
			'date' => $oPost->getDateTimeEvent(),
			'content' => $oPost->getTheContent()
		);
	}
	
	
}


