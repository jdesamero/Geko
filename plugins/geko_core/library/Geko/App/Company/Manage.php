<?php
/*
 * "geko_core/library/Geko/App/Company/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Company_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##company', 'co' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'name', array( 'size' => 256 ) )
			->fieldVarChar( 'website', array( 'size' => 256 ) )
			->fieldVarChar( 'email', array( 'size' => 256 ) )
			->fieldVarChar( 'phone', array( 'size' => 32 ) )
			->fieldVarChar( 'fax', array( 'size' => 32 ) )
			->fieldSmallInt( 'lang_id' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		return $this;
		
	}
	
	
	
	
}


