<?php

//
class Geko_App_Entity_Manage extends Geko_Singleton_Abstract
{
	
	protected $_sEntityIdVarName = '';
	
	protected $_aTables = array();
	protected $_sPrimaryTable = '';
	
	
	
	
	//// TO DO: consolidate with Geko_Wp_Options
	
	
	//// table functions
	
	// ensure that an <sql table object> is returned
	public function resolveTable( $mSqlTable ) {
		
		if ( is_string( $mSqlTable ) ) {
			$oSqlTable = $this->getTable( $mSqlTable );
		} elseif ( is_a( $mSqlTable, 'Geko_Sql_Table' ) ) {
			$oSqlTable = $mSqlTable;
		} else {
			$oSqlTable = NULL;
		}
		
		return $oSqlTable;
	}
	
	// ensure that a <table name> is returned
	public function resolveTableName( $mSqlTable ) {

		if ( is_string( $mSqlTable ) ) {
			$sTableName = $mSqlTable;
		} elseif ( is_a( $mSqlTable, 'Geko_Sql_Table' ) ) {
			$sTableName = $mSqlTable->getTableName();
		} else {
			$sTableName = '';
		}
		
		return $sTableName;
	}
	
	// create database table using the <sql table object> or <table name>
	public function createTable( $mSqlTable ) {
		
		$oDb = Geko_App::get( 'db' );
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			return $oDb->tableCreateIfNotExists( $oSqlTable );
		}
		
		return FALSE;
	}
	
	// register an <sql table object> into _aTables property
	// use <table name> as a key
	// if second arg is TRUE, register as _sPrimaryTable property
	public function addTable( $oSqlTable, $bPrimaryTable = TRUE, $bCreateTable = FALSE ) {
		
		$sTableName = $oSqlTable->getTableName();
		
		$this->_aTables[ $sTableName ] = $oSqlTable;
		
		if ( $bPrimaryTable ) {
			
			$this->_sPrimaryTable = $sTableName;
			
			if ( !$this->_sEntityIdVarName && ( $oPkf = $oSqlTable->getPrimaryKeyField() ) ) {
				$this->_sEntityIdVarName = $oPkf->getFieldName();
			}
		}
		
		if ( $bCreateTable ) {
			$this->createTable( $oSqlTable );
		}
		
 		return $this;
	}
	
	// get matching <table name> from _aTables
	// if <table name> is not provided, get the primary table
	public function getTable( $sTableName = '' ) {
		if ( $sTableName ) {
			return $this->_aTables[ $sTableName ];
		}
		return $this->getPrimaryTable();
	}
	
	// get _sPrimaryTable from _aTables
	public function getPrimaryTable() {
		return $this->_aTables[ $this->_sPrimaryTable ];	
	}
	
	// drop the provided <sql table object> or <table name> from the database
	public function dropTable( $mSqlTable ) {
		
		$oDb = Geko_App::get( 'db' );
				
		if ( $sTableName = $this->resolveTableName( $mSqlTable ) ) {
			$oDb->exec( sprintf( 'DROP TABLE %s', $sTableName ) );
		}
		
		return $this;
	}
	
	// drop the provided <sql table object> or <table name> from the database then create it
	public function resetTable( $mSqlTable ) {
		$this
			->dropTable( $mSqlTable )
			->createTable( $mSqlTable )
		;
		return $this;
	}
	
	// get an array of <sql table field objects> from the <sql table object>
	// wrapped as an <options field object>
	public function getTableFields( $mSqlTable ) {
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			
			return $oSqlTable->getFields( TRUE );
		}
		
		return array();
	}

	// get an array of key <sql table field objects> from the <sql table object>
	// wrapped as an <options field object>
	public function getTableKeyFields( $mSqlTable ) {
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			
			return $oSqlTable->getKeyFields( TRUE );
		}
		
		return array();
	}
	
	// get the <options field objects> of the <primary table>
	public function getPrimaryTableFields() {
		return $this->getTableFields( $this->getPrimaryTable() );
	}

	// get the key <options field objects> of the <primary table>
	public function getPrimaryTableKeyFields() {
		return $this->getTableKeyFields( $this->getPrimaryTable() );
	}
	
	// get the primary <options field object> of the given <sql table object>
	public function getTablePrimaryKeyField( $mSqlTable ) {
		
		if ( $oSqlTable = $this->resolveTable( $mSqlTable ) ) {
			
			return $oSqlTable->getPrimaryKeyField();
		}
		
		return NULL;
	}
	
	// get the primary <options field object> of the primary <sql table object>
	public function getPrimaryTablePrimaryKeyField() {
		return $this->getTablePrimaryKeyField( $this->getPrimaryTable() );
	}
	
	
	
	
	
	//// crud methods
	
	
	//
	public function formatWhere( $aWhere ) {
		
		$oDb = Geko_App::get( 'db' );
		
		$aWhereFmt = array();
		
		foreach ( $aWhere as $sKey => $sValue ) {
			$aWhereFmt[] = sprintf( "%s = '%s'", $sKey, $oDb->quote( $sValue ) );
		}
		
		return $aWhereFmt;
	}
	
	
	//
	public function insert( $aInsertData ) {
		
		$oDb = Geko_App::get( 'db' );
		
		// returns affected rows
		$iRes = $oDb->insert( $this->_sPrimaryTable, $aInsertData );
		
		if ( !$iLastInsertId = $oDb->lastInsertId() ) {
			$iLastInsertId = $trySomeOtherMethod;
		}
		
		return $iRes;
	}
	
	
	//
	public function update( $aUpdateData, $aWhere ) {
		
		$oDb = Geko_App::get( 'db' );
		
		// returns affected rows
		return $oDb->update( $this->_sPrimaryTable, $aUpdateData, $this->formatWhere( $aWhere ) );
	}
	
	
	//
	public function delete( $aWhere ) {
		
		$oDb = Geko_App::get( 'db' );
		
		// returns affected rows
		return $oDb->delete( $this->_sPrimaryTable, $this->formatWhere( $aWhere ) );
	}


}




