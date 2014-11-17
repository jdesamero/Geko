<?php

//
class Geko_Wp_Form_MetaData_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'fmmd_id';
	
	protected $_sSubject = 'Form Meta Data';
	protected $_sDescription = 'Meta data that can be attached to form items/values.';
	protected $_sType = 'fmmd';
	
	protected $_bSubMainFields = TRUE;
	protected $_bExtraForms = TRUE;
	protected $_bHasDisplayMode = FALSE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		Geko_Wp_Enumeration_Manage::getInstance()->add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_meta_data', 'fmd' )
			->fieldMediumInt( 'fmmd_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'form_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldSmallInt( 'fmitmtyp_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'name' )
			->fieldVarChar( 'slug', array( 'size' => 256, 'key' ) )
			->fieldSmallInt( 'rank', array( 'unsgnd', 'notnull' ) )
			->fieldSmallInt( 'lang_id', array( 'unsgnd', 'notnull' ) )
			->fieldSmallInt( 'context_id', array( 'unsgnd', 'notnull' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
		
	}
	
	// create table
	public function install() {
		
		parent::install();
		
		Geko_Once::run( sprintf( '%s::enumeration', __CLASS__ ), array( $this, 'installEnumeration' ) );
				
		$this->createTableOnce();
		
		return $this;
	}
	
	//
	public function installEnumeration() {
		
		Geko_Wp_Enumeration_Manage::getInstance()->install();
		Geko_Wp_Enumeration_Manage::populate( array(
			array( 'title' => 'Form Context', 'slug' => 'geko-form-context', 'description' => 'List of areas where meta data is applicable.' ),
			array(
				array( 'title' => 'Question', 'slug' => 'geko-form-context-question', 'value' => 0, 'rank' => 0, 'description' => 'Meta data for form questions.' ),
				array( 'title' => 'Choice', 'slug' => 'geko-form-context-choice', 'value' => 1, 'rank' => 1, 'description' => 'Meta data for form choices.' ),
				array( 'title' => 'Section', 'slug' => 'geko-form-context-section', 'value' => 2, 'rank' => 2, 'description' => 'Meta data for form sections.' )
			)
		) );
		
	}
	
	
	
	//
	public function getStoredSubOptionParams( $oMainMng, $oMainEnt ) {
		
		$aParams = array_merge(
			parent::getStoredSubOptionParams( $oMainMng, $oMainEnt ),
			array (
				'orderby' => 'rank',
				'order' => 'ASC'
			)
		);
		
		return $aParams;
	}
	
	//
	public function modifySubEntityValues( $aRow, $oSubItem ) {
		$aRow[ 'item_type' ] = $oSubItem->getEntityPropertyValue( 'item_type' );
		return $aRow;
	}

	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	//// front-end display methods
	
	//
	public function extraForms( $oEntity ) {
		
		$aContext = Geko_Wp_Enumeration_Query::getSet( 'geko-form-context' );
		
		?>
		<div class="dialog" id="edit_meta_data">
			<form>
				<fieldset class="ui-helper-reset">
					<div>
						<label for="meta_data_name">Name</label>
						<input type="text" name="meta_data_name" id="meta_data_name" value="" />
					</div>
					<div>
						<label for="meta_data_slug">Code</label>
						<input type="text" name="meta_data_slug" id="meta_data_slug" value="" />
					</div>
					<div>
						<label for="meta_data_context_id">Context</label>
						<select name="meta_data_context_id" id="meta_data_context_id">
							<?php echo $aContext->implode( array( '<option value="##Value##">##Title##</option>', '' ) ); ?>
						</select>
					</div>
				</fieldset>
			</form>
		</div>		
		<div class="dialog" id="move_meta_data" title="Move Meta Data to Other Language">
			<form>
				<fieldset class="ui-helper-reset">
					<div>
						<label for="meta_data_language">Language</label>
						<select name="meta_data_language" id="meta_data_language"></select>
					</div>
				</fieldset>
			</form>
		</div>		
		<?php
	}
	
	//
	public function formFields( $oEntity, $sSection ) {
		
		if ( 'pre' == $sSection ):
			
			?>
			<tr>
				<th colspan="2"><label>Form Meta Data</label></th>
			</tr>
			<tr>
				<td colspan="2">
					<div class="loading">Loading... <span class="loading"></span></div>
					<div id="meta_data_editor" class="geko-wpadmin-tabs">
						<ul>
							<li class="ui-tab-template"><a href="#{href}" class="ui-tab">#{label}</a></li>
						</ul>
						<div class="ui-tabs-panel-template">
							<div class="ui-tabs-panel-header">
								<div class="icons">
									<a href="#" class="geko-form-add-item"><span class="geko-form-icon"></span></a>
								</div>
								<br clear="all" />
							</div>
							<div class="ui-tabs-panel-body">
								<ul class="geko-form-items">
									<li class="geko-form-item">
										<a href="#" class="geko-form-item-options"><span class="geko-form-icon"></span></a>
										<a href="#" class="geko-form-remove-item" title="Remove Meta Data"><span class="geko-form-icon geko-form-icon-remove"></span></a>
										<a href="#" class="geko-form-move-item" title="Move Meta Data to Other Language"><span class="geko-form-icon geko-form-icon-submit"></span></a>
										<span class="label"></span>
										<div class="fix"></div>
									</li>
								</ul>
							</div>
							<div class="ui-tabs-panel-footer">&nbsp;</div>
						</div>
					</div>
					<input type="hidden" id="fmmd" name="fmmd" />
					<input type="hidden" id="fmmv" name="fmmv" />
				</td>
			</tr>
			<?php
			
		endif;
	
	}
	
	
	
}



