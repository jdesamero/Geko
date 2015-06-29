<?php

// listing
class Geko_Wp_User_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	// implement by sub-class to process $aParams
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		
		if ( $aParams[ 'kwsearch' ] && !$aParams[ 'kwsearch_override_default_fields' ] ) {
			
			$aParams[ 'kwsearch_fields' ] = array_diff( Geko_Array::merge(
				$aParams[ 'kwsearch_fields' ],
				array( 'dum1.meta_value', 'dum2.meta_value', 'dum3.meta_value' )
			), array( '' ) );
		}
		
		return $aParams;
	}
	
	
	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page', 'geko_role_slug' );
	}
	
	
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			
			// primary table now handled by Geko_Wp_User_Manage
			
			
			// secondary
			
			->field( 'CAST( r.meta_value AS UNSIGNED )', 'role_id' )
			->field( 'gr.title', 'role_title' )
			->field( 'gr.slug', 'role_slug' )
			->field( 'gr.slug', 'role' )						// synonymous with "role_slug"
			
			->joinLeft( '##pfx##usermeta', 'r' )
				->on( 'r.user_id = u.ID' )
				->on( 'r.meta_key = ?', '_geko_role_id' )
			->joinLeft( '##pfx##geko_roles', 'gr' )
				->on( 'gr.role_id = CAST( r.meta_value AS UNSIGNED )' )
			
			// tertiary
			
			->fieldKvp( 'dum1.meta_value', 'first_name' )
			->fieldKvp( 'dum2.meta_value', 'last_name' )
			->fieldKvp( 'dum3.meta_value', 'description' )
			->fieldKvp( 'dum4.meta_value', 'geko_activation_key' )
			->fieldKvp( 'dum5.meta_value', 'geko_password_reset_key' )
			->fieldKvp( 'dum6.meta_value', 'geko_has_logged_in' )
			
			->joinLeftKvp( '##pfx##usermeta', 'dum*' )
				->on( 'dum*.user_id = u.ID' )
				->on( 'dum*.meta_key = ?', '*' )
			
		;
		
		
		// user id
		
		if ( isset( $aParams[ 'geko_user_id' ] ) ) {
			$oQuery->where( 'u.ID * ($)', $aParams[ 'geko_user_id' ] );
		}
		
		if ( isset( $aParams[ 'user_login' ] ) ) {
			$oQuery->where( 'u.user_login = ?', $aParams[ 'user_login' ] );
		}
		
		// HACK!!!
		if (
			( isset( $aParams[ 'exclude' ] ) ) && 
			( is_array( $aParams[ 'exclude' ] ) ) && 
			( count( $aParams[ 'exclude' ] ) > 0 )
		) {
			$sExcludeWhere = sprintf( "u.ID NOT IN ('%s')", implode( "', '", $aParams[ 'exclude' ] ) );
			$oQuery->where( $sExcludeWhere );
		}
		
		
		//
		if ( 'id_in_order' == $aParams[ 'orderby' ] ) {
			
			if ( is_array( $aParams[ 'geko_user_id' ] ) ) {
				
				$aIds = array_values( $aParams[ 'geko_user_id' ] );
				
				$sField = '';
				
				foreach ( $aIds as $i => $iId ) {
					$sField .= sprintf( ' WHEN %d THEN %d ', $iId, $i );
				}
				
				$sField = sprintf( 'CAST( ( CASE u.ID %s ELSE %d END ) AS UNSIGNED )', $sField, $i + 1 );
				
				$oQuery->field( $sField, $aParams[ 'orderby' ] );
				
			} else {
				
				// must remove this since the sort field was never defined
				$oQuery->unsetOrder( $aParams[ 'orderby' ] );
				
			}
			
		}
		
		// user slug
		if ( isset( $aParams[ 'geko_user_slug' ] ) ) {
			$oQuery->where( 'u.user_nicename = ?', $aParams[ 'geko_user_slug' ] );
		}
		
		// login_email_nicename
		if ( isset( $aParams[ 'login_email_nicename' ] ) ) {
			$oQuery->where( '( u.user_login = ? ) OR ( u.user_email = ? ) OR ( u.user_nicename = ? )', $aParams[ 'login_email_nicename' ] );
		}
		
		if ( isset( $aParams[ 'geko_activation_key' ] ) ) {
			$oQuery->where( 'dum4.meta_value = ?', $aParams[ 'geko_activation_key' ] );
		}
		
		if ( isset( $aParams[ 'geko_password_reset_key' ] ) ) {
			$oQuery->where( 'dum5.meta_value = ?', $aParams[ 'geko_password_reset_key' ] );
		}
		
		//// role filters

		// role filter
		if ( isset( $aParams[ 'geko_role_id' ] ) ) {
			$oQuery->where( 'CAST( r.meta_value AS UNSIGNED ) = ?', $aParams[ 'geko_role_id' ] );
		}
		
		// role filter
		if ( isset( $aParams[ 'geko_role_slug' ] ) && ( 'all' != $aParams[ 'geko_role_slug' ] ) ) {
			
			$oSubQuery = new Geko_Sql_Select();
			$oSubQuery
				->field( 'role_id' )
				->from( '##pfx##geko_roles' )
				->where( 'slug * (?)', $aParams[ 'geko_role_slug' ] )
			;
			
			$oQuery->where( 'r.meta_value IN (?)', $oSubQuery );
			
		}
		
		
		//// advanced search filters
		
		//
		if ( isset( $aParams[ 'first_name' ] ) ) {
			$oQuery->where( 'fn.meta_value LIKE ?', sprintf( '%%%s%%', $aParams[ 'first_name' ] ) );
		}
		
		//
		if ( isset( $aParams[ 'last_name' ] ) ) {
			$oQuery->where( 'ln.meta_value LIKE ?', sprintf( '%%%s%%', $aParams[ 'last_name' ] ) );
		}
		
		//
		if ( isset( $aParams[ 'email' ] ) ) {
			$oQuery->where( 'u.user_email = ?', $aParams[ 'email' ] );
		}
		
		
		
		//// number of posts by user
		
		if (
			( $aParams[ 'show_num_posts' ] ) || 
			( $aParams[ 'exclude_zero_posts' ] )
		) {
			
			$oSubQuery2 = new Geko_Sql_Select();
			$oSubQuery2
				->field( 'COUNT(*)' )
				->from( '##pfx##posts', 'p' )
				->where( 'p.post_author = u.ID' )
				->where( "p.post_type = 'post'" )
			;
			
			$oQuery->field( $oSubQuery2, 'num_posts' );
			
			if ( $aParams[ 'show_with_zero_posts' ] ) {
				$oQuery->having( 'num_posts > 0' );
			}
			
		}
		
		//// latest post date
		
		if ( $aParams[ 'show_latest_post_date' ] ) {
			
			$oSubQuery3 = new Geko_Sql_Select();
			$oSubQuery3
				->field( 'MAX(p.post_date)' )
				->from( '##pfx##posts', 'p' )
				->where( 'p.post_author = u.ID' )
				->where( "p.post_type = 'post'" )
			;			
			
			$oQuery->field( $oSubQuery3, 'latest_post_date' );
			
		}
		
		
		
		// apply default sorting
		if ( !isset( $aParams[ 'orderby' ] ) ) {		
			$oQuery->order( 'last_name' );
		}
		
		
		return $oQuery;
	}
	
	
}


