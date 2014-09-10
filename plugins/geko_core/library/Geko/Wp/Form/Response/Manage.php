<?php

// listing
class Geko_Wp_Form_Response_Manage extends Geko_Wp_Options_Manage
{


	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_response', 'fr' )
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
		
		parent::install();
		
		$this->createTableOnce();
		
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

