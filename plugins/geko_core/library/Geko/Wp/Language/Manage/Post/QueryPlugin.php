<?php

class Geko_Wp_Language_Manage_Post_QueryPlugin extends Geko_Entity_Query_Plugin
{
	
	//
	public function modifyQuery( $oQuery, $aParams, $oEntityQuery ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams, $oEntityQuery );
		
		if (
			( $sLang = $aParams[ 'lang' ] ) ||
			( $bAddLangFlds = intval( $aParams[ 'add_lang_fields' ] ) )
		) {
			
			$oLangQuery = new Geko_Sql_Select();
			$oLangQuery
				
				->field( 'lgm.obj_id', 'obj_id' )
				->field( 'l.code', 'lang_code' )
				->field( 'l.title', 'lang_title' )
				
				->from( '##pfx##geko_lang_group_members', 'lgm' )
				
				->joinLeft( '##pfx##geko_lang_groups', 'lg' )
					->on( 'lg.lgroup_id = lgm.lgroup_id' )
				
				->joinLeft( '##pfx##geko_languages', 'l' )
					->on( 'l.lang_id = lgm.lang_id' )
				
				->where( 'lg.type_id = ?', Geko_Wp_Options_MetaKey::getId( 'post' ) )
			;
			
			$oQuery
				->joinLeft( $oLangQuery, 'lang' )
					->on( 'lang.obj_id = p.ID' )
			;
			
			// lang fields
			if ( $bAddLangFlds ) {
				$oQuery
					->field( 'lang.lang_code', 'lang_code' )
					->field( 'lang.lang_title', 'lang_title' )
				;			
			}
			
			// current lang
			if ( $sLang ) {
				
				$oIsDefQuery = new Geko_Sql_Select();
				$oIsDefQuery
					->field( 'lndf.is_default', 'is_default' )
					->field( '##pfx##geko_languages', 'lndf' )
					->where( 'lndf.code = ?', $sLang )
				;
				
				$oQuery->where( '( lang.lang_code = :lang ) OR ( :query )', array(
					'lang' => $sLang,
					'query' => $oIsDefQuery
				) );
			}
			
		}
		
		
		return $oQuery;
	
	}
	
	
}



