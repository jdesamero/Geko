<?php
/*
 * "geko_core/library/Geko/App/Meta/Key/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Meta_Key_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##meta_key', 'mk' )
			->fieldInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'label' )
			->fieldVarChar( 'slug', array( 'size' => 255 ) )
			->fieldSmallInt( 'rel_type_id' )
			->fieldSmallInt( 'lang_id' )
			->fieldBool( 'is_multiple' )
		;
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		return $this;
		
	}
	
	
	
	
}


