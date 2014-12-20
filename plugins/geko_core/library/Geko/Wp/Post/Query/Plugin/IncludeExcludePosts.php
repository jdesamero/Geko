<?php

//
class Geko_Wp_Post_Query_Plugin_IncludeExcludePosts extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$sOrderBy = $aParams[ 'orderby' ];
		
		
		//// where clause
		
		$mIncludeIds = $aParams[ 'include_ids' ];
		if ( $sIncludeIds = $this->getImplodedIds( $mIncludeIds ) ) {
			
			$oQuery->where( 'p.ID IN (?)', $sIncludeIds );

			//// order
			
			if ( 'include_ids_order' == $sOrderBy ) {
				
				$aIncludeIds = $this->getExplodedIds( $mIncludeIds );
				
				$sFieldName = 'include_ids_order';
				
				$sField = ' CASE p.ID ';
				foreach ( $aIncludeIds as $i => $iId ) {
					$sField .= sprintf( ' WHEN %d THEN %d ', $iId, $i );
				}
				$sField .= ' ELSE NULL END ';
				
				$sOrder = $this->getSortOrder( $aParams[ 'order' ] );
				
				$oQuery
					->field( $sField, $sFieldName )
					->order( $sFieldName, $sOrder )
				;
			}
		}
		
		if ( $sExcludeIds = $this->getImplodedIds( $aParams[ 'exclude_ids' ] ) ) {
			$oQuery->where( 'p.ID NOT IN (?)', $sExcludeIds );
		}
		
		
		return $oQuery;
	
	}
	
	
}



