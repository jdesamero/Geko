<?php

// listing
class Geko_Wp_Language_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// lang id
		$iLangId = Geko_String::coalesce( $aParams[ 'geko_lang_id' ], $aParams[ 'lang_id' ] );
		if ( $iLangId ) {
			$oQuery->where( 'l.lang_id = ?', $iLangId );
		}
		
		// lang code/slug
		$sLangCode = Geko_String::coalesce( $aParams[ 'geko_lang_code' ], $aParams[ 'lang_code' ] );
		if ( $sLangCode ) {
			$oQuery->where( 'l.code = ?', $sLangCode );
		}
		
		// apply default sorting
		if ( !isset( $aParams[ 'orderby' ] ) ) {
			$oQuery
				->order( 'l.is_default', 'DESC' )
				->order( 'l.title' )
			;
		}
		
		return $oQuery;
	}
	
	
}


