<?php

//
class Geko_Wp_Post_ExpirationDate_Delegate extends Geko_Delegate
{
	
	
	//
	public function getDateTimeExpired( $sFormat ) {
		
		$oPost = $this->_oSubject;
		
		if ( $sExpiryDate = $oPost->getExpiryDate() ) {
			return mysql2date( $sFormat, $sExpiryDate );
		} else {
			return $oPost->getDateTimeCreated( $sFormat );
		}
	}
	
	//
	public function getDateTimeStart( $sFormat ) {
		
		$oPost = $this->_oSubject;
		
		if ( $sStartDate = $oPost->getStartDate() ) {
			return mysql2date( $sFormat, $sStartDate );
		} else {
			return $oPost->getDateTimeCreated( $sFormat );
		}
	}
	
	//
	public function getDateTimeRange( $sFormat = 'F j, Y', $sOutput = 'From %s to %s', $sCompareFormat = 'Y-m-d' ) {
		
		$oPost = $this->_oSubject;
		
		$sMinDate = $oPost->getMinDate();
		$sMaxDate = $oPost->getMaxDate();
		
		$sMinDateCmp = date( $sCompareFormat, strtotime( $oPost->getMinDate() ) );
		$sMaxDateCmp = date( $sCompareFormat, strtotime( $oPost->getMaxDate() ) );
		
		if ( $sMinDateCmp != $sMaxDateCmp ) {
			return sprintf(
				$sOutput,
				date( $sFormat, strtotime( $sMinDate ) ),
				date( $sFormat, strtotime( $sMaxDate ) )
			);
		}
		
		// no date range
		return FALSE;
	}
	
	//
	public function getDateTimeEvent( $sFormat = 'F j, Y', $sOutput = 'From %s to %s', $sCompareFormat = 'Y-m-d' ) {
		
		$oPost = $this->_oSubject;
		
		if ( $sRange = $this->getDateTimeRange( $sFormat, $sOutput, $sCompareFormat ) ) {
			return $sRange;
		}
		
		return $this->getDateTimeExpired( $sFormat );
	}
	
	
}






