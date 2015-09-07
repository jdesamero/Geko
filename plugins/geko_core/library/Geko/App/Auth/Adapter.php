<?php

//
class Geko_App_Auth_Adapter extends Zend_Auth_Adapter_DbTable
{

	
	//
	public function __construct( $oDb, $sTableName = NULL, $sIdentityColumn = NULL, $sCredentialColumn = NULL, $sCredentialTreatment = NULL ) {
		
		if ( !$sTableName ) {
			
			// create the default table

			$oAuthTable = new Geko_Sql_Table( $oDb );
			$oAuthTable
				->create( '##pfx##users', 'u' )
				->fieldBigInt( 'user_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
				->fieldVarChar( 'login', array( 'size' => 255, 'unq' ) )
				->fieldChar( 'pass', array( 'size' => 32 ) )
				->fieldDateTime( 'date_created' )
				->fieldDateTime( 'date_modified' )
			;
			
			$oDb->tableCreateIfNotExists( $oAuthTable );
			
			$sTableName = $oAuthTable->getTableName();
		}
		
		if ( !$sIdentityColumn ) {
			$sIdentityColumn = 'login';
		}
		
		if ( !$sCredentialColumn ) {
			$sCredentialColumn = 'pass';
		}
		
		if ( !$sCredentialTreatment ) {
			$sCredentialTreatment = 'MD5(?)';
		}
				
		parent::__construct( $oDb->getDb(), $sTableName, $sIdentityColumn, $sCredentialColumn, $sCredentialTreatment );
		
	}
	
	
	
}


