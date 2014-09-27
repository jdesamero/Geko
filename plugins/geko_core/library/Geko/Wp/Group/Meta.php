<?php

// abstract
class Geko_Wp_Group_Meta extends Geko_Wp_Options_Meta
{
	protected static $aMetaCache = array();
	
	protected $_sGroupTypeSlug = 'group';
	protected $_sParentFieldName = 'group_id';
	
	protected $aPassParams = array();
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$this->forceInit( __CLASS__ );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_group_meta', 'gm' )
			->fieldBigInt( 'gmeta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'group_id', array( 'unsgnd' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd' ) )
			->fieldLongText( 'meta_value' )
			->indexKey( 'group_mkey_id', array( 'group_id', 'mkey_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( '##pfx##geko_group_meta_members', 'gmm' )
			->fieldBigInt( 'gmeta_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'member_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'member_value' )
			->fieldLongText( 'flags' )
		;
		
		$this->addTable( $oSqlTable2, FALSE );
		
		
		return $this;
	}
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		$this->createTableOnce( '##pfx##geko_group_meta_members' );
		
		return $this;
	}
	
	
	
	//// accessors
	
	//
	public function getStoredOptions() {
		
		$iGroupId = intval( $_GET[ 'group_id' ] );
		
		if ( $iGroupId ) {
			
			$this->setMetaCache( $iGroupId );
			
			$aMeta = array();
			$aElemsGroup = parent::getElemsGroup();			// yields correct result!
			$aMetaCache = self::$aMetaCache[ $iGroupId ];
			
			foreach ( $aElemsGroup as $sMetaKey => $aElem ) {
				if ( isset( $aMetaCache[ $sMetaKey ] ) ) {
					$aMeta[ $sMetaKey ] = $aMetaCache[ $sMetaKey ];
				}
			}
			
			return $aMeta;
		
		} else {
			return array();
		}
	}
	
	
	//
	public function getMeta( $iGroupId, $sMetaKey = '' ) {
		
		$this->setMetaCache( $iGroupId );
		
		if ( $sMetaKey ) {
			return self::$aMetaCache[ $iGroupId ][ sprintf( '%s%s', $this->getPrefixWithSep(), $sMetaKey ) ];
		} else {
			return self::$aMetaCache[ $iGroupId ];
		}
	}
	
	
	//// cache helpers
	
	//
	protected function setMetaCache( $iGroupId ) {
		
		if ( !isset( self::$aMetaCache[ $iGroupId ] ) ) {
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'g.gmeta_id', 'gmeta_id' )
				->field( 'h.meta_key', 'meta_key' )
				->field( 'g.meta_value', 'meta_value' )
				
				->from( '##pfx##geko_group_meta', 'g' )
				
				->joinLeft( '##pfx##geko_meta_key', 'h' )
					->on( 'h.mkey_id = g.mkey_id' )
				
				->where( 'g.group_id = ?', $iGroupId )
			;
			
			$aFmt = Geko_Wp_Db::getResultsHash( strval( $oQuery ), 'meta_key' );
			
			////
			$aSubVals = $this->gatherSubMetaValues( $aFmt, 'geko_group_meta_members', 'gmeta_id' );
			
			foreach ( $aFmt as $sKey => $oItem ) {
				if ( isset( $aSubVals[ $oItem->gmeta_id ] ) ) {
					$aFmt[ $sKey ] = $aSubVals[ $oItem->gmeta_id ];
				} else {
					$aFmt[ $sKey ] = maybe_unserialize( $oItem->meta_value );				
				}
			}
			
			self::$aMetaCache[ $iGroupId ] = $aFmt;
		}
	}
	
	
	
	
	
	//// crud methods
	
	// cleanup all orphaned metadata
	public function delete( $oGroup ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		// meta
		$oQuery1 = new Geko_Sql_Select();
		$oQuery1
			->field( 'g.group_id', 'group_id' )
			->from( '##pfx##geko_groups', 'g' )
		;
		
		$oDb->delete( '##pfx##geko_group_meta', array(
			'group_id NOT IN (?)' => new Zend_Db_Expr( strval( $oQuery1 ) )
		) );
		
		// members
		$oQuery2 = new Geko_Sql_Select();
		$oQuery2
			->field( 'gm.gmeta_id', 'gmeta_id' )
			->from( '##pfx##geko_group_meta', 'gm' )
		;
		
		$oDb->delete( '##pfx##geko_group_meta_members', array(
			'gmeta_id NOT IN (?)' => new Zend_Db_Expr( strval( $oQuery2 ) )
		) );
		
	}
	
	
	
	//
	public function getElemsGroup( $oGroup, $sGroupTypeSlug = '' ) {
		
		$aElemsGroup = array();
		
		$this->aPassParams = array( $oGroup, 'main', $sGroupTypeSlug );
		$aElemsGroup = array_merge( $aElemsGroup, parent::getElemsGroup() );
		
		$this->aPassParams = array( $oGroup, 'extra', $sGroupTypeSlug );
		$aElemsGroup = array_merge( $aElemsGroup, parent::getElemsGroup() );
		
		return $aElemsGroup;
	}


	// save the data
	public function save(
		$oGroup, $sMode = 'insert', $sGroupTypeSlug = '', $aParams = NULL, $aDataVals = NULL, $aFileVals = NULL
	) {
		
		//
		$aElemsGroup = isset( $aParams[ 'elems_group' ] ) ?
			$aParams[ 'elems_group' ] : 
			$this->getElemsGroup( $oGroup, $sGroupTypeSlug )
		;
		
		if ( 'update' == $sMode ) {
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'g.gmeta_id', 'gmeta_id' )
				->field( 'h.meta_key', 'meta_key' )
				->field( 'g.meta_value', 'meta_value' )
				
				->from( '##pfx##geko_group_meta', 'g' )
				
				->joinLeft( '##pfx##geko_meta_key', 'h' )
					->on( 'h.mkey_id = g.mkey_id' )
				
				->where( 'g.group_id = ?', $oGroup->getId() )
			;
			
			$aMeta = Geko_Wp_Db::getResultsHash( strval( $oQuery ), 'meta_key' );
			
		} else {
			$aMeta = array();
		}
		
		$this->commitMetaData(
			array(
				'elems_group' => $aElemsGroup,
				'meta_data' => $aMeta,
				'entity_id' => $oGroup->getId(),
				'meta_table' => 'geko_group_meta',
				'meta_member_table' => 'geko_group_meta_members',
				'meta_entity_id_field_name' => 'group_id',
				'meta_id_field_name' => 'gmeta_id',
				'use_mkey_id' => TRUE
			),
			$aDataVals,
			$aFileVals
		);
		
	}
	
	
	
}



