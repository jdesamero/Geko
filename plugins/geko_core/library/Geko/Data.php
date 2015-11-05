<?php
/*
 * "geko_core/library/Geko/Data.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * data formatting utility for associative data
 */

//
class Geko_Data
{
	
	//
	public static function format( $mInput, $mFormat, $aParams = array() ) {
		
		
		//// declare first
		
		$aInput = NULL;
		$aFormat = NULL;
		$aOutput = array();
		
		
		
		//// get input
		
		if ( is_string( $mInput ) ) {
			
			$mInput = strtoupper( $mInput );
			
			if ( in_array( $mInput,
				array( 'SERVER', 'GET', 'POST', 'FILES', 'COOKIE', 'SESSION', 'REQUEST', 'ENV' )
			) ) {
				
				$sKey = sprintf( '_%s', $mInput );
				
				$aInput = $GLOBALS[ $sKey ];
				
			} else {
				
				throw new Exception( sprintf( '%s: Invalid input specified: %s', __METHOD__, $mInput ) );
				
			}
			
		} elseif ( is_array( $mInput ) ) {
			
			$aInput = $mInput;
			
		}
		
		
		
		//// apply formatting
		
		// $aFormat is an associative array, keys are used for the output
		// the values can contain: value and format
		
		if ( $mFormat instanceof Geko_Sql_Table ) {
			
			$aFormat = $mFormat->toJsonEncodable();
		
		} elseif ( is_array( $mFormat ) ) {
			
			$aFormat = $mFormat;
			
		}
		
		
		
		//// sanitize
		
		if ( !is_array( $aInput ) ) $aInput = array();
		if ( !is_array( $aFormat ) ) $aFormat = array();
		
		
		
		//// perform formatting
		
		$sInputPrefix = '';
		if ( isset( $aParams[ 'input_prefix' ] ) ) {
			$sInputPrefix = $aParams[ 'input_prefix' ];
		}
		
		foreach ( $aFormat as $sKey => $mValue ) {
			
			$sFmtKey = sprintf( '%s%s', $sInputPrefix, $sKey );
			
			$mInput = $aInput[ $sFmtKey ];
			
			if (
				( is_array( $mValue ) ) && 
				( $sFormat = $mValue[ 'format' ] )
			) {
				$mInput = self::formatInput( $mInput, $sFormat );
			}
			
			$aOutput[ $sKey ] = $mInput;
			
		}
		
		
		return $aOutput;
		
	}
	
	
	//// helpers
	
	//
	public static function formatInput( $mValue, $sFormat ) {

		if ( 'bool' == $sFormat ) {
			
			$mValue = intval( $mValue ) ? TRUE : FALSE ;
			
		} elseif ( 'int' == $sFormat ) {
			
			$mValue = intval( $mValue );
			
		} elseif ( 'float' == $sFormat ) {
			
			$mValue = floatval( $mValue );
			
		} elseif ( 'string' == $sFormat ) {
			
			$mValue = strval( $mValue );
		}
		
		return $mValue;
		
	}
	
	
	
}


