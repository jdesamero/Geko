<?php

//
class Geko_Wp_Form_MetaValue_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'fmmd_id:fmmv_idx';
	
	protected $_sSubject = 'Form Meta Value';
	protected $_sDescription = 'Possible meta data values.';
	protected $_sType = 'fmmv';
	
	protected $_bHasDisplayMode = FALSE;
	
	
	
	//// init
	
	//
	public function affix() {

		global $wpdb;
		
		$sTableName = 'geko_form_meta_value';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'fmv' )
			->fieldSmallInt( 'fmmv_idx', array( 'unsgnd', 'notnull' ) )
			->fieldBigInt( 'fmmd_id', array( 'unsgnd', 'notnull' ) )
			->fieldLongText( 'label' )
			->fieldVarChar( 'slug', array( 'size' => 256, 'key' ) )
			->fieldSmallInt( 'rank', array( 'unsgnd', 'notnull' ) )
			->fieldBool( 'is_default' )
			->indexUnq( 'form_meta_value_index', array( 'fmmd_id', 'fmmv_idx' ) )
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
	
	
	
	//// crud methods
	
	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
		global $wpdb;
		
		$aSubItemIds = $wpdb->aSubItemIds[ 'Geko_Wp_Form_MetaData_Manage' ];
		
		unset( $aQueryParams[ 'form_id' ] );
		$aQueryParams[ 'fmmd_id' ] = $aSubItemIds;
		
		$aParams[ 'main_entity_pk_field' ] = 'fmmd_id';
		$aParams[ 'main_entity_format' ] = '%d';
		$aParams[ 'main_entity_id' ] = $aSubItemIds;
		
		if ( is_array( $aInsIds = $wpdb->aInsertIds[ 'Geko_Wp_Form_MetaData_Manage' ] ) ) {
			
			foreach ( $aPostData as $sId => $aRow ) {
				
				$iFmMdId = $aRow[ 'fmmd_id' ];
				if ( $iInsId = $aInsIds[ $iFmMdId ] ) {
					$aPostData[ $sId ][ 'fmmd_id' ] = $iInsId;
				}
			}
		}
		
		$aIdxCounter = array();
		if ( is_array( $aSubItemIds ) && ( count( $aIdxCounter ) > 0 ) ) {
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'fmv.fmmd_id' )
				->field( 'MAX( fmv.fmmv_idx )', 'fmmv_max_idx' )
				->from( $wpdb->geko_form_meta_value, 'fmv' )
				->where( 'fmv.fmmd_id * ($)', $aSubItemIds )
				->group( 'fmv.fmmd_id' )
			;
			
			// create a hash of counters
			$aIdxCounter = Geko_Wp_Db::getPair( strval( $oQuery ) );
		}
		
		foreach ( $aPostData as $sId => $aRow ) {
			$iFmMdId = $aRow[ 'fmmd_id' ];			// updated item id
			$iFmMvIdx = $aRow[ 'fmmv_idx' ];
			if ( 0 === strpos( $iFmMvIdx, '_' ) ) {
				$aIdxCounter[ $iFmMdId ]++;
				$aPostData[ $sId ][ 'fmmv_idx' ] = $aIdxCounter[ $iFmMdId ];
			}
		}
		
		parent::updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
		
	}
	
	//
	public function updateRelatedInsertId( $aInsertVals ) {
		
		global $wpdb;
		
		$aValues = $aInsertVals[ 0 ];
		
		return array( $aValues[ 'fmmd_id' ], $aValues[ 'fmmv_idx' ] );
	}
	
	
}



