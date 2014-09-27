<?php

// listing
class Geko_Wp_EmailMessage_Storage_Query extends Geko_Wp_Entity_Query
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

		// strg_id
		if ( $aParams[ 'geko_emsg_strg_id' ] ) {
			$oQuery->where( 't.strg_id * ($)', $aParams[ 'geko_emsg_strg_id' ] );
		}
		
		// strg slug
		if ( $aParams[ 'geko_emsg_strg_slug' ] ) {
			$oQuery->where( 't.slug = ?', $aParams[ 'geko_emsg_strg_slug' ] );
		}
		
		return $oQuery;
	}
	
	
}


