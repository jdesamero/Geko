<?php

// listing
class Geko_Wp_Form_ItemValue_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	private $bJoinItem = FALSE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		// fmitm_id (form item id)
		if ( $aParams[ 'fmitm_id' ] ) {
			$oQuery->where( 'fiv.fmitm_id * ($)', $aParams[ 'fmitm_id' ] );
		}
		
		// is default
		if ( isset( $aParams[ 'is_default' ] ) ) {
			$oQuery->where( 'fiv.is_default = ?', $aParams[ 'is_default' ] );
		}
		
		// fmitmval_idx (form item value index)
		if ( $aParams[ 'fmitmval_idx' ] ) {
			$oQuery->where( 'fiv.fmitmval_idx * ($)', $aParams[ 'fmitmval_idx' ] );
		}
		
		// fmsec_id (form section id)
		if ( $aParams[ 'fmsec_id' ] ) {
			$this->joinItem( $oQuery );
			$oQuery
				->where( 'fi.fmsec_id * ($)', $aParams[ 'fmsec_id' ] )
			;
		}
		
		// form id
		if ( $aParams[ 'form_id' ] ) {
			if ( !$aParams[ 'fmsec_id' ] ) $this->joinItem( $oQuery );
			$oQuery
				->joinLeft( '##pfx##geko_form_section', 'fs' )
					->on( 'fs.fmsec_id = fi.fmsec_id' )
				->where( 'fs.form_id = ?', $aParams[ 'form_id' ] )
			;
		}
		
		//
		if ( $aParams[ 'add_form_item_slug' ] ) {
			$this->joinItem( $oQuery );
			$oQuery->field( 'fi.slug', 'form_item_slug' );
		}
		
		//
		if ( $aParams[ 'add_item_type' ] ) {
			$this->joinItem( $oQuery );
			$oQuery
				->field( 'ft.slug', 'item_type' )
				->joinLeft( '##pfx##geko_form_item_type', 'ft' )
					->on( 'ft.fmitmtyp_id = fi.fmitmtyp_id' )
			;			
		}
		
		return $oQuery;
		
	}
	
	//// helpers
	
	// do only once
	private function joinItem( $oQuery ) {
		
		if ( !$this->bJoinItem ) {
			
			$oQuery
				->field( 'fi.fmsec_id' )
				->joinLeft( '##pfx##geko_form_item', 'fi' )
					->on( 'fi.fmitm_id = fiv.fmitm_id' )
			;
			
			$this->bJoinItem = TRUE;
		}
		
	}
	
}


