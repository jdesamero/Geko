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
	public function affix() {

		global $wpdb;
		
		$sTableName = 'geko_form_item_meta_value';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'fimv' )
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
		$this->createTable( $this->getPrimaryTable() );
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
		
		global $wpdb;
		
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

			$oSqlDelete = new Geko_Sql_Delete();
			$oSqlDelete->from( $this->_sPrimaryTable );
			
			foreach ( $aKeyFields as $sField ) {
				$oSqlDelete->where( $sField . ' = ?', $oFimv->getEntityPropertyValue( $sField ) );
			}
			
			$wpdb->query( strval( $oSqlDelete ) );
			
		}
		
		// resolve keys
		
		foreach ( $aPostData as $i => $aData ) {
			
			$iFmSecId = $aData[ 'fmsec_id' ];
			if ( 0 === strpos( $iFmSecId, '_' ) ) {
				$aPostData[ $i ][ 'fmsec_id' ] = $wpdb->aInsertIds[ 'Geko_Wp_Form_Section_Manage' ][ $iFmSecId ];
			}
			
			$iFmItmId = $aData[ 'fmitm_id' ];
			if ( 0 === strpos( $iFmItmId, '_' ) ) {
				$aPostData[ $i ][ 'fmitm_id' ] = $wpdb->aInsertIds[ 'Geko_Wp_Form_Item_Manage' ][ $iFmItmId ];
			}
			
			$iFmItmValIdx = $aData[ 'fmitmval_idx' ];
			if ( 0 === strpos( $iFmItmValIdx, '_' ) ) {
				$aPostData[ $i ][ 'fmitmval_idx' ] = $wpdb->aInsertIds[ 'Geko_Wp_Form_ItemValue_Manage' ][ $iFmItmId . ':' . $iFmItmValIdx ][ 1 ];
			}
			
		}
		
		parent::updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
		
	}
	
	//
	public function updateRelatedContinue( $aKeyFields ) {
		return TRUE;
	}
	
	
}



