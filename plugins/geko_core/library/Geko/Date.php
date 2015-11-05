<?php
/*
 * "geko_core/library/Geko/Date.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Date
{
	
	protected static $aIntervalUnits = array(
		array( 'value' => 31536000, 'long' => 'year', 'short' => 'yr' ),
		array( 'value' => 2592000, 'long' => 'month', 'short' => 'mon' ),
		array( 'value' => 604800, 'long' => 'week', 'short' => 'wk' ),
		array( 'value' => 86400, 'long' => 'day', 'short' => 'dy' ),
		array( 'value' => 3600, 'long' => 'hours', 'short' => 'hr' ),
		array( 'value' => 60, 'long' => 'minute', 'short' => 'min' ),
		array( 'value' => 1, 'long' => 'second', 'short' => 'sec' )
	);
	
	
	// $iInterval is in seconds
	public static function formatIntervalSecs( $iInterval, $iGranularity = 2, $sFormat = 'short' ) {
		
		$sOut = '';
		
		foreach ( self::$aIntervalUnits as $aUnit ) {
			
			$iValue = $aUnit[ 'value' ];
			$sUnit = $aUnit[ $sFormat ];
			
			if ( $iInterval >= $iValue ) {
				
				$iUnit = floor( $iInterval / $iValue );
				
				$sOut .= ( $sOut ? ' ' : '' ) . ( ( $iUnit == 1 ) ? sprintf( '1 %s', $sUnit ) : sprintf( '%d %ss', $iUnit, $sUnit ) );
				$iInterval %= $iValue;
				$iGranularity--;
			}
			
			if ( 0 == $iGranularity ) {
				break;
			}
		}
		
		return $sOut ? $sOut : '0 sec';
	}
	
	
	// adapted from http://api.drupal.org/api/function/format_interval/7
	public static function formatInterval( $iTimestamp, $iGranularity = 2, $sFormat = 'short' ) {
		
		return self::formatIntervalSecs( time() - $iTimestamp );
	}
	
	
	
	//
	public static function formatDdHhMmSs( $iNumSeconds, $bIncludeZeroed = FALSE ) {
		
		$iDays   = floor( $iNumSeconds / 86400 );
		$iHours   = floor( ( $iNumSeconds - ( $iDays * 86400 ) ) / 3600 );
		$iMinutes = floor( ( $iNumSeconds - ( $iDays * 86400 ) - ( $iHours * 3600 ) ) / 60 );
		$iSeconds = $iNumSeconds - ( $iDays * 86400 ) - ( $iHours * 3600 ) - ( $iMinutes * 60 );
		
		// sprintf( '%02d:%02d:%02d:%02d', $iDays, $iHours, $iMinutes, $iSeconds );
		
		// minutes and seconds are always included by default
		$sOut = sprintf( '%02d:%02d', $iMinutes, $iSeconds );
		
		// if include zeroed was set or there is an hours value
		if ( $bIncludeZeroed || $iHours ) {
			$sOut = sprintf( '%02d:%s', $iHours, $sOut );		
		}
		
		// if include zeroed was set or there is a days value
		if ( $bIncludeZeroed || $iDays ) {
			$sOut = sprintf( '%02d:%s', $iDays, $sOut );		
		}
		
		return $sOut;
		
	}
	
	
	
}



