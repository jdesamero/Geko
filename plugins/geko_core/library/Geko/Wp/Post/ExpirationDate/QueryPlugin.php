<?php

class Geko_Wp_Post_ExpirationDate_QueryPlugin extends Geko_Entity_Query_Plugin
{
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		
		$bHideExpired = $aParams[ 'hide_expired' ];
		$bHideUnexpired = $aParams[ 'hide_unexpired' ];
		
		$bAddStart = $aParams[ 'add_start_date_field' ];
		$bAddExpiry = $aParams[ 'add_expiry_date_field' ];
		$bAddIsExpired = $aParams[ 'add_is_expired_field' ];
		$bAddMin = $aParams[ 'add_min_date_field' ];
		$bAddMax = $aParams[ 'add_max_date_field' ];

		$iStartDay = intval( $aParams[ 'where_start_day' ] );
		$iStartMon = intval( $aParams[ 'where_start_monthnum' ] );
		$iStartYear = intval( $aParams[ 'where_start_year' ] );

		$iExpDay = intval( $aParams[ 'where_expiry_day' ] );
		$iExpMon = intval( $aParams[ 'where_expiry_monthnum' ] );
		$iExpYear = intval( $aParams[ 'where_expiry_year' ] );
		
		$sMin = $aParams[ 'where_min_date' ];
		$sMax = $aParams[ 'where_max_date' ];
		$sBetween = $aParams[ 'where_between_date' ];
		
		$bOrderByStart = ( 'start_date' == $aParams[ 'orderby' ] );
		$bOrderByExpiry = ( 'expiry_date' == $aParams[ 'orderby' ] );
		
		
		if (
			$bHideExpired || $bHideUnexpired ||
			$bAddStart || $bAddExpiry || $bAddIsExpired || $bAddMin || $bAddMax || 
			$iStartDay || $iStartMon || $iStartYear || 
			$iExpDay || $iExpMon || $iExpYear ||
			$sMin || $sMax || $sBetween ||
			$bOrderByStart || 	$bOrderByExpiry
		) {
			
			
			////// join
			
			$oQuery
				->joinLeft( $wpdb->geko_expiry, 'gpexp' )
					->on( 'gpexp.post_id = p.ID' )
			;
			
			
			
			////// fields
			
			if ( $bAddStart ) {
				$oQuery->field( 'COALESCE(gpexp.start_date, p.post_date)', 'start_date' );
			}
			
			if ( $bAddExpiry ) {
				$oQuery->field( 'COALESCE(gpexp.expiry_date, p.post_date)', 'expiry_date' );
			}
			
			if ( $bAddIsExpired ) {
				$oQuery->field( array( 'gpexp.expiry_date <= ?', current_time( 'mysql' ) ), 'is_expired' );
			}
			
			if ( $bAddMin ) {
				$oQuery->field( 'CAST( COALESCE(gpexp.start_date, gpexp.expiry_date, p.post_date) AS DATE )', 'min_date' );
			}
			
			if ( $bAddMax ) {
				$oQuery->field( 'CAST( COALESCE(gpexp.expiry_date, gpexp.start_date, p.post_date) AS DATE )', 'max_date' );
			}
			
			
			////// where clauses
			
			//// expiry
			
			if ( $bHideExpired ) {
				$oQuery->where( 'COALESCE(gpexp.expiry_date, p.post_date) > ?', current_time( 'mysql' ) );
			}

			if ( $bHideUnexpired ) {
				$oQuery->where( 'COALESCE(gpexp.expiry_date, p.post_date) <= ?', current_time( 'mysql' ) );
			}
			
			
			
			//// start
			
			if ( $iStartDay ) {
				$oQuery->where( 'COALESCE( DAY( gpexp.start_date ), DAY( p.post_date ) ) = ?', $iStartDay );			
			}
			
			if ( $iStartMon ) {
				$oQuery->where( 'COALESCE( MONTH( gpexp.start_date ), MONTH( p.post_date ) ) = ?', $iStartMon );			
			}
			
			if ( $iStartYear ) {
				$oQuery->where( 'COALESCE( YEAR( gpexp.start_date ), YEAR( p.post_date ) ) = ?', $iStartYear );						
			}
			
			
			//// expiry
			
			if ( $iExpDay ) {
				$oQuery->where( 'COALESCE( DAY( gpexp.expiry_date ), DAY( p.post_date ) ) = ?', $iExpDay );			
			}
			
			if ( $iExpMon ) {
				$oQuery->where( 'COALESCE( MONTH( gpexp.expiry_date ), MONTH( p.post_date ) ) = ?', $iExpMon );			
			}
			
			if ( $iExpYear ) {
				$oQuery->where( 'COALESCE( YEAR( gpexp.expiry_date ), YEAR( p.post_date ) ) = ?', $iExpYear );						
			}
			
			
			//// ranges
			
			if ( $sMin ) {
				$oQuery->where( 'CAST( COALESCE( gpexp.expiry_date, gpexp.start_date, p.post_date ) AS DATE ) >= CAST( ? AS DATE )', $sMin );			
			}
			
			if ( $sMax ) {
				$oQuery->where( 'CAST( COALESCE( gpexp.start_date, gpexp.expiry_date, p.post_date ) AS DATE ) <= CAST( ? AS DATE )', $sMax );						
			}
			
			if ( $sBetween ) {
				$oQuery->where( '
					CAST( ? AS DATE ) BETWEEN 
					CAST( COALESCE( gpexp.start_date, gpexp.expiry_date, p.post_date ) AS DATE ) AND 
					CAST( COALESCE( gpexp.expiry_date, gpexp.start_date, p.post_date ) AS DATE )				
				', $sBetween );
			}
			
			
			////// order by clause
			
			if ( $bOrderByStart || $bOrderByExpiry ) {
				
				$sQhOrder = '';
				
				if ( !$sOrder = $aParams[ 'order' ] ) {
					$sOrder = 'DESC';
				}
				
				if ( $bOrderByStart ) {
					
					$sQhOrder = 'COALESCE(gpexp.start_date, p.post_date)';
				
				} else {
					
					// 'expiry_date' == $aParams[ 'orderby'
					$sQhOrder = 'COALESCE(gpexp.expiry_date, p.post_date)';				
				}
				
				$oQuery->order( $sQhOrder, $sOrder );
			}
			
			
		}
		
		
		return $oQuery;
	
	}
	
	
}



