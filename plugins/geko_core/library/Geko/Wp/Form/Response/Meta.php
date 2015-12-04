<?php

// abstract
class Geko_Wp_Form_Response_Meta extends Geko_Wp_Options_Meta
{


	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_response_meta', 'frm' )
			->fieldBigInt( 'fmrsp_meta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'fmrsp_id', array( 'unsgnd', 'notnull' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'meta_value' )
			->indexKey( 'fmrsp_mkey_id', array( 'point_id', 'mkey_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
	
	
	
	// save the data
	public function save() {
	
	}
	

}

