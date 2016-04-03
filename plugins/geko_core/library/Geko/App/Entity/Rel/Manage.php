<?php
/*
 * "geko_core/library/Geko/App/Entity/Rel/Manage.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Entity_Rel_Manage extends Geko_App_Entity_Manage
{
	
	
	protected $_sEntityIdVarName = 'id';
	
	
	//
	public function start() {
		
		parent::start();
		
		$oDb = Geko_App::get( 'db' );
		
		$oSqlTable = new Geko_Sql_Table( $oDb );
		$oSqlTable
			->create( '##pfx##entity_rel', 'er' )
			->fieldBigInt( 'entity_rel_id', array( 'unsgnd', 'notnull' ) )
			->fieldSmallInt( 'entity_rel_type_id', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'subject_rel_id', array( 'unsgnd', 'notnull' ) )
			->fieldSmallInt( 'subject_role_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'temp_comment' )
			->indexUnq( 'unique_rel', array( 'entity_rel_id', 'entity_rel_type_id', 'subject_rel_id', 'subject_role_id' ) )
		;
		
		
		Geko_Once::run(
			$oSqlTable->getTableName(),
			array( $this, 'addTable' ),
			array( $oSqlTable, TRUE, TRUE )
		);
		
		
		return $this;
		
	}
	
	
	
	
}


