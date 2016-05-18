<?php
/*
 * "geko_core/library/Geko/App/Finance/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##finance', 'f' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldSmallInt( 'type_id', array( 'unsgnd' ) )
			->fieldVarChar( 'slug', array( 'size' => 255 ) )
			->fieldLongText( 'name' )
			->fieldBool( 'has_debit_due_date' )
			->fieldBool( 'has_credit_due_date' )
			->fieldSmallInt( 'sort_order' )
			->fieldBigInt( 'owner_id', array( 'unsgnd' ) )
		;
		
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		
		return $this;
		
	}
	
	
	
	
}


