<?php

//
class Geko_Wp_Form_Manage extends Geko_Wp_Options_Manage
{
	private static $aTypes;
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'form_id';
	
	protected $_sSubject = 'Form';
	protected $_sListingTitle = 'Title';	
	protected $_sDescription = 'An API/UI for creating forms.';
	protected $_sIconId = 'icon-edit-pages';
	protected $_sType = 'form';
	
	protected $_aSubOptions = array(
		'Geko_Wp_Form_ItemType_Manage',
		'Geko_Wp_Form_Section_Manage',
		'Geko_Wp_Form_Item_Manage',
		'Geko_Wp_Form_ItemValue_Manage',
		'Geko_Wp_Form_MetaData_Manage',
		'Geko_Wp_Form_MetaValue_Manage',
		'Geko_Wp_Form_ItemMetaValue_Manage',
		'Geko_Wp_Form_Response_Manage',
		'Geko_Wp_Form_ResponseValue_Manage'
	);
	
	protected $_iEntitiesPerPage = 10;
	protected $_bExtraForms = TRUE;
	
	protected $_bCanImport = TRUE;
	protected $_bCanExport = TRUE;
	protected $_bCanDuplicate = TRUE;
	protected $_bCanRestore = TRUE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form', 'f' )
			->fieldBigInt( 'form_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'title' )
			->fieldVarChar( 'slug', array( 'size' => 255, 'unq' ) )
			->fieldLongText( 'description' )
			->fieldLongText( 'notes' )
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
	
	
		
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {			
			wp_enqueue_script( 'geko_wp_form_manage' );
		}
		
		return $this;
	}
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		if ( $this->isDisplayMode( 'add|edit' ) ):
			
			$oUrl = new Geko_Uri();
			$oUrl->setVar( 'ajax', 1 );
			
			$aJsonParams = array(
				'script' => array(
					'ajax' => strval( $oUrl )
				)
			);
			
