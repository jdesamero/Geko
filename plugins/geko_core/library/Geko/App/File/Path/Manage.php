<?php
/*
 * "geko_core/library/Geko/App/File/Path/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_File_Path_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##file_path', 'p' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'path' )
			->fieldLongText( 'title' )
			->fieldLongText( 'description' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		
		
		// add paths from bootstrap, if given
		if ( is_array( $aUploadPaths = Geko_App::get( 'upload_paths' ) ) ) {
			
			Geko_Once::run(
				sprintf( '%s:upload_paths', __CLASS__ ),
				function ( $aUploadPaths ) {
					Geko_App_File_Path::add( $aUploadPaths );
				},
				array( $aUploadPaths )
			);
			
		}
		
		
		return $this;
		
	}
	
	
	
}



