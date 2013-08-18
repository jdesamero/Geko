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
	public function affix() {
		
		global $wpdb;
		
		$sTableName = 'geko_group_meta';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'gm' )
			->fieldBigInt( 'gmeta_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'group_id', array( 'unsgnd' ) )
			->fieldSmallInt( 'mkey_id', array( 'unsgnd' ) )
			->fieldLongText( 'meta_value' )
			->indexKey( 'group_mkey_id', array( 'group_id', 'mkey_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		$sTableName2 = 'geko_group_meta_members';
		Geko_Wp_Db::addPrefix( $sTableName2 );
		
		$oSqlTable2 = new Geko_Sql_Table();
		$oSqlTable2
			->create( $wpdb->$sTableName2, 'gmm' )
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
		
		global $wpdb;
		
		$this->createTable( $this->getPrimaryTable() );
		$this->createTable( $wpdb->geko_group_meta_members );
		
		return $this;
	}
	
	//
	public function getPrimaryTable() {
		
		if ( $this->_sInstanceClass != __CLASS__ ) {
			$oMng = Geko_Singleton_Abstract::getInstance( __CLASS__ );
			return $oMng->getPrimaryTable();
		}
		
		return parent::getPrimaryTable();
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
			return self::$aMetaCache[ $iGroupId ][ $this->getPrefixWithSep() . $sMetaKey ];
		} else {
			return self::$aMetaCache[ $iGroupId ];
		}
	}
	
	
	//// cache helpers
	
	//
	protected function setMetaCache( $iGroupId ) {
		
		if ( !isset( self::$aMetaCache[ $iGroupId ] ) ) {
			
			global $wpdb;
			
			$aFmt = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"	SELECT			g.gmeta_id,
										h.meta_key,
										g.meta_value
						FROM			$wpdb->geko_group_meta g
						LEFT JOIN		$wpdb->geko_meta_key h
							ON			h.mkey_id = g.mkey_id
						WHERE			g.group_id = %d
					",
					$iGroupId
				),
				'meta_key'
			);
			
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
	
	//
	public function delete( $oGroup ) {
		// cleanup all orphaned metadata
		global $wpdb;
		
		// meta
		$wpdb->query("
			DELETE FROM		$wpdb->geko_group_meta
			WHERE			group_id NOT IN (
				SELECT			group_id
				FROM			$wpdb->geko_groups
			)
		");
		
		// members
		$wpdb->query("
			DELETE FROM		$wpdb->geko_group_meta_members
			WHERE			gmeta_id NOT IN (
				SELECT			gmeta_id
				FROM			$wpdb->geko_group_meta
			)
		");
		
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
		
		global $wpdb;
		
		//
		$aElemsGroup = isset( $aParams[ 'elems_group' ] ) ?
			$aParams[ 'elems_group' ] : 
			$this->getElemsGroup( $oGroup, $sGroupTypeSlug )
		;
		
		if ( 'update' == $sMode ) {
			$aMeta = Geko_Wp_Db::getResultsHash(
				$wpdb->prepare(
					"	SELECT			g.gmeta_id,
										h.meta_key,
										g.meta_value
						FROM			$wpdb->geko_group_meta g
						LEFT JOIN		$wpdb->geko_meta_key h
							ON			h.mkey_id = g.mkey_id
						WHERE			g.group_id = %d
					",
					$oGroup->getId()
				),
				'meta_key'
			);
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



