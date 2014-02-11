<?php

//
class Geko_Db_Sqlite
{
		
	
	
	
	//// helpers for Geko_Entity_Query class
	
	// deal with vendor specific functionality
	
	//
	public static function getTimestamp( $iTimestamp = NULL ) {
		if ( NULL == $iTimestamp ) $iTimestamp = time();
		return @date( 'Y-m-d H:i:s', $iTimestamp );
	}
	
	//
	public static function gekoQueryInit( $oQuery, $aParams ) {
		
		return $oQuery;
	}
	
	//
	public static function gekoQueryOrderRandom( $oQuery, $aParams ) {
		
		$oQuery->order( 'RANDOM()', '', 'random' );
		
		return $oQuery;
	}
	
	//
	public static function gekoQueryFoundRows( $oEntityQuery ) {
		
		$oQuery = $oEntityQuery->constructQuery( NULL, TRUE );
		
		// re-jig query as count(*)
		$oQuery
			->unsetField()
			->unsetOrder()
			->unsetLimitOffset()
		;
		
		if ( $oQuery->hasGroup() ) {
			
			$oWrapQuery = new Geko_Sql_Select();
			
			$oWrapQuery
				->field( 'COUNT(*)', 'num_rows' )
				->from( $oQuery )
			;
			
			$oQuery = $oWrapQuery;				// re-assign
			
		} else {
			$oQuery->field( 'COUNT(*)', 'num_rows' );
		}
		
		
		return strval( $oQuery );
	}
	
	
	
	
	
}


