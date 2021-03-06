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
	protected $_bDisableAttachPage = TRUE;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_form_meta_value', 'fmv' )
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
	
	
	
	
	
	//// crud methods
	
	//
	public function updateRelatedEntities( $aQueryParams, $aPostData, $aParams ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aSubItemIds = $oDb->getSubItemIds( 'Geko_Wp_Form_MetaData_Manage' );
		
		$aParams[ 'main_entity_pk_field' ] = 'fmmd_id';
		$aParams[ 'main_entity_format' ] = '%d';
		$aParams[ 'main_entity_id' ] = $aSubItemIds;
		
		if ( is_array( $aInsIds = $oDb->getInsertIds( 'Geko_Wp_Form_MetaData_Manage' ) ) ) {
			
			foreach ( $aPostData as $sId => $aRow ) {
				
				$iFmMdId = $aRow[ 'fmmd_id' ];
				$aSubItemIds[] = $iFmMdId;
				
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
				->from( '##pfx##geko_form_meta_value', 'fmv' )
				->where( 'fmv.fmmd_id * ($)', $aSubItemIds )
				->group( 'fmv.fmmd_id' )
			;
			
			// create a hash of counters
			$aIdxCounter = $oDb->fetchPairs( strval( $oQuery ) );
		}
		
		foreach ( $aPostData as $sId => $aRow ) {
			
			$iFmMdId = $aRow[ 'fmmd_id' ];			// updated item id
			$iFmMvIdx = $aRow[ 'fmmv_idx' ];
			
			if ( 0 === strpos( $iFmMvIdx, '_' ) ) {
				$aIdxCounter[ $iFmMdId ]++;
				$aPostData[ $sId ][ 'fmmv_idx' ] = $aIdxCounter[ $iFmMdId ];
			}
		}
		
		
		// set up query params
		unset( $aQueryParams[ 'form_id' ] );
		
		if ( is_array( $aSubItemIds ) && count( $aSubItemIds ) > 0 ) {
			$aQueryParams[ 'fmmd_id' ] = $aSubItemIds;
		} else {
			$aQueryParams[ 'force_empty' ] = TRUE;
		}
		
		
		parent::updateRelatedEntities( $aQueryParams, $aPostData, $aParams );
		
	}
	
	//
	public function updateRelatedInsertId( $aInsertVals ) {
		
		$aValues = $aInsertVals[ 0 ];
		
		return array( $aValues[ 'fmmd_id' ], $aValues[ 'fmmv_idx' ] );
	}
	
	
}



