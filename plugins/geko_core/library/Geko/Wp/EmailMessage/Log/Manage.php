<?php

//
class Geko_Wp_EmailMessage_Log_Manage extends Geko_Wp_Log_Manage
{
	
	protected $_sTableSuffix = 'email_message';
	protected $_bUseMetaTable = TRUE;
	
	
	
	// implement hook method
	public function modifyPrimaryTable( $oSqlTable ) {
		
		$oSqlTable
			->fieldSmallInt( 'dlvstat_id', array( 'unsgnd' ) )
			->fieldVarChar( 'email_address', array( 'size' => 256 ) )
			->fieldBigInt( 'emsg_id', array( 'unsgnd' ) )
			->fieldVarChar( 'emsg_slug', array( 'size' => 256 ) )
			->fieldDateTime( 'scheduled_delivery_date' )
			->fieldDateTime( 'actual_delivery_date' )
			->fieldBigInt( 'batch_id', array( 'unsgnd' ) )
		;
		
		return $oSqlTable;
	}
	
	//
	public function modifyParams( $aParams ) {
		
		if ( $aParams[ 'delivery_status' ] ) {
			$aParams[ 'dlvstat_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'delivery_status' ] );
		}
		
		return parent::modifyParams( $aParams );
		
	}
	
	
	
}

