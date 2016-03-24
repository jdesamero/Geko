<?php
/*
 * "geko_core/library/Geko/App/Entity/Role/Meta/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Role_Meta_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##entity_role_meta', 'erm' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldSmallInt( 'entity_role_id', array( 'unsgnd', 'notnull' ) )
			->fieldInt( 'meta_key_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'value' )
			->fieldLongText( 'sub_key' )
			->fieldInt( 'sub_key_order', array( 'unsgnd', 'notnull' ) )
		;
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		return $this;
		
	}
	
	
	
	
}


