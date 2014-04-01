<?php

//
class Geko_Wp_Location_Meta extends Geko_Wp_Options_Meta
{

	
	
	
	//// init
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		
		$sTable = 'geko_location_meta';
		Geko_Wp_Db::addPrefix( $sTable );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTable, 'am' )
			->fieldBigInt( 'ameta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'address_id', array( 'unsgnd', 'key' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'meta_value' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		$sTable2 = 'geko_location_meta_members';
		Geko_Wp_Db::addPrefix( $sTable2 );
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( $wpdb->$sTable2, 'amm' )
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


