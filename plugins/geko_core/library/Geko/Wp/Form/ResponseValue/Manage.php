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
	protected $_bDisableAttachPage = TRUE;
	protected $_bDisableGetStoredSubOptions = TRUE;
	protected $_bDisableUpdateRelatedEntities = TRUE;
	
	
	
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
	
	
	
	
}

