<?php
/*
 * "geko_core/library/Geko/App/File/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_File_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##file', 'f' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'path_id' )
			->fieldVarChar( 'name', array( 'size' => 255 ) )
			->fieldVarChar( 'orig_name', array( 'size' => 255 ) )
			->fieldVarChar( 'extension', array( 'size' => 8 ) )
			->fieldLongText( 'title' )
			->fieldLongText( 'description' )
			->fieldVarChar( 'mime_type', array( 'size' => 128 ) )
			->fieldBool( 'has_dimensions' )
			->fieldBigInt( 'size' )
			->fieldBigInt( 'width' )
			->fieldBigInt( 'height' )
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


