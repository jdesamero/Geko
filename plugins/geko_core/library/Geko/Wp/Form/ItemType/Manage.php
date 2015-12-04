<?php

//
class Geko_Wp_Form_ItemType_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'fmitmtyp_id';
	
	protected $_sSubject = 'Form Item Type';
	protected $_sDescription = 'Types of widgets in the form.';
	protected $_sType = 'fmitmtyp';
	
	protected $_bExtraForms = TRUE;
	protected $_bHasDisplayMode = FALSE;
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_item_type', 'fit' )
			->fieldSmallInt( 'fmitmtyp_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'slug', array( 'size' => 256, 'key' ) )
			->fieldLongText( 'name' )
			->fieldBool( 'has_multiple_values' )
			->fieldBool( 'has_multiple_response' )
			->fieldBool( 'has_choice_subs' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		// first-time setup, call only once
		if ( !$this->regWasInitialized() ) {
			
			$sTableName = $oSqlTable->getTableName();
			
			
			$oDb = Geko_Wp::get( 'db' );
			
			if ( 0 === $oDb->getTableNumRows( $sTableName ) ) {
				
				$oDb->insertMulti( $sTableName, array(
					array(
						'slug' => 'text',
						'name' => 'Text',
						'has_multiple_values' => 0,
						'has_multiple_response' => 0,
						'has_choice_subs' => 0
					),
					array(
						'slug' => 'textarea',
						'name' => 'Textarea',
						'has_multiple_values' => 0,
						'has_multiple_response' => 0,
						'has_choice_subs' => 0
					),
					array(
						'slug' => 'radio',
						'name' => 'Radio Buttons',
						'has_multiple_values' => 1,
						'has_multiple_response' => 0,
						'has_choice_subs' => 1
					),
					array(
						'slug' => 'checkbox',
						'name' => 'Checkbox',
						'has_multiple_values' => 0,
						'has_multiple_response' => 0,
						'has_choice_subs' => 0
					),
					array(
						'slug' => 'checkbox_multi',
						'name' => 'Checkbox (Multiple)',
						'has_multiple_values' => 1,
						'has_multiple_response' => 1,
						'has_choice_subs' => 0
					),
					array(
						'slug' => 'select',
						'name' => 'Select',
						'has_multiple_values' => 1,
						'has_multiple_response' => 0,
						'has_choice_subs' => 1
					),
					array(
						'slug' => 'select_multi',
						'name' => 'Select (Multiple)',
						'has_multiple_values' => 1,
						'has_multiple_response' => 1,
						'has_choice_subs' => 0
					)
				) );
			}
			
			$this->regSetInitialized();
		}
		
		
		return $this;
		
	}
	
	
	
	
	//
	public function getDefaultSubOptions( $aRet ) {
		$aRet[ $this->_sType ] = Geko_Wp_Form::getItemTypes()->getRawEntities();
		return $aRet;
	}

	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	//// front-end display methods
	
	//
	public function extraForms( $oEntity ) {
		?>
		<div id="dialog_field_templates">
			<div class="fields">
				<div class="text">
					<label for="" class="main"></label>
					<input type="text" name="" id="" value="" class="text" />
				</div>
				<div class="textarea">
					<label for="" class="main"></label>
					<textarea type="text" name="" id=""></textarea>
				</div>
				<div class="radio">
					<label for="" class="main"></label>
					<div class="multiple">
						<div class="row">
							<input type="radio" name="" id="" value="" class="sub radio" /> 
							<label for="" class="sub"></label>
						</div>
					</div>
				</div>
				<div class="checkbox">
					<label for="" class="main"></label>
					<input type="checkbox" name="" id="" value="" />
				</div>
				<div class="checkbox_multi">
					<label for="" class="main"></label>
					<div class="multiple">
						<div class="row">
							<input type="checkbox" name="" id="" value="" class="sub checkbox" /> 
							<label for="" class="sub"></label>
						</div>
					</div>
				</div>
				<div class="select">
					<label for="" class="main"></label>
					<select name="" id=""></select>
				</div>
				<div class="select_multi">
					<label for="" class="main"></label>
					<select name="" id="" multiple="multiple"></select>
				</div>
			</div>
			<div class="values">
				<div class="text">
					<label for="" class="main">Default Value</label>
					<input type="text" class="label text" />
				</div>
				<div class="textarea">
					<label for="" class="main">Default Value</label>
					<textarea type="text" name="" id=""></textarea>
				</div>
				<div class="radio"></div>
				<div class="checkbox">
					<label for="" class="main">Checked</label>
					<input type="checkbox" value="1" class="is_default" name="is_default" />
				</div>
				<div class="checkbox_multi"></div>
				<div class="select"></div>
				<div class="select_multi"></div>
			</div>
			<div class="validation">
				<div class="all">
					<div>
						<label for="vld_not_required">Not Required</label>
						<input type="checkbox" class="vld" name="vld_not_required" id="vld_not_required" value="" />
					</div>
				</div>
				<div class="text">
					<div>
						<label for="vld_text_type">Type</label>
						<input type="radio" class="vld" name="vld_text_type" id="vld_text_type_email" value="vld_text_type_email" /> <label for="vld_text_type_email" class="radio">Email</label><br />
						<input type="radio" class="vld" name="vld_text_type" id="vld_text_type_url" value="vld_text_type_url" /> <label for="vld_text_type_url" class="radio">URL</label><br />
						<input type="radio" class="vld" name="vld_text_type" id="vld_text_type_postal_code" value="vld_text_type_postal_code" /> <label for="vld_text_type_postal_code" class="radio">Postal Code (Canadian)</label><br />
						<button id="unchk_text_type" class="unchk">Uncheck</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	
	
	//// crud methods

	// HACKish, disable this
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) { }
	
	
	
}



