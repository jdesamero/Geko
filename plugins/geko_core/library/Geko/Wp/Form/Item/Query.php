<?php

// listing
class Geko_Wp_Form_Item_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 'fit.slug', 'item_type' )
			->joinLeft( $wpdb->geko_form_item_type, 'fit' )
				->on( 'fit.fmitmtyp_id = fi.fmitmtyp_id' )
		;
		
		// form item id
		if ( $aParams[ 'fmitm_id' ] ) {
			$oQuery->where( 'fi.fmitm_id * ($)', $aParams[ 'fmitm_id' ] );
		}
		
		// fmsec id
		if ( $aParams[ 'fmsec_id' ] ) {
			$oQuery->where( 'fi.fmsec_id * ($)', $aParams[ 'fmsec_id' ] );
		}
		
		// form id
		if ( $aParams[ 'form_id' ] ) {
			$oQuery
				->joinLeft( $wpdb->geko_form_section, 'fs' )
					->on( 'fs.fmsec_id = fi.fmsec_id' )
				->where( 'fs.form_id = ?', $aParams[ 'form_id' ] )
			;
		}
		
		return $oQuery;
		
	}
	
	
}


