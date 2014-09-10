<?php

// abstract
class Geko_Wp_Point_Meta extends Geko_Wp_Options_Meta
{
	
	protected $_sParentFieldName = 'point_id';
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		Geko_Wp_Options_MetaKey::init();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_point_meta', 'pm' )
			->fieldBigInt( 'pmeta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( $this->_sParentFieldName, array( 'unsgnd', 'notnull' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'meta_value' )
			->indexKey( 'point_mkey_id', array( 'point_id', 'mkey_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
		
	
	
	// create table
	public function install() {
		
		parent::install();
		
		Geko_Wp_Options_MetaKey::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	// save the data
	public function save() {
	
	}
	
	
	
}


