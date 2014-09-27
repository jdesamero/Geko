<?php

// listing
class Geko_Wp_Language_Member_Query extends Geko_Wp_Entity_Query
{	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'lgm.lgroup_id', 'lang_group_id' )
			->field( 'lgm.obj_id' )
			->field( 'lgm.lang_id' )
			->from( '##pfx##geko_lang_group_members', 'lgm' )
			
			->field( 'lg.type_id' )
			->joinLeft( '##pfx##geko_lang_groups', 'lg' )
				->on( 'lg.lgroup_id = lgm.lgroup_id' )
			
			->field( 'l.code', 'lang_code' )
			->field( 'l.title', 'lang_title' )
			->field( 'l.is_default', 'lang_is_default' )
			->joinLeft( '##pfx##geko_languages', 'l' )
				->on( 'l.lang_id = lgm.lang_id' )
			
		;
		
		
		
		//// filtering params
		
		// obj id
		if ( $aParams[ 'obj_id' ] ) {
			$oQuery->where( 'lgm.obj_id = ?', $aParams[ 'obj_id' ] );
		}
		
		
		
		// type
		if ( $aParams[ 'type' ] ) {
			$aParams[ 'type_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'type' ] );
		}
		
		// type id
		if ( $aParams[ 'type_id' ] ) {
			$oQuery->where( 'lg.type_id = ?', $aParams[ 'type_id' ] );
		}
		
		
		
		// lang (code)
		if ( $aParams[ 'lang' ] ) {
			$oQuery->where( 'l.code = ?', $aParams[ 'lang' ] );
		}
		
		// lang id
		if ( $aParams[ 'lang_id' ] ) {
			$oQuery->where( 'lgm.lang_id = ?', $aParams[ 'lang_id' ] );
		}
		
		
		
		// lang group id
		if ( $aParams[ 'lang_group_id' ] ) {
			$oQuery->where( 'lgm.lgroup_id = ?', $aParams[ 'lang_group_id' ] );
		}
		
		
		
		// sibling id
		if (
			( $aParams[ 'sibling_id' ] ) && 
			( $aParams[ 'type_id' ] || $aParams[ 'type' ] )
		) {
			$oSubQuery = new Geko_Sql_Select();
			$oSubQuery
				->field( 'lgm1.lgroup_id' )
				->from( '##pfx##geko_lang_group_members', 'lgm1' )
				
				->joinLeft( '##pfx##geko_lang_groups', 'lg1' )
					->on( 'lg1.lgroup_id = lgm1.lgroup_id' )
				
				->where( 'lgm1.obj_id = ?', $aParams[ 'sibling_id' ] )
				->where( 'lg1.type_id = ?', $aParams[ 'type_id' ] )				
			;
			
			$oQuery->where( 'lgm.lgroup_id = ?', $oSubQuery );
		}
		
		
		// add siblings field
		if ( $aParams[ 'add_siblings_field' ] ) {
			
			$oSubQuery = new Geko_Sql_Select();
			$oSubQuery
				->field( "GROUP_CONCAT( CONCAT( m1.lang_id, ':', m1.obj_id ) )" )
				->from( '##pfx##geko_lang_group_members', 'm1' )
				->where( 'm1.lgroup_id = lgm.lgroup_id' )
				->where( 'm1.obj_id != lgm.obj_id' )
			;
			
			$oQuery->field( array( 'CAST( ? AS CHAR )', $oSubQuery ), 'siblings' );
		}
		
		
		// more filtering
		if ( is_array( $aParams[ 'filter' ] ) ) {
			
			$aWhere = array();
			
			foreach ( $aParams[ 'filter' ] as $aFilter ) {
				
				$aSubWhere = array();
				
				if ( $aFilter[ 'type' ] ) {
					$aFilter[ 'type_id' ] = Geko_Wp_Options_MetaKey::getId( $aFilter[ 'type' ] );
				}
				
				if ( $aFilter[ 'type_id' ] ) {
					$aSubWhere[] = $oQuery->evaluateExpressionPair( 'lg.type_id = ?', $aFilter[ 'type_id' ] );
				}
				
				if ( $aFilter[ 'obj_id' ] ) {
					$aSubWhere[] = $oQuery->evaluateExpressionPair( 'lgm.obj_id * (?)', $aFilter[ 'obj_id' ] );
				}
				
				$aWhere[] = sprintf( ' (%s) ', implode( ') AND (', $aSubWhere ) );
			}
			
			$oQuery->where( sprintf( ' (%s) ', implode( ') OR (', $aWhere ) ) );
		}
		
		
		return $oQuery;
	}
	
	
}


