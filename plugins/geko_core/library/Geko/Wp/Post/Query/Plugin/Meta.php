<?php

//
class Geko_Wp_Post_Query_Plugin_Meta extends Geko_Entity_Query_Plugin
{
	
	
	//
	public function modifyQuery( $oQuery, $aParams, $oEntityQuery ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams, $oEntityQuery );
		
		
		$aAddMeta = $aParams[ 'add_meta' ];
		
		$bHasSearchTerms = FALSE;
		$aSearchTerms = $aParams[ 'search_terms' ];
		
		if ( is_array( $aSearchTerms ) && ( count( $aSearchTerms ) > 0 ) ) {
			$bHasSearchTerms = TRUE;
		}
		
		if ( is_array( $aAddMeta ) ) {
			
			$aCastTypes = array( 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' );
			$aMatchOps = array( '=', 'LIKE', 'RLIKE' );
			
			$i = 0;
			
			foreach ( $aAddMeta as $sMetaKey => $aMeta ) {
				
				$i++;
				
				$sJk = sprintf( '_pm%d', $i );			// join key
				
				
				$sMetaFieldName = $aParams[ 'meta_field_name' ];
				if ( !$sMetaFieldName ) $sMetaFieldName = $sMetaKey;

				
				//// fields
								
				// check for meta key specific cast stype
				if ( isset( $aMeta[ 'cast_type' ] ) ) {
					$sCastType = strtoupper( $aMeta[ 'cast_type' ] );
					if ( !in_array( $sCastType, $aCastTypes ) ) $sCastType = '';		// bad type given
				}
				
				if ( $sCastType ) {
					$oQuery->field( sprintf( 'CAST( %s.meta_value AS %s )', $sJk, $sCastType ), $sMetaFieldName );
				} else {
					$oQuery->field( sprintf( '%s.meta_value', $sJk ), $sMetaFieldName );
				}
				
				
				
				//// join
				
				$oQuery
					->joinLeft( '##pfx##postmeta', $sJk )
						->on( sprintf( '%s.post_id = p.ID', $sJk ) )
						->on( sprintf( '%s.meta_key = ?', $sJk ), $sMetaKey )
				;
				
				
				
				//// where
				
				if ( isset( $aMeta[ 'empty' ] ) ) {
					if ( $aMeta['empty'] ) {
						$oQuery->where( sprintf( '( %s.meta_value IS NULL ) OR ( 0 = %s.meta_value ) OR ( '' = %s.meta_value )', $sJk, $sJk, $sJk ) );
					} else {
						$oQuery->where( sprintf( '( %s.meta_value IS NOT NULL ) OR ( 0 != %s.meta_value ) OR ( '' != %s.meta_value )', $sJk, $sJk, $sJk ) );
					}
				}
				
				if ( isset( $aMeta[ 'match' ] ) ) {
					
					$sMatchOp = '';
					
					// check for meta key specific match op
					if ( isset( $aMeta[ 'match_op' ] ) ) {
						$sMatchOp = strtoupper( $aMeta[ 'match_op' ] );
						if ( !in_array( $sMatchOp, $aMatchOps ) ) $sMatchOp = '';		// bad op given
					}
					
					$sMatchOp = ( $sMatchOp ) ? $sMatchOp : '=' ;
					$sMatchValue = $aMeta[ 'match' ];
					
					$oQuery->where( sprintf( '? %s %s.meta_value', $sMatchOp, $sJk ), $aMeta[ 'match' ] );
				}
				
				if ( $aMeta[ 'searchable' ] && $bHasSearchTerms ) {
					
					foreach ( $aSearchTerms as $sTerm ) {
						// TO DO: Tricky stuff...
					}
				}
				
				
				//// order
				
				if ( isset( $aMeta[ 'order' ] ) ) {
					
					$sOrder = $this->getSortOrder( $aMeta[ 'order' ] );
					
					$oQuery->order( $sMetaFieldName, $sOrder );
				}
				
			}
			
		}
		
		
		return $oQuery;
	
	}
	
	
}



