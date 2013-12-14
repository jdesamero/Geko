<?php

// abstract
class Geko_Wp_Form_Response_Meta extends Geko_Wp_Options_Meta
{


	//// init
	
	//
	public function affix() {
		
		global $wpdb;
		
		$sTableName = 'geko_form_response_meta';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'frm' )
			->fieldBigInt( 'fmrsp_meta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'fmrsp_id', array( 'unsgnd', 'notnull' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'meta_value' )
			->indexKey( 'fmrsp_mkey_id', array( 'point_id', 'mkey_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
	}
	
	
	// create table
	public function install() {
		$this->createTable( $this->getPrimaryTable() );
		return $this;
	}
	
	
	// save the data
	public function save() {
	
	}
	

}

