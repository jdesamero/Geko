<?php

//
class Geko_Wp_Ext_WooCommerce_File_Query extends Geko_Wp_Entity_Query
{
	
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		//
		if ( $mId = $aParams[ 'file_id' ] ) {
			$oQuery->where( 'f.file_id * ($)', $mId );
		}
		
		//
		if ( $mSlug = $aParams[ 'file_key' ] ) {
			$oQuery->where( 'f.file_key * (?)', $mSlug );
		}
		
		//
		if ( $mProdId = $aParams[ 'prod_id' ] ) {
			$oQuery->where( 'f.prod_id * ($)', $mProdId );
		}
		
		
		return $oQuery;
	}
	
	
	




}

