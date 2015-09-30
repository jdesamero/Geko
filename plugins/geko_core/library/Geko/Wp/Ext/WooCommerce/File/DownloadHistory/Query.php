<?php

//
class Geko_Wp_Ext_WooCommerce_File_DownloadHistory_Query extends Geko_Wp_Entity_Query
{
	
	protected $_bUseManageQuery = TRUE;
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		$oQuery
			
			->field( 'f.file_key' )
			->field( 'f.title' )
			->field( 'f.path' )
			->field( 'f.prod_id' )
			
			->joinLeft( '##pfx##geko_wc_files', 'f' )
				->on( 'f.file_id = h.file_id' )
			
		;

		
		
		//
		if ( $mId = $aParams[ 'dlh_id' ] ) {
			$oQuery->where( 'h.dlh_id * ($)', $mId );
		}

		//
		if ( $mUserId = $aParams[ 'user_id' ] ) {
			$oQuery->where( 'h.user_id * ($)', $mUserId );
		}
		
		//
		if ( $aParams[ 'unique_files' ] ) {
			
			$oQuery
				->field( 'COUNT(*)', 'download_count' )
				->group( 'f.path' )
			;
		
		}
		
		//
		if ( $aParams[ 'unique_prod_files' ] ) {
			
			$oQuery
				->field( 'COUNT(*)', 'download_count' )
				->group( 'f.file_key' )
				->group( 'f.prod_id' )
			;
		
		}
		
		return $oQuery;
	}
	
	
	
}

