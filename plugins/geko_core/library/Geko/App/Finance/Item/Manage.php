<?php
/*
 * "geko_core/library/Geko/App/Finance/Item/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Finance_Item_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##finance_item', 'i' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'account_id', array( 'unsgnd' ) )
			->fieldBigInt( 'entry_id', array( 'unsgnd' ) )
			->fieldLongText( 'details' )
			->fieldLongText( 'long_details' )
			->fieldLongText( 'external_reference' )
			->fieldBool( 'debit_credit' )
			->fieldDecimal( 'amount', array( 'size' => '10,2' ) )
			->fieldSmallInt( 'sort_order' )
			->fieldDateTime( 'date_due_debit' )
			->fieldDateTime( 'date_due_credit' )
		;
		
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		
		return $this;
		
	}
	
	
	
	
}


