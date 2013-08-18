<?php

// listing
class Geko_Wp_Form_MetaValue_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// fmmd_id (form meta data id)
		if ( $aParams[ 'fmmd_id' ] ) {
			$oQuery->where( 'fmv.fmmd_id * ($)', $aParams[ 'fmmd_id' ] );
		}
		
		// fmmv_idx (form meta value index)
		if ( $aParams[ 'fmmv_idx' ] ) {
			$oQuery->where( 'fmv.fmmv_idx * ($)', $aParams[ 'fmmv_idx' ] );
		}
		
		// form id
		if ( $aParams[ 'form_id' ] ) {
			$oQuery
				->joinLeft( $wpdb->geko_form_meta_data, 'fmd' )
					->on( 'fmd.fmmd_id = fmv.fmmd_id' )
				->where( 'fmd.form_id = ?', $aParams[ 'form_id' ] )
			;			
		}
		
		return $oQuery;
		
	}
	
	
}


