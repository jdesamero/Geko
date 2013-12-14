<?php

//
class Geko_Wp_Archive_Query extends Geko_Wp_Entity_Query
{
	
	
	// implement by sub-class to process $aParams
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		
		if ( !$aParams[ 'orderby' ] ) $aParams[ 'orderby' ] = 'period';
		if ( !$aParams[ 'order' ] ) $aParams[ 'order' ] = 'DESC';
		
		return $aParams;
	}

	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// main query
		$oQuery
			->field( 'COUNT(*)', 'archive_count' )
			->field( "DATE_FORMAT( p.post_date, '%Y' )", 'year' )
			->from( $wpdb->posts, 'p' )
			->group( 'period' )
		;
		
		// periods
		
		$sPeriod = $aParams[ 'type' ];
		$sPeriod = ( $sPeriod ) ? $sPeriod : 'monthly';
		
		if ( 'yearly' == $sPeriod ) {
			// yearly
			$sPeriodFormat = '%Y';
		} else {
			// monthly (default)
			$sPeriodFormat = '%Y-%m';
			$oQuery->field( "DATE_FORMAT( p.post_date, '%m' )", 'month' );
		}
		
		$oQuery->field( "DATE_FORMAT( p.post_date, '" . $sPeriodFormat . "' )", 'period' );
		
		// post type
		$mPostType = $aParams[ 'post_type' ];
		$mPostType = ( $mPostType ) ? ( ( 'any' == $mPostType ) ? '' : $mPostType ) : 'post';
		
		if ( $mPostType ) {
			$oQuery->where( 'p.post_type * (?)', $mPostType );
		}
		
		// post status
		$mPostStatus = $aParams[ 'post_status' ];
		$mPostStatus = ( $mPostStatus ) ? ( ( 'any' == $mPostStatus ) ? '' : $mPostStatus ) : 'publish';
		
		if ( $mPostStatus ) {
			$oQuery->where( 'p.post_status * (?)', $mPostStatus );
		}
		
		// category
		if ( $mCat = $aParams[ 'cat' ] ) {
			
			$oQuery
				->joinLeft( $wpdb->term_relationships, 'tr' )
					->on( 'tr.object_id = p.ID' )
				->joinLeft( $wpdb->term_taxonomy, 'tx' )
					->on( 'tx.term_taxonomy_id = tr.term_taxonomy_id' )
				->where( 'tx.term_id * ($)', $mCat )
			;
			
			/* /
			// TO DO: allow slugs
			$oQuery
				->joinLeft( $wpdb->terms, 't' )
					->on( 't.term_id = tx.term_id' )
			;
			/* */
			
		}
		
		return $oQuery;
	}
	
	
}


