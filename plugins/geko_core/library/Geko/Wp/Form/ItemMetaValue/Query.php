<?php

// listing
class Geko_Wp_Form_ItemMetaValue_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$sSecIdExp = 'IF( 2 = fimv.context_id, fimv.fmsec_id, fi.fmsec_id )';
		
		$oQuery
			
			->field( $sSecIdExp, 'section_id' )
			->field( 'fs.form_id' )
			
			->joinLeft( $wpdb->geko_form_item, 'fi' )
				->on( 'fi.fmitm_id = fimv.fmitm_id' )

			->joinLeft( $wpdb->geko_form_section, 'fs' )
				->on( 'fs.fmsec_id = ' . $sSecIdExp )
				
		;
		
		// fmsec id
		if ( $aParams[ 'fmsec_id' ] ) {
			$oQuery->where( $sSecIdExp . ' = ?', $aParams[ 'fmsec_id' ] );
		}

		// form id
		if ( $aParams[ 'form_id' ] ) {
			$oQuery->where( 'fs.form_id = ?', $aParams[ 'form_id' ] );
		}

		// context id
		if ( $aParams[ 'context_id' ] ) {
			$oQuery->where( 'fimv.context_id = ?', $aParams[ 'context_id' ] );
		}
		
		// language id
		if ( $aParams[ 'lang_id' ] ) {
			$oQuery->where( 'fimv.lang_id = ?', $aParams[ 'lang_id' ] );
		}
		
		return $oQuery;
		
	}
	
	
}


