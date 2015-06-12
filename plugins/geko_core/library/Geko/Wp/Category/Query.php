<?php

class Geko_Wp_Category_Query extends Geko_Wp_Entity_Query
{
	
	// implement by sub-class to populate entities/total rows
	public function init() {
		
		// if using non-standard parameters, use our own query
		if ( $this->_aParams[ 'use_non_native_query' ] ) {
			return parent::init();
		}
		
		
		// fix for Wordpress as of version of 4.2.2
		// sanitize passed parameters
		
		$aGetCatParamKeys = array(
			'type', 'child_of', 'parent', 'orderby', 'order', 'hide_empty', 'hierarchical', 
			'exclude', 'include', 'number', 'taxonomy', 'pad_counts'
		);
		
		$aSanitizedParams = array();
		foreach ( $aGetCatParamKeys as $sKey ) {
			if ( isset( $this->_aParams[ $sKey ] ) ) {
				$aSanitizedParams[ $sKey ] = $this->_aParams[ $sKey ];
			}
		}
		
		
		$this->_aEntities = ( 0 === $this->_aParams[ 'number' ] ) ?
			array() : 
			array_values( get_categories( $aSanitizedParams ) )
		;
		
		$this->_iTotalRows = count( $this->_aEntities );
		
		return $this;
	}
	
	//
	public function getDefaultParams() {
		
		// hacky!!!
		$aDefaultParams = parent::getDefaultParams();
		
		if ( $aDefaultParams[ 'category_name' ] ) {
			
			$aDefaultParams[ 'include' ] = Geko_Wp_Category::get_ID(
				$aDefaultParams[ 'category_name' ]
			);
			
			unset( $aDefaultParams[ 'category_name' ] );
		}
		
		return $aDefaultParams;
	}
	
	
	// only kicks in when "use_non_native_query" is set to TRUE
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$sTaxonomy = ( $aParams[ 'taxonomy' ] ) ? $aParams[ 'taxonomy' ] : 'category' ;
		
		
		$oQuery
			
			->field( 't.term_id' )
			->field( 't.name' )
			->field( 't.slug' )
			->field( 't.term_group' )
			
			->field( 'tx.term_taxonomy_id' )
			->field( 'tx.taxonomy' )
			->field( 'tx.description' )
			->field( 'tx.parent' )
			->field( 'tx.count' )
			
			->from( '##pfx##terms', 't' )
			->joinLeft( '##pfx##term_taxonomy', 'tx' )
				->on( 'tx.term_id = t.term_id' )
			
			->where( 'tx.taxonomy = ?', $sTaxonomy )
			
		;
		
		
		////
		
		if ( !$aParams[ 'not_distinct' ] ) {
			$oQuery->distinct( TRUE );
		}
		
		if ( !$aParams[ 'dont_join_term_relationships' ] ) {
			
			$oQuery
				->joinLeft( '##pfx##term_relationships', 'tr' )
					->on( 'tr.term_taxonomy_id = tx.term_taxonomy_id' )			
			;
			
		}
		
		
		
		////
		
		if ( $iParentId = $aParams[ 'parent' ] ) {
			$oQuery->where( 'tx.parent = ?', $iParentId );
		}
		
		
		// "ps" stands for "parent slug"
		if ( $sParentSlug = $aParams[ 'parent_slug' ] ) {
			
			$oQuery
				
				->joinLeft( '##pfx##term_taxonomy', 'pstx' )
					->on( 'pstx.term_taxonomy_id = tx.parent' )
					
				->joinLeft( '##pfx##terms', 'pst' )
					->on( 'pst.term_id = pstx.term_id' )
				
				->where( 'pst.slug = ?', $sParentSlug )
				
			;
			
		}
		
		
		// "wp" stands for "with posts"
		if ( $iCatWithPosts = $aParams[ 'has_posts_in_cat' ] ) {
			
			
			$oHasPostsQuery = new Geko_Sql_Select();
			$oHasPostsQuery
				
				->field( 'wptx.term_id' )
				->field( 'COUNT( wptr.object_id )', 'num_posts' )
				
				->from( '##pfx##term_relationships', 'wptr' )
				
				->joinLeft( '##pfx##term_taxonomy', 'wptx' )
					->on( 'wptx.term_taxonomy_id = wptr.term_taxonomy_id' )

				->joinLeft( '##pfx##term_relationships', 'wp2tr' )
					->on( 'wp2tr.object_id = wptr.object_id' )

				->joinLeft( '##pfx##term_taxonomy', 'wp2tx' )
					->on( 'wp2tx.term_taxonomy_id = wp2tr.term_taxonomy_id' )
					
				
				->where( 'wp2tx.term_id = ?', $iCatWithPosts )
				
				->where( 'wptx.taxonomy = ?', $sTaxonomy )
				->where( 'wp2tx.taxonomy = ?', $sTaxonomy )
				
				->group( 'wptx.term_id' )
			;
			
			$oQuery
				
				// ->field( 'wpt.num_posts' )
				
				->joinLeft( $oHasPostsQuery, 'wpt' )
					->on( 'wpt.term_id = t.term_id' )
				
				->where( 'wpt.num_posts > 0' )
			;
			
			
		}
		
		return $oQuery;
		
	}
	
	
	/* /
	// ???
	public function getSqlQuery()
	{
		// no idea what the original query is
	}
	/* */
	
	
	//
	public function getAsFlatNested() {
		
		$aCatGroup = array();
		foreach ( $this as $oCat ) {
			$aCatGroup[ $oCat->getParent() ][] = $oCat;
		}
		
		return $this->sortAsFlatNested( $aCatGroup );
	}
	
	// helper for $this->getAsFlatNested()
	public function sortAsFlatNested( $aCatGroup, $iParent = 0, $iLevel = 0 ) {
		$aRet = array();
		$aList = $aCatGroup[ $iParent ];
		if ( count( $aList ) > 0 ) {
			foreach ( $aList as $oCat ) {
				$aRet[] = $oCat->setData( 'level', $iLevel );
				$iCatId = $oCat->getId();
				if ( is_array( $aCatGroup[ $iCatId ] ) ) {
					$aRet = array_merge( $aRet, $this->sortAsFlatNested( $aCatGroup, $iCatId, $iLevel + 1 ) );
				}
			}
		}
		return $aRet;	
	}
	
}




