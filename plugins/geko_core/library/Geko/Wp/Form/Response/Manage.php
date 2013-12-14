<?php

// listing
class Geko_Wp_Form_Response_Manage extends Geko_Wp_Options_Manage
{


	//// init
	
	//
	public function affix() {
		
		global $wpdb;
		
		$sTable = 'geko_form_response';
		Geko_Wp_Db::addPrefix( $sTable );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTable, 'fr' )
			->fieldBigInt( 'fmrsp_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'form_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldBool( 'completed' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	
	// create table
	public function install() {
		$this->createTable( $this->getPrimaryTable() );
		return $this;
	}
	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	
	//// crud methods

	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
	}
	
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
		
	}
	
	
	
}

