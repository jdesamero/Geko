<?php

//
class Geko_App_Auth_Adapter extends Zend_Auth_Adapter_DbTable
{

	
	//
	public function __construct( $oDb ) {
		
		parent::__construct( $oDb->getDb() );
		
		
		$oAuthTable = new Geko_Sql_Table( $oDb );
		$oAuthTable
			->create( '##pfx##users', 'u' )
			->fieldBigInt( 'user_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'login', array( 'size' => 256, 'unq' ) )
			->fieldChar( 'pass', array( 'size' => 32 ) )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$oDb->tableCreateIfNotExists( $oAuthTable );
		
		$this
			->setTableName( $oAuthTable->getTableName() )
			->setIdentityColumn( 'login' )
			->setCredentialColumn( 'pass' )
			->setCredentialTreatment( 'MD5(?)' )
		;
		
	}
	

}


