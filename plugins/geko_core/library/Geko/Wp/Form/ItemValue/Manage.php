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
	
	
	
	//// init
	
	//
	public function affix() {

		global $wpdb;
		
		$sTable = 'geko_form_item_value';
		Geko_Wp_Db::addPrefix( $sTable );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTable, 'fiv' )
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
		$this->createTable( $this->getPrimaryTable() );
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
	
	
	
	// HACKish, disable this
	public function attachPage() { }
	
	
	
	
	
	//// front-end display methods
	
	//
	public function extraForms( $oEntity ) {
		?>
		<div class="dialog" id="edit_form_value">
			<form>
				<div id="edit_form_itemval_lang" class="geko-wpadmin-tabs">
					<ul>
						<li class="ui-tab-template"><a href="#{href}" class="ui-tab">#{label}</a></li>
					</ul>
					<div class="ui-tabs-panel-template">
						<div class="ui-tabs-panel-single">
							<fieldset class="ui-helper-reset">
								<div>
									<label for="itemval_label">Label</label>
									<input type="text" name="itemval_label" id="itemval_label" value="" />
								</div>
								<div>
									<label for="itemval_slug">Code</label>
									<input type="text" name="itemval_slug" id="itemval_slug" value="" />
								</div>
								<div>
									<label for="itemval_help">Help Text</label>
									<textarea name="itemval_help" id="itemval_help"></textarea>
								</div>
							</fieldset>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}
	
	
	
	//// crud methods
	
	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
		global $wpdb;
		
		$aSubItemIds = $wpdb->aSubItemIds[ 'Geko_Wp_Form_Item_Manage' ];
		
		unset( $aQueryParams[ 'form_id' ] );
		$aQueryParams[ 'fmitm_id' ] = $aSubItemIds;
		
		$aParams[ 'main_entity_pk_field' ] = 'fmitm_id';
		$aParams[ 'main_entity_format' ] = '%d';
		$aParams[ 'main_entity_id' ] = $aSubItemIds;
		
		if ( is_array( $aInsIds = $wpdb->aInsertIds[ 'Geko_Wp_Form_Item_Manage' ] ) ) {
			
			foreach ( $aPostData as $sId => $aRow ) {
				
				$iFmItmId = $aRow[ 'fmitm_id' ];
				if ( $iInsId = $aInsIds[ $iFmItmId ] ) {
					$aPostData[ $sId ][ 'fmitm_id' ] = $iInsId;
				}
			}
		}
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'fiv.fmitm_id' )
			->field( 'MAX( fiv.fmitmval_idx )', 'fmitmval_max_idx' )
			->from( $wpdb->geko_form_item_value, 'fiv' )
			->where( 'fiv.fmitm_id * ($)', $aSubItemIds )
			->group( 'fiv.fmitm_id' )
		;
		
		// create a hash of counters
		$aIdxCounter = Geko_Wp_Db::getPair( strval( $oQuery ) );
		
		foreach ( $aPostData as $sId => $aRow ) {
			$iFmItmId = $aRow[ 'fmitm_id' ];			// updated item id
			$iFmItmValIdx = $aRow[ 'fmitmval_idx' ];
			if ( 0 === strpos( $iFmItmValIdx, '_' ) ) {
				$aIdxCounter[ $iFmItmId ]++;
				$aPostData[ $sId ][ 'fmitmval_idx' ] = $aIdxCounter[ $iFmItmId ];
			}
		}
		
		parent::updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
		
		$sAction = $this->_sInstanceClass . '::' . 'updateRelatedEntities';
		do_action( $sAction );
		
	}
	
	
	//
	public function updateRelatedInsertId( $aInsertVals ) {
		
		global $wpdb;
		
		$aValues = $aInsertVals[ 0 ];
		
		return array( $aValues[ 'fmitm_id' ], $aValues[ 'fmitmval_idx' ] );
	}
		
	
	
}



