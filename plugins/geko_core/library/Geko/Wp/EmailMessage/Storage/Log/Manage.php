<?php

//
class Geko_Wp_EmailMessage_Storage_Log_Manage extends Geko_Wp_Log_Manage
{
	
	protected $_sTableSuffix = 'email_storage';
	protected $_bUseMetaTable = TRUE;
	
	
	
	// implement hook method
	public function modifyPrimaryTable( $oSqlTable ) {
	
		$oSqlTable
			->fieldLongText( 'message_body' )
			->fieldBigInt( 'strg_id', array( 'unsgnd' ) )
			->fieldBigInt( 'unique_id', array( 'unsgnd' ) )
			->fieldDateTime( 'date_parsed' )
		;
		
		return $oSqlTable;
	}
	
	
	// implement hook method
	// types: "header", "custom"
	public function modifyMetaTable( $oSqlTable ) {
		
		// add a type_id field to the meta table
		$oSqlTable
			->fieldSmallInt( 'type_id', array( 'unsgnd' ) )
		;
		
		return $oSqlTable;
	}
	
		
	
	// NOTE!!! Possible collision!!!
	public function modifyParams( $aParams ) {
		
		if ( $sDate = $aParams[ 'meta' ][ 'header' ][ 'date' ] ) {
			$aParams[ 'date_parsed' ] = Geko_Db_Mysql::getTimestamp( strtotime( $sDate ) );
		}
		
		// re-format meta values
		$aMetaFmt = array();
		$aMeta = $aParams[ 'meta' ];
		
		foreach ( $aMeta as $sType => $aValues ) {
			$iTypeId = Geko_Wp_Options_MetaKey::getId( $sType );
			foreach ( $aValues as $sKey => $sValue ) {
				$aMetaFmt[ $sKey ] = array( $iTypeId, $sValue );
			}
		}
		
		// re-assign
		$aParams[ 'meta' ] = $aMetaFmt;
		
		return parent::modifyParams( $aParams );
	}
	

	// implement hook method
	public function getInsertMetaData( $aParams ) {
		
		// separate meta value into type and value
		$aValue = $aParams[ 'meta_value' ];
		$aParams[ 'type_id' ] = $aValue[ 0 ];
		$aParams[ 'meta_value' ] = $aValue[ 1 ];
		
		return parent::getInsertMetaData( $aParams );
	}
	
	
	
}