			?>
			<script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					
					$.get( oParams.script.ajax, function( data ) {
						
						Geko.Wp.Form.Manage.run( data );					
					
					}, 'json' );
					
				} );
				
			</script><?php
			
		endif;
		
		return $this;
	}
	
	// HACKISH!!!

	//
	public function doAdminInit() {
		parent::doAdminInit();
		$this->outputAjax();
	}
	
	//
	public function outputAjax() {
		
		if ( $_GET[ 'ajax' ] ) {
			
			//// auxiliary data
			
			$aLangs = new Geko_Wp_Language_Query( array(
				'showposts' => -1,
				'posts_per_page' => -1
			), FALSE );

			$aContext = Geko_Wp_Enumeration_Query::getSet( 'geko-form-context' );
			
			
			$sUnsupportedBrowser = '';
			
			$oBrowser = new Geko_Browser();
			if ( $oBrowser->isName( 'internet-explorer' ) ) {
				$sUnsupportedBrowser = 'Microsoft Internet Explorer';
			}/* elseif ( $oBrowser->isName( 'chrome' ) ) {
				$sUnsupportedBrowser = 'Google Chrome';			
			}*/
			
			
			//// set-up payload
			
			$aValues = $this->getStoredOptions();
			
			$aValues[ 'langs' ] = $aLangs->getRawEntities( TRUE );
			$aValues[ 'contexts' ] = $aContext->getRawEntities( TRUE );
			
			
			$aValues = $this->fixValues( $aValues );
			
			$aParams = array(
				'values' => $aValues,
				'unsupported_browser' => $sUnsupportedBrowser
			);
			
			echo Zend_Json::encode( $aParams );
			
			die();
		}
	}

	//
	public function fixValues( $aValues ) {
		
		////// FIX #1
		
		// ensure that:
		//    a) there has to be a corresponding language/context/slug for item meta value
		//    b) there is only one value if item type does not have multiple responses
		
		// group form meta data by language/context/slug then check against this
		$aFmdFmt = array();
		foreach ( $aValues[ 'fmmd' ] as $aFmd ) {
			$iLangId = $aFmd[ 'lang_id' ];
			$iContextId = $aFmd[ 'context_id' ];
			$sSlug = $aFmd[ 'slug' ];
			$iFmTypId = $aFmd[ 'fmitmtyp_id' ];
			$iMultipleResponse = $aValues[ 'fmitmtyp' ][ $iFmTypId ][ 'has_multiple_response' ];
			$aFmdFmt[ $iLangId ][ $iContextId ][ $sSlug ] = $iMultipleResponse;
		}
		
		// track meta indexes to be deleted
		$aUnsetIdx = array();
		$aCheckMultiIdx = array();
		foreach ( $aValues[ 'fmitmmv' ] as $i => $aFmimv ) {
			
			$iContextId = $aFmimv[ 'context_id' ];
			$iFmItmId = $aFmimv[ 'fmitm_id' ];
			$iFmItmValIdx = $aFmimv[ 'fmitmval_idx' ];
			$iFmSecId = $aFmimv[ 'fmsec_id' ];
			$iLangId = $aFmimv[ 'lang_id' ];
			$sSlug = $aFmimv[ 'slug' ];
			
			$sKey = sprintf( '%d:%d:%d:%d:%d:%s', $iContextId, $iFmItmId, $iFmItmValIdx, $iFmSecId, $iLangId, $sSlug );
			
			if ( $aCheck = $aFmdFmt[ $iLangId ][ $iContextId ] ) {
				if ( array_key_exists( $sSlug, $aCheck ) ) {
					if ( !$aCheck[ $sSlug ] ) {
						// there can only be one value
						$aCheckMultiIdx[ $sKey ][] = $i;
					}
				} else {
					$aUnsetIdx[] = $i;			// no matching slug, set for deletion
				}
			} else {
				$aUnsetIdx[] = $i;				// no matching language/context, set for deletion
			}
		}
		
		// keep the last value, set others for deletion
		foreach ( $aCheckMultiIdx as $aIdx ) {
			array_pop( $aIdx );
			$aUnsetIdx = array_merge( $aUnsetIdx, $aIdx );
		}
		
		//// perform deletions!!!
		foreach ( $aUnsetIdx as $iIdx ) {
			unset( $aValues[ 'fmitmmv' ][ $iIdx ] );
		}
		
		
		////// FIX #2

		// reset indexing to maintain sorting sequence
		
		$aSubs = array( 'fmitmtyp', 'fmsec', 'fmitm', 'fmitmval', 'fmmd', 'fmmv', 'fmitmmv' );
		
		foreach ( $aValues as $sKey => $mValue ) {
			if ( in_array( $sKey, $aSubs ) ) {
				$aValues[ $sKey ] = array_values( $mValue );
			}
		}
		
				
		////// FIX #3
		
		
		// enforce data formatting
		
		foreach ( $aValues[ 'contexts' ] as $i => $aVal ) {
			
			$aVal[ 'value' ] = intval( $aVal[ 'value' ] );
			
			$aValues[ 'contexts' ][ $i ] = $aVal;
		}
		
		
		
		return $aValues;
	}
	
	
	
	
	//// front-end display methods
	
	//
	public function listingPage() {
		
		?>
		<style type="text/css">
			
			th.column-slug,
			td.column-slug {
				width: auto !important;
			}

			th.column-description,
			td.column-description {
				width: 30%;
			}
			
		</style>
		<?php
		
		parent::listingPage();
	}
	
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-slug">Slug</th>
		<th scope="col" class="manage-column column-description">Description</th>
		<th scope="col" class="manage-column column-date-created">Date Created</th>
		<th scope="col" class="manage-column column-date-modified">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-slug"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-description"><?php $oEntity->echoTheExcerpt( 100 ); ?></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-modified"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	
	//
	public function formFields() {
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<style type="text/css">
			
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
			
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="form_title">Title</label></th>
				<td>
					<input id="form_title" name="form_title" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="form_slug">Slug</label></th>
				<td>
					<input id="form_slug" name="form_slug" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<?php $this->customFieldsPre(); ?>
			<tr>
				<th colspan="2"><label for="form_description">Description</label></th>
			</tr>
			<tr>
				<td colspan="2">
					<textarea cols="30" rows="5" id="form_description" name="form_description" />
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
		
	
	
	
	//// crud methods
	
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
		);
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
		
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
		
		$sTitle = $aValues[ 'title' ];
		
		// check title
		if ( $bContinue && !$sTitle ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty title was given
		}
		
		return $bContinue;
		
	}
	
	
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		if ( $aValues[ 'slug' ] != $oEntity->getSlug() ) {
			$aValues[ 'slug' ] = Geko_Wp_Db::generateSlug(
				$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
			);
		}
			
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}
	
	
	
	// delete overrides
	
	
	
	//// import/export/duplicate serialization methods
	
	//
	public function exportSerialize( $aParams = array() ) {
		
		$iFormId = $aParams[ 'entity_id' ];
		if ( !$oForm = $aParams[ 'entity' ] ) {
			$oForm = new Geko_Wp_Form( $iFormId );
		}
		
		$sTitleOverride = $aParams[ 'title' ];
		$aSerialized = array(
			'title' => ( $sTitleOverride ) ? $sTitleOverride : $oForm->getTitle(),
			'slug' => $oForm->getSlug(),
			'description' => $oForm->getDescription(),
			'notes' => $oForm->getNotes()
		);
		
		
		
		//// geko_form_section
		
		$aFmSecFmt = array();
		$aFmSec = new Geko_Wp_Form_Section_Query( array(
			'form_id' => $iFormId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aFmSec as $oFmSec ) {
			$aFmSecFmt[] = array(
				'id' => $oFmSec->getId(),
				'title' => $oFmSec->getTitle(),
				'slug' => $oFmSec->getSlug(),
				'description' => $oFmSec->getDescription(),
				'rank' => $oFmSec->getRank()			
			);
		}
		
		$aSerialized[ 'sections' ] = $aFmSecFmt;
		
		
		
		//// geko_form_item
		
		$aFmItmFmt = array();
		$aFmItm = new Geko_Wp_Form_Item_Query( array(
			'form_id' => $iFormId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aFmItm as $oFmItm ) {
			$aFmItmFmt[] = array(
				'id' => $oFmItm->getId(),
				'fmsec_id' => $oFmItm->getFmsecId(),
				'fmitmtyp_id' => $oFmItm->getFmitmtypId(),
				'slug' => $oFmItm->getSlug(),
				'title' => $oFmItm->getTitle(),
				'help' => $oFmItm->getHelp(),
				'css' => $oFmItm->getCss(),
				'rank' => $oFmItm->getRank(),
				'validation' => $oFmItm->getValidation(),
				'parent_itmvalidx_id' => $oFmItm->getParentItmvalidxId(),
				'parent_itm_id' => $oFmItm->getParentItmId(),
				'hide_subs' => $oFmItm->getHideSubs()
			);
		}
		
		$aSerialized[ 'items' ] = $aFmItmFmt;
		
		
		
		//// geko_form_item_value
		
		$aFmItmValFmt = array();
		$aFmItmVal = new Geko_Wp_Form_ItemValue_Query( array(
			'form_id' => $iFormId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aFmItmVal as $oFmItmVal ) {
			$aFmItmValFmt[] = array(
				'fmitmval_idx' => $oFmItmVal->getFmitmvalIdx(),
				'fmitm_id' => $oFmItmVal->getFmitmId(),
				'label' => $oFmItmVal->getLabel(),
				'slug' => $oFmItmVal->getSlug(),
				'help' => $oFmItmVal->getHelp(),
				'rank' => $oFmItmVal->getRank(),
				'is_default' => $oFmItmVal->getIsDefault(),
				'hide_items' => $oFmItmVal->getHideItems(),
				'show_widgets' => $oFmItmVal->getShowWidgets()
			);		
		}
		
		$aSerialized[ 'item_values' ] = $aFmItmValFmt;
		
		
		
		//// geko_form_item_meta_value
		
		$aFmItmMetaValFmt = array();
		$aFmItmMetaVal = new Geko_Wp_Form_ItemMetaValue_Query( array(
			'form_id' => $iFormId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aFmItmMetaVal as $oFmItmMetaVal ) {
			$aFmItmMetaValFmt[] = array(
				'context_id' => $oFmItmMetaVal->getContextId(),
				'fmitm_id' => $oFmItmMetaVal->getFmitmId(),
				'fmitmval_idx' => $oFmItmMetaVal->getFmitmvalIdx(),
				'fmsec_id' => $oFmItmMetaVal->getFmsecId(),
				'lang_id' => $oFmItmMetaVal->getLangId(),
				'slug' => $oFmItmMetaVal->getSlug(),
				'value' => $oFmItmMetaVal->getValue()
			);		
		}
		
		$aSerialized[ 'item_meta_values' ] = $aFmItmMetaValFmt;
		
		
		
		//// geko_form_meta_data
		
		$aFmMetaDataFmt = array();
		$aFmMetaData = new Geko_Wp_Form_MetaData_Query( array(
			'form_id' => $iFormId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aFmMetaData as $oFmMetaData ) {
			$aFmMetaDataFmt[] = array(
				'id' => $oFmMetaData->getId(),
				'fmitmtyp_id' => $oFmMetaData->getFmitmtypId(),
				'name' => $oFmMetaData->getName(),
				'slug' => $oFmMetaData->getSlug(),
				'rank' => $oFmMetaData->getRank(),
				'lang_id' => $oFmMetaData->getLangId(),
				'context_id' => $oFmMetaData->getContextId()
			);		
		}
		
		$aSerialized[ 'meta_data' ] = $aFmMetaDataFmt;
		
		
		
		//// geko_form_meta_value
		
		$aFmMetaValFmt = array();
		$aFmMetaVal = new Geko_Wp_Form_MetaValue_Query( array(
			'form_id' => $iFormId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aFmMetaVal as $oFmMetaVal ) {
			$aFmMetaValFmt[] = array(
				'fmmv_idx' => $oFmMetaVal->getFmmvIdx(),
				'fmmd_id' => $oFmMetaVal->getFmmdId(),
				'label' => $oFmMetaVal->getLabel(),
				'slug' => $oFmMetaVal->getSlug(),
				'rank' => $oFmMetaVal->getRank(),
				'is_default' => $oFmMetaVal->getIsDefault()
			);
		}
		
		$aSerialized[ 'meta_values' ] = $aFmMetaValFmt;
		
		
		
		return $aSerialized;
	}
	
	//
	public function importSerialized( $aSerialized ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		//// do checks
		
		// start transaction
		// NOTE: This only works on InnoDB tables!!!
		
		$bRes = FALSE;
		
		$oDb->beginTransaction();
		
		
		// setup values
		
		$sDateTime = $oDb->getTimestamp();
		
		$aMainValues = array(
			'title' => $aSerialized[ 'title' ],
			'description' => $aSerialized[ 'description' ],
			'notes' => $aSerialized[ 'notes' ],
			'date_created' => $sDateTime,
			'date_modified' => $sDateTime
		);
		
		if ( $iRestoreFormId = intval( $aSerialized[ 'entity_id' ] ) ) {
			
			// maintain form values
			$aMainValues[ 'form_id' ] = $iRestoreFormId;
			$aMainValues[ 'slug' ] = $aSerialized[ 'slug' ];
			
			
			//// clean up old values
			
			// one
			$oQuery1 = new Geko_Sql_Select();
			$oQuery1
				->field( 'fmd.fmmd_id', 'fmmd_id' )
				->from( '##pfx##geko_form_meta_data', 'fmd' )
				->where( 'fmmd_id.form_id = ?', $iRestoreFormId )
			;
			
			$oDb->delete( '##pfx##geko_form_meta_value', array(
				'fmmd_id IN (?)' => new Zend_Db_Expr( strval( $oQuery1 ) )
			) );
			
			
			// two
			$oDb->delete( '##pfx##geko_form_meta_data', array(
				'form_id = ?' => $iRestoreFormId
			) );
			
			
			// three
			$oQuery3 = new Geko_Sql_Select();
			$oQuery3
				->field( 'fs.fmsec_id', 'fmsec_id' )
				->from( '##pfx##geko_form_section', 'fs' )
				->where( 'form_id = ?', $iRestoreFormId )
			;
			
			$oDb->delete( '##pfx##geko_form_item_meta_value', array(
				'fmsec_id IN (?)' => new Zend_Db_Expr( strval( $oQuery3 ) )
			) );
			
			
			// four
			$oQuery4 = new Geko_Sql_Select();
			$oQuery4
				->field( 'fi.fmitm_id', 'fmitm_id' )
				->from( '##pfx##geko_form_item', 'fi' )
				->where( 'fmsec_id IN (?)', $oQuery3 )
			;
			
			$oDb->delete( '##pfx##geko_form_item_value', array(
				'fmitm_id IN (?)' => new Zend_Db_Expr( strval( $oQuery4 ) )
			) );
			
			
			// five
			$oDb->delete( '##pfx##geko_form_item', array(
				'fmsec_id IN (?)' => new Zend_Db_Expr( strval( $oQuery3 ) )
			) );
			
			
			// six
			$oDb->delete( '##pfx##geko_form_section', array(
				'form_id = ?' => $iRestoreFormId
			) );
			
			
			// seven
			$oDb->delete( '##pfx##geko_form', array(
				'form_id = ?' => $iRestoreFormId
			) );
			
			
		} else {
			
			// generate a new slug, if needed
			$aMainValues[ 'slug' ] = Geko_Wp_Db::generateSlug(
				$aSerialized[ 'slug' ], '##pfx##geko_form', 'slug'
			);
			
		}
		
		//// geko_form
		
		$bRes = $oDb->insert( '##pfx##geko_form', $aMainValues );
		
		
		//// geko_form_section
		
		if ( $bRes ) {
			
			$iDupFormId = ( $iRestoreFormId ) ? $iRestoreFormId : $oDb->lastInsertId() ;
			
			$aFmSecIds = array();
			$aFmSec = $aSerialized[ 'sections' ];
			
			foreach ( $aFmSec as $aSection ) {
				
				$bRes = $oDb->insert( '##pfx##geko_form_section', array(
					'form_id' => intval( $iDupFormId ),
					'title' => $aSection[ 'title' ],
					'slug' => $aSection[ 'slug' ],
					'description' => $aSection[ 'description' ],
					'rank' => $aSection[ 'rank' ]
				) );
				
				if ( !$bRes ) break;
				
				$aFmSecIds[ $aSection[ 'id' ] ] = $oDb->lastInsertId();
			}		
		}
		
		
		//// geko_form_item
		
		if ( $bRes ) {
			
			$aFmItmIds = array();
			$aParItmIds = array();
			$aFmItm = $aSerialized[ 'items' ];
			
			foreach ( $aFmItm as $aItem ) {
				
				$bRes = $oDb->insert( '##pfx##geko_form_item', array(
					'fmsec_id' => intval( $aFmSecIds[ $aItem[ 'fmsec_id' ] ] ),
					'fmitmtyp_id' => intval( $aItem[ 'fmitmtyp_id' ] ),
					'slug' => $aItem[ 'slug' ],
					'title' => $aItem[ 'title' ],
					'help' => $aItem[ 'help' ],
					'css' => $aItem[ 'css' ],
					'rank' => intval( $aItem[ 'rank' ] ),
					'validation' => $aItem[ 'validation' ],
					'parent_itmvalidx_id' => intval( $aItem[ 'parent_itmvalidx_id' ] ),
					'hide_subs' => intval( $aItem[ 'hide_subs' ] )
				) );
				
				if ( !$bRes ) break;
				
				$iFmItmId = $oDb->lastInsertId();
				$aFmItmIds[ $aItem[ 'id' ] ] = $iFmItmId;
				$aParItmIds[ $iFmItmId ] = $aItem[ 'parent_itm_id' ];	// re-translate this
				
			}
			
			// do parent ids
			foreach ( $aParItmIds as $iFmItmId => $iParItmId ) {
				
				$bRes = $oDb->update(
					'##pfx##geko_form_item',
					array( 'parent_itm_id' => intval( $aFmItmIds[ $iParItmId ] ) ),
					array( 'fmitm_id = ?' => intval( $iFmItmId ) )
				);
				
				if ( !$bRes ) break;
			}
			
		}
		
		
		//// geko_form_item_value
		
		if ( $bRes ) {
		
			$aFmItmVal = $aSerialized[ 'item_values' ];
			
			foreach ( $aFmItmVal as $aItemVal ) {
				
				$bRes = $oDb->insert( '##pfx##geko_form_item_value', array(
					'fmitmval_idx' => intval( $aItemVal[ 'fmitmval_idx' ] ),
					'fmitm_id' => intval( $aFmItmIds[ $aItemVal[ 'fmitm_id' ] ] ),
					'label' => $aItemVal[ 'label' ],
					'slug' => $aItemVal[ 'slug' ],
					'help' => $aItemVal[ 'help' ],
					'rank' => intval( $aItemVal[ 'rank' ] ),
					'is_default' => intval( $aItemVal[ 'is_default' ] ),
					'hide_items' => intval( $aItemVal[ 'hide_items' ] ),
					'show_widgets' => intval( $aItemVal[ 'show_widgets' ] )
				) );
				
				if ( !$bRes ) break;
			}
		}
		
		
		//// geko_form_item_meta_value
		
		if ( $bRes ) {
		
			$aFmItmMetaVal = $aSerialized[ 'item_meta_values' ];
			
			foreach ( $aFmItmMetaVal as $aItemMetaVal ) {
				
				$bRes = $oDb->insert( '##pfx##geko_form_item_meta_value', array(
					'context_id' => intval( $aItemMetaVal[ 'context_id' ] ),
					'fmitm_id' => intval( $aFmItmIds[ $aItemMetaVal[ 'fmitm_id' ] ] ),
					'fmitmval_idx' => intval( $aItemMetaVal[ 'fmitmval_idx' ] ),
					'fmsec_id' => intval( $aFmSecIds[ $aItemMetaVal[ 'fmsec_id' ] ] ),
					'lang_id' => intval( $aItemMetaVal[ 'lang_id' ] ),
					'slug' => $aItemMetaVal[ 'slug' ],
					'value' => $aItemMetaVal[ 'value' ]
				) );
				
				if ( !$bRes ) break;
			}
		}
		
		
		//// geko_form_meta_data
		
		if ( $bRes ) {
			
			$aFmMetaDataIds = array();
			$aFmMetaData = $aSerialized[ 'meta_data' ];
			
			foreach ( $aFmMetaData as $aMetaData ) {
				
				$bRes = $oDb->insert( '##pfx##geko_form_meta_data', array(
					'form_id' => intval( $iDupFormId ),
					'fmitmtyp_id' => intval( $aMetaData[ 'fmitmtyp_id' ] ),
					'name' => $aMetaData[ 'name' ],
					'slug' => $aMetaData[ 'slug' ],
					'rank' => intval( $aMetaData[ 'rank' ] ),
					'lang_id' => intval( $aMetaData[ 'lang_id' ] ),
					'context_id' => intval( $aMetaData[ 'context_id' ] )
				) );
				
				if ( !$bRes ) break;
				
				$aFmMetaDataIds[ $aMetaData[ 'id' ] ] = $oDb->lastInsertId();
			}
		}
		
		
		//// geko_form_meta_value
		
		if ( $bRes ) {
		
			$aFmMetaVal = $aSerialized[ 'meta_values' ];
			
			foreach ( $aFmMetaVal as $aMetaValue ) {
				
				$bRes = $oDb->insert( '##pfx##geko_form_meta_value', array(
					'fmmv_idx' => intval( $aMetaValue[ 'fmmv_idx' ] ),
					'fmmd_id' => intval( $aFmMetaDataIds[ $aMetaValue[ 'fmmd_id' ] ] ),
					'label' => $aMetaValue[ 'label' ],
					'slug' => $aMetaValue[ 'slug' ],
					'rank' => intval( $aMetaValue[ 'rank' ] ),
					'is_default' => intval( $aMetaValue[ 'is_default' ] )
				) );
				
				if ( !$bRes ) break;
			}
		}
		
		// commit if no errors
		if ( $bRes ) {
			
			$oDb->commit();
			
			return array( 'dup_entity_id' => $iDupFormId );
		}
		
		// rollback if there are errors
		
		$oDb->rollBack();	
		
		return FALSE;
	}
	
	
	
}


