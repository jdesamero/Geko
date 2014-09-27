<?php

// listing
class Geko_Wp_Form_MetaData_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->field( 'fit.slug', 'item_type' )
			->joinLeft( '##pfx##geko_form_item_type', 'fit' )
				->on( 'fit.fmitmtyp_id = fmd.fmitmtyp_id' )
		;
		
		// form id
		if ( $aParams[ 'form_id' ] ) {
			$oQuery->where( 'fmd.form_id = ?', $aParams[ 'form_id' ] );
		}
		
		return $oQuery;
		
	}
	
	
}


