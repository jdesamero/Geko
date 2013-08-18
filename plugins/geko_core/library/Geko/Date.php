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
	
	// adapted from http://api.drupal.org/api/function/format_interval/7
	public static function formatInterval( $iTimestamp, $iGranularity = 2, $sFormat = 'short' ) {
		
		$iInterval = time() - $iTimestamp;
		
		$sOut = '';
		
		foreach ( self::$aIntervalUnits as $aUnit ) {
			
			$iValue = $aUnit['value'];
			$sUnit = $aUnit[ $sFormat ];
			
			if ( $iInterval >= $iValue ) {
				
				$iUnit = floor( $iInterval / $iValue );
				
				$sOut .= ( $sOut ? ' ' : '' ) . ( ( $iUnit == 1 ) ? '1 ' . $sUnit : $iUnit . ' ' . $sUnit . 's' );
				$iInterval %= $iValue;
				$iGranularity--;
			}
			
			if ( 0 == $iGranularity ) {
				break;
			}
		}
		
		return $sOut ? $sOut : '0 sec';
	}
	
}



