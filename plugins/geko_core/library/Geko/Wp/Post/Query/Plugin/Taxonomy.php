<?php

//
class Geko_Wp_Post_Query_Plugin_Taxonomy extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		if ( is_array( $aTx = $aParams[ 'taxonomy' ] ) ) {

			$aTypes = $aTx[ 'types' ];
			$aTerms = $aTx[ 'terms' ];
			
			if ( !is_array( $aTypes ) ) $aTypes = array( $aTypes );
			if ( !is_array( $aTerms ) ) $aTerms = array( $aTerms );
			
			$aTypes = array_map( 'sanitize_title', $aTypes );
			$aTerms = array_map( 'sanitize_title', $aTerms );
			
			
			$oTxQuery = new Geko_Sql_Select();
			$oTxQuery
				
				->field( 'tr.object_id', 'object_id' )
				->from( '##pfx##terms', 't' )
				
				->joinInner( '##pfx##term_taxonomy', 'tt' )
					->on( 'tt.term_id = t.term_id' )
				
				->joinInner( '##pfx##term_relationships', 'tr' )
					->on( 'tr.term_taxonomy_id = tt.term_taxonomy_id' )
				
				->where( 'tt.taxonomy * (?)', $aTypes )
				->where( 't.slug * (?)', $aTerms )
			;
			
			
			$oQuery->where( 'p.ID IN (?)', $oTxQuery );
			
		}
		
		
		return $oQuery;
	
	}
	
	
}



