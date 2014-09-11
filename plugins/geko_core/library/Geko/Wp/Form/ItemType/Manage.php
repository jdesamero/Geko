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
		
		
		return $this;
		
	}
	
	
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
		// populate with default values if table is empty
		
		$sTable = 'geko_form_item_type';
		
		Geko_Once::run( sprintf( '%s::populate' ), array( $this, 'populateTable' ), array( $sTable ) );
		
		return $this;
	}
	
	
	//
	public function populateTable( $sTable ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( 0 === $oDb->getTableNumRows( $sTable ) ) {

			Geko_Wp_Db::insertMulti( $sTable, array(
				array( 'slug:%s' => 'text', 'name:%s' => 'Text', 'has_multiple_values:%d' => 0, 'has_multiple_response:%d' => 0, 'has_choice_subs:%d' => 0 ),
				array( 'slug:%s' => 'textarea', 'name:%s' => 'Textarea', 'has_multiple_values:%d' => 0, 'has_multiple_response:%d' => 0, 'has_choice_subs:%d' => 0 ),
				array( 'slug:%s' => 'radio', 'name:%s' => 'Radio Buttons', 'has_multiple_values:%d' => 1, 'has_multiple_response:%d' => 0, 'has_choice_subs:%d' => 1 ),
				array( 'slug:%s' => 'checkbox', 'name:%s' => 'Checkbox', 'has_multiple_values:%d' => 0, 'has_multiple_response:%d' => 0, 'has_choice_subs:%d' => 0 ),
				array( 'slug:%s' => 'checkbox_multi', 'name:%s' => 'Checkbox (Multiple)', 'has_multiple_values:%d' => 1, 'has_multiple_response:%d' => 1, 'has_choice_subs:%d' => 0 ),
				array( 'slug:%s' => 'select', 'name:%s' => 'Select', 'has_multiple_values:%d' => 1, 'has_multiple_response:%d' => 0, 'has_choice_subs:%d' => 1 ),
				array( 'slug:%s' => 'select_multi', 'name:%s' => 'Select (Multiple)', 'has_multiple_values:%d' => 1, 'has_multiple_response:%d' => 1, 'has_choice_subs:%d' => 0 )
			) );
		}
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
					<input type="text" name="" id="" value="" class="widget text" />
				</div>
				<div class="textarea">
					<label for="" class="main"></label>
					<textarea type="text" name="" id="" class="widget"></textarea>
				</div>
				<div class="radio">
					<label for="" class="main"></label>
					<div class="multiple widget">
						<div class="row">
							<input type="radio" name="" id="" value="" class="sub radio" /> 
							<label for="" class="sub"></label>
						</div>
					</div>
				</div>
				<div class="checkbox">
					<label for="" class="main"></label>
					<input type="checkbox" name="" id="" value="" class="widget" />
				</div>
				<div class="checkbox_multi">
					<label for="" class="main"></label>
					<div class="multiple widget">
						<div class="row">
							<input type="checkbox" name="" id="" value="" class="sub checkbox" /> 
							<label for="" class="sub"></label>
						</div>
					</div>
				</div>
				<div class="select">
					<label for="" class="main"></label>
					<select name="" id="" class="widget"></select>
				</div>
				<div class="select_multi">
					<label for="" class="main"></label>
					<select name="" id="" multiple="multiple" class="widget"></select>
				</div>
			</div>
			<div class="values">
				<div class="default">
					<label for="" class="main">Default Value</label>
					<div id="default_elem">
						<input type="text" class="label widget text" />
					</div>
				</div>
				<div class="checkbox">
					<label for="" class="main">Checked</label>
					<div id="default_elem">
						<input type="checkbox" value="1" class="is_default" name="is_default" />
					</div>
				</div>
				<div class="has_multiple_values">
					<label for="" class="main">Values</label>
					<table>
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Title</th>
								<th>Code</th>
								<th>Default</th>
							</tr>
						</thead>
						<tbody>
							<tr class="multi">
								<td><a href="#" class="geko-form-remove-item" title="Remove Option"><span class="geko-form-icon geko-form-icon-remove"></span></a></td>
								<td><input type="text" class="label" /></td>
								<td><input type="text" class="slug" /></td>
								<td><input type="radio" class="is_default" name="is_default" value="1" /></td>
							</tr>
						</tbody>
					</table>
					<button class="add">Add</button>
					&nbsp;&nbsp;&nbsp;
					<button class="remove_default">Remove Default</button>
				</div>
				<div class="has_multiple_responses">
					<label for="" class="main">Values</label>
					<table>
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Title</th>
								<th>Code</th>
								<th>Default</th>
							</tr>
						</thead>
						<tbody>
							<tr class="multi">
								<td><a href="#" class="geko-form-remove-item" title="Remove Option"><span class="geko-form-icon geko-form-icon-remove"></span></a></td>
								<td><input type="text" class="label" /></td>
								<td><input type="text" class="slug" /></td>
								<td><input type="checkbox" class="is_default" value="1" /></td>
							</tr>
						</tbody>
					</table>
					<button class="add">Add</button>
				</div>
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



