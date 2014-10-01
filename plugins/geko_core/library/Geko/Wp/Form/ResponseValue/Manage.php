<?php

// listing
class Geko_Wp_Form_ResponseValue_Manage extends Geko_Wp_Options_Manage
{

	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'fmrv_id';
	
	protected $_sSubject = 'Form Response Values';
	protected $_sDescription = 'Response values to a form.';
	protected $_sType = 'fmrv';
	
	protected $_bHasDisplayMode = FALSE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_response_value', 'frv' )
			->fieldBigInt( 'fmrv_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'fmrsp_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldVarChar( 'slug', array( 'size' => 256, 'key' ) )
			->fieldLongText( 'value' )
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
	
	
	// disable this
	public function getStoredSubOptions( $aRet, $oMainEnt, $oPlugin = NULL ) {
		return $aRet;
	}
	
	
	
	//// crud methods
	
	// disable this
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) { }
	
	
	
}

