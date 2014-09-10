<?php

//
class Geko_Wp_Custom_Taxonomy_Manage extends Geko_Wp_Options_Manage
{


	//// init
	
	//
	public function add() {
		
		parent::add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_custom_taxonomy', 'tx' )
			->fieldBigInt( 'tx_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'title' )
			->fieldVarChar( 'slug', array( 'size' => 256, 'unq' ) )
			->fieldLongText( 'description' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	
}


