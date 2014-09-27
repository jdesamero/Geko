<?php

//
class Geko_Wp_Form_ItemMetaValue_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sEntityIdVarName = '';
	
	protected $_sSubject = 'Form Item Meta Value';
	protected $_sDescription = 'Meta data values for the item';
	protected $_sType = 'fmitmmv';
	
	protected $_bExtraForms = TRUE;
	protected $_bHasDisplayMode = FALSE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_item_meta_value', 'fimv' )
			->fieldSmallInt( 'context_id', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'fmitm_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldSmallInt( 'fmitmval_idx', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'fmsec_id', array( 'unsgnd', 'notnull', 'key' ) )
			->fieldSmallInt( 'lang_id', array( 'unsgnd', 'notnull' ) )
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
	
	
	
	//// front-end display methods
	
	//
	public function extraForms( $oEntity ) {

	}
	
	
	
	//// crud methods

	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		// delete existing
		
		unset( $aParams[ 'main_entity_pk_field' ] );
		unset( $aParams[ 'main_entity_format' ] );
		unset( $aParams[ 'main_entity_id' ] );
		
		$aFimv = new Geko_Wp_Form_ItemMetaValue_Query( array(
			'showposts' => -1,
			'posts_per_page' => -1,
			'form_id' => $aQueryParams[ 'form_id' ]
		), FALSE );
		
		$aKeyFields = array( 'context_id', 'fmitm_id', 'fmitmval_idx', 'fmsec_id', 'lang_id', 'slug' );
		
		foreach ( $aFimv as $oFimv ) {
			
			$aDeleteKeys = array();
			
			foreach ( $aKeyFields as $sField ) {
				$aDeleteKeys[ sprintf( '%s = ?', $sField ) ] = $oFimv->getEntityPropertyValue( $sField );
			}
			
			$oDb->delete( $this->_sPrimaryTable, $aDeleteKeys );
		}
		
		// resolve keys
		
		foreach ( $aPostData as $i => $aData ) {
			
			$iFmSecId = $aData[ 'fmsec_id' ];
			if ( 0 === strpos( $iFmSecId, '_' ) ) {
				$aFmSecIds = $oDb->getInsertIds( 'Geko_Wp_Form_Section_Manage' );
				$aPostData[ $i ][ 'fmsec_id' ] = $aFmSecIds[ $iFmSecId ];
			}
			
			$iFmItmId = $aData[ 'fmitm_id' ];
			if ( 0 === strpos( $iFmItmId, '_' ) ) {
				$aFmItmIds = $oDb->getInsertIds( 'Geko_Wp_Form_Item_Manage' );
				$aPostData[ $i ][ 'fmitm_id' ] = $aFmItmIds[ $iFmItmId ];
			}
			
			$iFmItmValIdx = $aData[ 'fmitmval_idx' ];
			if ( 0 === strpos( $iFmItmValIdx, '_' ) ) {
				$aFmItmValIdxs = $oDb->getInsertIds( 'Geko_Wp_Form_ItemValue_Manage' );
				$aPostData[ $i ][ 'fmitmval_idx' ] = $aFmItmValIdxs[ sprintf( '%s:%s', $iFmItmId, $iFmItmValIdx ) ][ 1 ];
			}
			
		}
		
		parent::updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
		
	}
	
	//
	public function updateRelatedContinue( $aKeyFields ) {
		return TRUE;
	}
	
	
}



