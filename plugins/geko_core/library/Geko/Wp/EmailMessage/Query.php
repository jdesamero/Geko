<?php

// listing
class Geko_Wp_EmailMessage_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function getDefaultParams() {
		return $this->setWpQueryVars( 'paged', 'posts_per_page' );
	}
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 't.strg_id' )
			->joinLeft( '##pfx##geko_emsg_transport', 't' )
				->on( 't.trpt_id = e.trpt_id' )			
		;
		
		// emsg id
		if ( $aParams[ 'geko_emsg_id' ] ) {
			$oQuery->where( 'e.emsg_id = ?', $aParams[ 'geko_emsg_id' ] );
		}

		// emsg slug
		if ( $aParams[ 'geko_emsg_slug' ] ) {
			$oQuery->where( 'e.slug = ?', $aParams[ 'geko_emsg_slug' ] );
		}
		
		//
		if ( $aHeaderMatch = $aParams[ 'header' ] ) {
			
			$i = 0;
			
			foreach ( $aHeaderMatch as $sKey => $sValue ) {
				
				$sPfx = sprintf( 'h_%d', $i );
				
				$oQuery
					->joinLeft( '##pfx##geko_emsg_header', $sPfx )
						->on( sprintf( '%s.emsg_id = e.emsg_id', $sPfx ) )
						->on( sprintf( '%s.name = ?', $sPfx ), $sKey )
					->where( sprintf( '%s.val = ?', $sPfx ), $sValue )
				;
				
				$i++;
			}
		}
		
		
		return $oQuery;
	}
	
	
}


