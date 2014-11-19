<?php

//
class Geko_Wp_Form_ItemValue_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'fmitm_id:fmitmval_idx';
	
	protected $_sSubject = 'Form Item Value';
	protected $_sDescription = 'Values (to select from) for a form item.';
	protected $_sType = 'fmitmval';
	
	protected $_bExtraForms = TRUE;
	protected $_bHasDisplayMode = FALSE;
	protected $_bDisableAttachPage = TRUE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_item_value', 'fiv' )
			->fieldSmallInt( 'fmitmval_idx', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'fmitm_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'label' )
			->fieldVarChar( 'slug', array( 'size' => 256, 'key' ) )
			->fieldLongText( 'help' )
			->fieldSmallInt( 'rank', array( 'unsgnd', 'notnull' ) )
			->fieldBool( 'is_default' )
			->fieldBool( 'hide_items' )
			->fieldBool( 'show_widgets' )
			->indexUnq( 'form_item_index', array( 'fmitm_id', 'fmitmval_idx' ) )
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
	
	
	
	
	
	
	//// front-end display methods
	
	//
	public function extraForms( $oEntity ) {
		?>
		<div class="dialog" id="edit_form_value">
			<form>
				<div id="edit_form_item_value_lang" class="geko-wpadmin-tabs">
					<ul>
						<script id="item_value-dialog-tab-tmpl" type="text/x-jquery-tmpl">
							<li class="ui-tab-template"><a href="" class="ui-tab"><\/a><\/li>
						</script>
					</ul>
					<script id="item_value-dialog-content-tmpl" type="text/x-jquery-tmpl">
						<div class="ui-tabs-panel-single">
							<fieldset class="ui-helper-reset">
								<div>
									<label for="item_value_label">Label<\/label>
									<input type="text" name="item_value_label" id="item_value_label" value="" \/>
								<\/div>
								<div>
									<label for="item_value_slug">Code<\/label>
									<input type="text" name="item_value_slug" id="item_value_slug" value="" \/>
								<\/div>
								<div>
									<label for="item_value_help">Help Text<\/label>
									<textarea name="item_value_help" id="item_value_help"><\/textarea>
								<\/div>
							<\/fieldset>
						<\/div>
					</script>
				</div>
			</form>
		</div>
		<div id="dialog_value_templates">
			<div class="has_multiple_values">
				<label for="" class="main">Values</label>
				<div class="wrap">
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
							<script class="row-tmpl" type="text/x-jquery-tmpl">
								<tr>
									<td><a href="#" class="geko-form-remove-item" title="Remove Option"><span class="geko-form-icon geko-form-icon-remove"><\/span><\/a><\/td>
									<td><input type="text" class="option_value_label" \/><\/td>
									<td><input type="text" class="option_value_slug" \/><\/td>
									<td><input type="radio" class="option_value_is_default" name="is_default" value="1" \/><\/td>
								<\/tr>
							</script>
						</tbody>
					</table>
					<button class="add">Add</button>
					&nbsp;&nbsp;&nbsp;
					<button class="remove_default">Remove Default</button>
				</div>
			</div>
			<div class="has_multiple_responses">
				<label for="" class="main">Values</label>
				<div class="wrap">
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
							<script class="row-tmpl" type="text/x-jquery-tmpl">
								<tr class="multi">
									<td><a href="#" class="geko-form-remove-item" title="Remove Option"><span class="geko-form-icon geko-form-icon-remove"><\/span><\/a><\/td>
									<td><input type="text" class="option_value_label" \/><\/td>
									<td><input type="text" class="option_value_slug" \/><\/td>
									<td><input type="checkbox" class="option_value_is_default" value="1" \/><\/td>
								<\/tr>
							</script>
						</tbody>
					</table>
					<button class="add">Add</button>
				</div>
			</div>
		</div>
		<?php
	}
	
	
	
	//// crud methods
	
	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aSubItemIds = $oDb->getSubItemIds( 'Geko_Wp_Form_Item_Manage' );
		
		$aParams[ 'main_entity_pk_field' ] = 'fmitm_id';
		$aParams[ 'main_entity_format' ] = '%d';
		$aParams[ 'main_entity_id' ] = $aSubItemIds;
		
		if ( is_array( $aInsIds = $oDb->getInsertIds( 'Geko_Wp_Form_Item_Manage' ) ) ) {
			
			foreach ( $aPostData as $sId => $aRow ) {
				
				$iFmItmId = $aRow[ 'fmitm_id' ];
				$aSubItemIds[] = $iFmItmId;
				
				if ( $iInsId = $aInsIds[ $iFmItmId ] ) {
					$aPostData[ $sId ][ 'fmitm_id' ] = $iInsId;
				}
			}
		}
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'fiv.fmitm_id' )
			->field( 'MAX( fiv.fmitmval_idx )', 'fmitmval_max_idx' )
			->from( '##pfx##geko_form_item_value', 'fiv' )
			->where( 'fiv.fmitm_id * ($)', $aSubItemIds )
			->group( 'fiv.fmitm_id' )
		;
		
		// create a hash of counters
		$aIdxCounter = $oDb->fetchPairs( strval( $oQuery ) );
		
		foreach ( $aPostData as $sId => $aRow ) {
			$iFmItmId = $aRow[ 'fmitm_id' ];			// updated item id
			$iFmItmValIdx = $aRow[ 'fmitmval_idx' ];
			if ( 0 === strpos( $iFmItmValIdx, '_' ) ) {
				$aIdxCounter[ $iFmItmId ]++;
				$aPostData[ $sId ][ 'fmitmval_idx' ] = $aIdxCounter[ $iFmItmId ];
			}
		}
		
		
		// set up query params
		unset( $aQueryParams[ 'form_id' ] );
		
		if ( is_array( $aSubItemIds ) && count( $aSubItemIds ) > 0 ) {
			$aQueryParams[ 'fmitm_id' ] = $aSubItemIds;
		} else {
			$aQueryParams[ 'force_empty' ] = TRUE;
		}
		
		
		parent::updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
		
		$sAction = sprintf( '%s::updateRelatedEntities', $this->_sInstanceClass );
		do_action( $sAction );
		
	}
	
	
	//
	public function updateRelatedInsertId( $aInsertVals ) {
		
		$aValues = $aInsertVals[ 0 ];
		
		return array( $aValues[ 'fmitm_id' ], $aValues[ 'fmitmval_idx' ] );
	}
		
	
	
}



