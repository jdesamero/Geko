<?php

//
class Geko_Wp_Location_Meta extends Geko_Wp_Options_Meta
{

	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_location_meta', 'am' )
			->fieldBigInt( 'ameta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'address_id', array( 'unsgnd', 'key' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'meta_value' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( '##pfx##geko_location_meta_members', 'amm' )
			->fieldBigInt( 'ameta_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'member_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'member_value' )
			->fieldLongText( 'flags' )
		;
		
		$this->addTable( $oSqlTable2, FALSE );
		
		
		
		return $this;
	}
	
	// create table
	public function install() {
		
		global $wpdb;
		
		parent::install();
		
		$this->createTableOnce();
		$this->createTableOnce( $wpdb->geko_location_meta_members );
		
		return $this;
	}
	
	
	
	
}


