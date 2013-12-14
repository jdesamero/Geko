<?php

//// !!! Re-factoring in progress...

//
class Geko_Wp_Generic_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	/*
	 *	add_meta[<Meta Key>]
	 *	add_meta[<Meta Key>][order]=< ASC | DESC >
	 *	add_meta[<Meta Key>][order_append]= (bool)
	 *	add_meta[<Meta Key>][match]=< expression >
	 *	add_meta[<Meta Key>][match_op]=< LIKE | RLIKE | EQ >
	 *	add_meta[<Meta Key>][empty]=< 0 | 1 >
	 *	add_meta[<Meta Key>][cast_type]
	 */
	
	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page' );
	}
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		// generic_id
		if ( isset( $aParams[ 'generic_id' ] ) ) {
			$oQuery->where( 'g.generic_id = ?', $aParams[ 'generic_id' ] );
		}
		
		// generic_type, gentype_id
		if ( isset( $aParams[ 'generic_type' ] ) ) {
			$oQuery->where( 'g.gentype_id = ?', Geko_Wp_Options_MetaKey::getId( $aParams[ 'generic_type' ] ) );
		}
		
		// object_id
		if ( isset( $aParams[ 'object_id' ] ) ) {
			$oQuery->where( 'g.object_id = ?', $aParams[ 'object_id' ] );
		}
		
		// object_type, objtype_id
		if ( isset( $aParams[ 'object_type' ] ) ) {
			$oQuery->where( 'g.objtype_id = ?', Geko_Wp_Options_MetaKey::getId( $aParams[ 'object_type' ] ) );
		}
		
		
		$aPrefixes = array();
		$aRawFields = array();
		
		if ( is_array( $aParams[ 'add_meta' ] ) ) {
			
			$i = 0;
			foreach ( $aParams[ 'add_meta' ] as $sMetaKey => $mParams ) {
				
				$aMetaParams = ( is_array( $mParams ) ) ? $mParams : array();
				
				$sPrefix = '_g' . $i;
				$aPrefixes[ $sMetaKey ] = $sPrefix;
				
				$sCastType = '';
				
				$aCastTypes = array( 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED' );
				
				// check for meta key specific cast stype
				if ( isset( $aMetaParams[ 'cast_type' ] ) ) {
					$sCastType = strtoupper( $aMetaParams[ 'cast_type' ] );
					if ( !in_array( $sCastType, $aCastTypes ) ) $sCastType = '';	// bad type given
					if ( $sCastType && $aMetaParams[ 'cast_params' ] ) $sCastType .= $aMetaParams[ 'cast_params' ];
				}
				
				if ( $sCastType ) {
					$sField = " CAST( $sPrefix.meta_value AS $sCastType ) ";
				} else {
					$sField = " $sPrefix.meta_value ";		
				}
								
				$oQuery
					->field( $sField, $sMetaKey )
					->joinLeft( $wpdb->geko_generic_meta, $sPrefix )
						->on( $sPrefix . '.generic_id = g.generic_id' )
						->on( $sPrefix . '.mkey_id = ?', Geko_Wp_Options_MetaKey::getId( $sMetaKey ) )
				;
				
				$aRawFields[ $sMetaKey ] = $sField;
				
				if ( isset( $aMetaParams[ 'searchable' ] ) ) {
					$aParams[ 'kwsearch_fields' ][] = " $sPrefix.meta_value ";
				}
				
				if ( isset( $aMetaParams[ 'order' ] ) ) {
					$sOrder = ( 'DESC' == strtoupper( $aMetaParams[ 'order' ] ) ) ? 'DESC' : 'ASC';
					$oQuery->order( $sMetaKey, $sOrder );
				}
				
				$i++;
			}
			
		}
		
		
		
		//// attach prefixes to query so that it can be used by sub-queries
		$oQuery->setProperty( 'prefixes', $aPrefixes );
		$oQuery->setProperty( 'raw_fields', $aRawFields );
		
		
		return $oQuery;
	}
	
	
}


