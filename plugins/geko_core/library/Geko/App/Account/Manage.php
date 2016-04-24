<?php
/*
 * "geko_core/library/Geko/App/Account/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Account_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##account', 'ac' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'login', array( 'size' => 255 ) )
			->fieldChar( 'password', array( 'size' => 32 ) )
			->fieldBigInt( 'rel_id' )
			->fieldSmallInt( 'rel_type_id' )
			->fieldSmallInt( 'lang_id' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
			->indexUnq( 'unique_rel', array( 'rel_id', 'rel_type_id' ) )
			->indexUnq( 'login', array( 'login' ) )
		;
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		return $this;
		
	}
	
	
	
	
}


