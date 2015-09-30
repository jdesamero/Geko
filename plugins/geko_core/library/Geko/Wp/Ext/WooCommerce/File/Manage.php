<?php

//
class Geko_Wp_Ext_WooCommerce_File_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_sSubject = 'WooCommerce Files';
	
	protected $_bDisableAttachPage = TRUE;
	
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_wc_files', 'f' )
			->fieldBigInt( 'file_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'file_key' )
			->fieldLongText( 'title' )
			->fieldLongText( 'path' )
			->fieldBigInt( 'prod_id', array( 'unsgnd', 'notnull' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
}


