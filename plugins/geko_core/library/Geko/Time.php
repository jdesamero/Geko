<?php

//
class Geko_Time
{

	//
	public static function formatSecs( $iSecs ) {
		
		$iHrs = NULL;
		$iMins = NULL;
		
		// get minutes
		if ( $iSecs > 60 ) {
			
			$iMins = floor( $iSecs / 60 );
			$iSecs = $iSecs % 60;
			
			if ( $iMins > 60 ) {
				
				$iHrs = floor( $iMins / 60 );
				$iMins = $iMins % 60;
			}
		}
		
		
		
		$sOut = '';
		
		if ( $iHrs ) {
			$sOut .= sprintf( '%d hr%s ', $iHrs, ( ( $iHrs > 1 ) ? 's' : '' ) );
		}
		
		if ( $iMins ) {
			$sOut .= sprintf( '%s min%s ', $iMins, ( ( $iMins > 1 ) ? 's' : '' ) );
		}
		
		if ( $iSecs ) {
			$sOut .= sprintf( '%s sec%s', $iSecs, ( ( $iSecs > 1 ) ? 's' : '' ) );
		}
		
		
		return $sOut;	
	}
	

}