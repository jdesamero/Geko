<?php

//
class Geko_Geography extends Geko_Singleton_Abstract
{
	
	protected $_bInitDb = FALSE;
	
	protected $_sTableName = '';
	
	
	
	//// initialization
	
	//
	public function start() {
		
		parent::start();
		
		$oGeoXml = Geko_Geography_Xml::getInstance();
		
		Geko_Once::run( 'geko_geography_load_xml_data', array( $oGeoXml, 'loadData' ) );
		
	}
	
	
	//
	public function initDb() {
		
		if (
			( FALSE == $this->_bInitDb ) && 
			( $oDb = Geko::get( 'db' ) )
		) {
			
			$this->runInitDb( $oDb );
			
			$this->_bInitDb = TRUE;
		}
		
		return $this;
	}
	
	
	// hook method
	public function runInitDb( $oDb ) {
	
	}
	
	
	//
	public function resetTable() {
	
		if (
			( $this->_sTableName ) && 
			( $oDb = Geko::get( 'db' ) )
		) {
			
			$oDb->query( sprintf( 'DROP TABLE %s', $this->_sTableName ) );

			$this->runInitDb( $oDb );
			
		}
		
		return $this;
	}
	
	
	
	//// accessors
	
	//
	public function getTableName() {
		return $this->_sTableName;
	}
	
	
	
	//// db dependent
	
	//
	public function _getDbId( $sCodeOrName, $sPropName, $iFldDbId, $fnDataCb ) {
		
		$this->initDb();
		
		$iDbId = NULL;
		
		if ( $oDb = Geko::get( 'db' ) ) {
			
			$sCode = $this->getCodeFromValue( $sCodeOrName );
			
			$aProp =& $this->$sPropName;
			
			if (
				( $aRow = $aProp[ $sCode ] ) &&
				( !$iDbId = $aRow[ $iFldDbId ] )
			) {
				
				$aData = call_user_func( $fnDataCb, $aRow, $sCode );
				
				$oDb->insert( $this->_sTableName, $aData );
				
				$iDbId = $oDb->lastInsertId();
				
				// track the id
				$aProp[ $sCode ][ $iFldDbId ] = $iDbId;
			}
			
		}
		
		return $iDbId;
	}
	
	//
	public function _populateDbTable( $aCodes, $sGetItemsMethod, $sGetDbIdMethod ) {
		
		if ( NULL === $aCodes ) {
			$aCodes = array_keys( $this->$sGetItemsMethod() );
		}
		
		//
		foreach ( $aCodes as $sCode ) {
			$this->$sGetDbIdMethod( $sCode );
		}
		
		return $this;	
	}
	
	
	
	
	//// helpers
	
	//
	public function normalize( $sValue ) {
		return strtoupper( trim( $sValue ) );
	}
	
	
	//
	public function getCodeFromValue( $sCodeOrName ) {

		$sCodeOrName = $this->normalize( $sCodeOrName );
		
		if ( is_array( $this->_aNameAbbrHash ) ) {
			
			if ( !$sCode = $this->_aNameAbbrHash[ $sCodeOrName ] ) {
				$sCode = $sCodeOrName;
			}
			
			return $sCode;
		}
		
		return $sCodeOrName;
	}
	

}

