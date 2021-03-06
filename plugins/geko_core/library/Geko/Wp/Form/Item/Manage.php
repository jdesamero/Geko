<?php

//
class Geko_Wp_Form_Item_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sSubject = 'Form Item';
	protected $_sDescription = 'Items (questions) belonging to a form section.';
	protected $_sType = 'fmitm';
	
	protected $_bExtraForms = TRUE;
	protected $_bHasDisplayMode = FALSE;
	protected $_bDisableAttachPage = TRUE;
	
	protected $aItemParents = array();
	
	
	//// init
	
	
	//
	public function add() {
		
		parent::add();
		
		
		//// actions
		
		add_action( 'Geko_Wp_Form_ItemValue_Manage::updateRelatedEntities', array( $this, 'updateParentIds' ), 10 );
		
		
		//// database
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_item', 'fi' )
			->fieldBigInt( 'fmitm_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'fmsec_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldSmallInt( 'fmitmtyp_id', array( 'unsgnd', 'notnull' ) )
			->fieldVarChar( 'slug', array( 'size' => 256, 'key' ) )
			->fieldLongText( 'title' )
			->fieldLongText( 'help' )
			->fieldLongText( 'css' )
			->fieldSmallInt( 'rank', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'validation' )
			->fieldSmallInt( 'parent_itmvalidx_id', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'parent_itm_id', array( 'unsgnd', 'notnull' ) )
			->fieldBool( 'hide_subs' )
		;
		
		$this->addTable( $oSqlTable );
		
		
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
		<div class="dialog" id="edit_form_item">
			<form>
				<div id="edit_form_item_lang" class="geko-wpadmin-tabs">
					<ul>
						<script id="item-dialog-tab-tmpl" type="text/x-jquery-tmpl">
							<li class="ui-tab-template"><a href="" class="ui-tab"></a></li>
						</script>
					</ul>
					<script id="item-dialog-content-tmpl" type="text/x-jquery-tmpl">
						<div class="ui-tabs-panel-single">
							<fieldset class="ui-helper-reset">
								<div>
									<label for="item_title">Title<\/label>
									<input type="text" name="item_title" id="item_title" value="" \/>
								<\/div>
								<div>
									<label for="item_slug">Code<\/label>
									<input type="text" name="item_slug" id="item_slug" value="" \/>
								<\/div>
								<div>
									<label for="item_help">Help Text<\/label>
									<textarea name="item_help" id="item_help"><\/textarea>
								<\/div>
								<div>
									<label for="item_css">CSS<\/label>
									<input type="text" name="item_css" id="item_css" value="" \/>
								<\/div>
							<\/fieldset>
						<\/div>
					</script>
				</div>
			</form>
		</div>		
		<div class="dialog" id="move_form_item" title="Move Item to Other Section">
			<form>
				<fieldset class="ui-helper-reset">
					<div>
						<label for="item_section">Section</label>
						<select name="item_section" id="item_section"></select>
					</div>
				</fieldset>
			</form>
		</div>		
		<?php
	}
	
	
	
	//// crud methods

	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aSubItemIds = $oDb->getSubItemIds( 'Geko_Wp_Form_Section_Manage' );
		
		$aParams[ 'main_entity_pk_field' ] = 'fmsec_id';
		$aParams[ 'main_entity_format' ] = '%d';
		$aParams[ 'main_entity_id' ] = $aSubItemIds;
		
		if ( is_array( $aInsIds = $oDb->getInsertIds( 'Geko_Wp_Form_Section_Manage' ) ) ) {
			
			foreach ( $aPostData as $iId => $aRow ) {
				
				$iFmSecId = $aRow[ 'fmsec_id' ];
				$aSubItemIds[] = $iFmSecId;				// track inserted values as well
				
				if ( $iInsId = $aInsIds[ $iFmSecId ] ) {
					$aPostData[ $iId ][ 'fmsec_id' ] = $iInsId;
				}
			}
			
		}
		
		// track parents for conditional questions
		foreach ( $aPostData as $mId => $aRow ) {
			
			$mParItmId = $aRow[ 'parent_itm_id' ];
			$mParItmValIdx = $aRow[ 'parent_itmvalidx_id' ];
			
			if (
				( 0 === strpos( $mParItmId, '_' ) ) || 
				( 0 === strpos( $mParItmValIdx, '_' ) )
			) {
				$this->aItemParents[ $mId ] = array( $mParItmId, $mParItmValIdx );
			}
		}
		
		
		// set up query params
		unset( $aQueryParams[ 'form_id' ] );
		
		if ( is_array( $aSubItemIds ) && count( $aSubItemIds ) > 0 ) {
			$aQueryParams[ 'fmsec_id' ] = $aSubItemIds;
		} else {
			$aQueryParams[ 'force_empty' ] = TRUE;
		}
		
		
		parent::updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
		
	}
	
	//
	public function updateParentIds( $aItemValIds ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aItemIds = $oDb->getInsertIds( 'Geko_Wp_Form_Item_Manage' );
		$aItemValIds = $oDb->getInsertIds( 'Geko_Wp_Form_ItemValue_Manage' );
		
		// translate
		foreach ( $this->aItemParents as $mId => $aIds ) {
			
			$iItemId = $aItemIds[ $mId ];
			$sIvKey = sprintf( '%s:%s', $aIds[ 0 ], $aIds[ 1 ] );
			
			$aIvIds = $aItemValIds[ $sIvKey ];
			
			if ( $iItemId && $aIvIds ) {
				
				$oDb->update(
					'##pfx##geko_form_item',
					array(
						'parent_itm_id' => $aIvIds[ 0 ],
						'parent_itmvalidx_id' => $aIvIds[ 1 ]
					),
					array( 'fmitm_id = ?' => $iItemId )
				);
			}
			
		}
		
	}
	
	
}



