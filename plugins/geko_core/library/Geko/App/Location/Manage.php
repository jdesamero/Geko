<?php
/*
 * "geko_core/library/Geko/App/Location/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Location_Manage extends Geko_App_Entity_Manage
{
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##location', 'l' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldInt( 'prop_type_tx_value', array( 'unsgnd', 'notnull' ) )
			->fieldVarChar( 'number', array( 'size' => 32, 'uniquecheck' ) )
			->fieldLongText( 'street', array( 'uniquecheck' ) )
			->fieldLongText( 'city', array( 'uniquecheck' ) )
			->fieldBigInt( 'province_id', array( 'uniquecheck' ) )
			->fieldVarChar( 'postal_code', array( 'size' => 16, 'uniquecheck' ) )
			->fieldFloat( 'latitude' )
			->fieldFloat( 'longitude' )
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


