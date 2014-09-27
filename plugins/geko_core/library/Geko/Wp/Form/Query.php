<?php

// listing
class Geko_Wp_Form_Query extends Geko_Wp_Entity_Query
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
		
		// emsg id
		if ( $aParams[ 'geko_form_id' ] ) {
			$oQuery->where( 'f.form_id = ?', $aParams[ 'geko_form_id' ] );
		}

		// emsg slug
		if ( $aParams[ 'geko_form_slug' ] ) {
			$oQuery->where( 'f.slug = ?', $aParams[ 'geko_form_slug' ] );
		}
		
		
		return $oQuery;
	}
	
	
}