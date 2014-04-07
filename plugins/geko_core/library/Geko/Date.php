<?php

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
	
	
	
}



