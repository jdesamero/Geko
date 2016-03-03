<?php
/*
 * "geko_core/library/Geko/App/Entity/Type/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Type_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##entity_type', 'et' )
			->fieldSmallInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'name' )
			->fieldVarChar( 'slug', array( 'size' => 255, 'unq' ) )
			->fieldLongText( 'description' )
		;
		
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		
		return $this;
		
	}
	
	
	
	
}


