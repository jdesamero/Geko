<?php

class Geko_Wp_Tag_Query extends Geko_Wp_Entity_Query
{
	
	// implement by sub-class to populate entities/total rows
	public function init() {
		
		// if using non-standard parameters, use our own query
		if ( $this->_aParams[ 'use_non_native_query' ] ) {
			return parent::init();
		}
		
		// defer to get_tags() function
		$this->setRawEntities( get_tags( $this->_aParams ) );
		
		
		return $this;
		
	}
	
	
	// only kicks in when "use_non_native_query" is set to TRUE
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			
			->distinct( TRUE )
			
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
			->joinLeft( '##pfx##term_relationships', 'tr' )
				->on( 'tr.term_taxonomy_id = tx.term_taxonomy_id' )
			
			->where( 'tx.taxonomy = ?', 'post_tag' )
			
		;
		
		// get tags for posts that belong to a particular category
		if (
			( $sCatId = $aParams[ 'cat_id' ] ) || 
			( $sCatSlug = $aParams[ 'cat_slug' ] )
		) {
			
			$oQuery
				->joinLeft( '##pfx##term_relationships', 'tr2' )
					->on( 'tr2.object_id = tr.object_id' )
				->joinLeft( '##pfx##term_taxonomy', 'tx2' )
					->on( 'tx2.term_taxonomy_id = tr2.term_taxonomy_id' )
				->joinLeft( '##pfx##terms', 't2' )
					->on( 't2.term_id = tx2.term_id' )
				->where( 'tx2.taxonomy = ?', 'category' )
			;
			
			if ( $sCatId ) $oQuery->where( 't2.term_id = ?', $sCatId );			
			if ( $sCatSlug ) $oQuery->where( 't2.slug = ?', $sCatSlug );
			
		}
		
		return $oQuery;
		
	}
	
	
	
}




