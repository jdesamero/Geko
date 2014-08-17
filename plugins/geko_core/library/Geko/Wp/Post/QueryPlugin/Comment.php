<?php

//
class Geko_Wp_Post_QueryPlugin_Comment extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$bCommentLatest = $aParams[ 'add_comment_latest_date_field' ];
		$bLatestActivity = $aParams[ 'add_latest_activity_date_field' ];
		$sOrderByField = $aParams[ 'orderby' ];
		
		
		//// fields
		
		if (
			( $bCommentLatest ) || 
			( $bLatestActivity ) || 
			in_array( $sOrderByField, array( 'comment_latest_date', 'latest_activity_date' ) )
		) {
			
			$oCommentQuery = new Geko_Sql_Select();
			$oCommentQuery
				->field( 'MAX( c.comment_date )' )
				->from( $wpdb->comments, 'c' )
				->where( 'c.comment_approved = 1' )
				->where( 'c.comment_post_ID = p.ID' )
			;
			
			$oQuery->field( sprintf( '@cld := (%s)', strval( $oCommentQuery ) ), 'comment_latest_date' );
			
			if ( $bLatestActivity || ( 'latest_activity_date' == $sOrderByField ) ) {
				$oQuery->field( 'COALESCE( @cld, p.post_date )', 'latest_activity_date' );
			}
			
		}
		
		
		//// order
		
		if ( in_array( $sOrderByField, array( 'comment_count', 'comment_latest_date', 'latest_activity_date' ) ) ) {
			
			$sOrder = $this->getSortOrder( $aParams[ 'order' ] );
			
			$oQuery->order( $sOrderByField, $sOrder );
		}
		
		
		return $oQuery;
	
	}
	
	
}



