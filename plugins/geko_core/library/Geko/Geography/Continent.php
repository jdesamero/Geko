<?php

//
class Geko_Geography_Continent extends Geko_Geography
{
	
	const FIELD_NAME = 1;
	const FIELD_COUNTRY_LABEL = 2;
	const FIELD_DB_ID = 3;
	
	
	protected $_aContinents = NULL;
	protected $_aNameAbbrHash = array();
	
	protected $_sTableName = '##pfx##geko_location_continent';
	
	
	
	//// initialization
	
	//
	public function start() {
		
		parent::start();
		
		//
		foreach ( $this->_aContinents as $sCode => $aRow ) {
			
			$this->_aNameAbbrHash[ $this->normalize( $aRow[ self::FIELD_NAME ] ) ] = $sCode;
		}
				
	}
	
	
	//
	public function runInitDb( $oDb ) {
		
		$this->init();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $this->_sTableName, 't' )
			->fieldTinyInt( 'continent_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'continent_name', array( 'size' => 256 ) )
			->fieldVarChar( 'continent_abbr', array( 'size' => 16 ) )
			->fieldTinyInt( 'rank', array( 'unsgnd' ) )
		;
		
		// if table was not created
		if ( !$oDb->tableCreateIfNotExists( $oSqlTable ) ) {
			
			//// populate with $this->_aContinents with existing ids
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 't.continent_id', 'id' )
				->field( 't.continent_abbr', 'abbr' )
				->from( $this->_sTableName, 't' )
			;
			
			$aRes = $oDb->fetchAll( strval( $oQuery ) );
			foreach ( $aRes as $aRow ) {
				
				$sCode = $aRow[ 'abbr' ];
				$iId = intval( $aRow[ 'id' ] );
				
				if ( $this->_aContinents[ $sCode ] ) {
					$this->_aContinents[ $sCode ][ self::FIELD_DB_ID ] = $iId;
				}
			}
			
		}			
		
	}
	
	
	
	//// accessors
	
	//
	public function get() {
		return $this->getContinents();
	}
	
	
	//
	public function getContinents() {
		
		$this->init();
		
		return $this->_aContinents;
	}
	
	//
	public function set( $aContinents ) {
		
		$this->_aContinents = $aContinents;
		
		return $this;
	}
	
	//
	public function getNameFromCode( $sCode ) {
		
		$this->init();
		
		return $this->_aContinents[ $sCode ][ self::FIELD_NAME ];
	}
	
	
	
	//// db dependent

	//
	public function getContinentId( $sCodeOrName ) {
		
		$oThis = $this;
		
		return $this->_getDbId(
			
			$sCodeOrName, '_aContinents', self::FIELD_DB_ID,
			
			function( $aRow, $sCode ) use( $oThis ) {
				
				return array(
					'continent_name' => $aRow[ $oThis::FIELD_NAME ],
					'continent_abbr' => $sCode
				);			
			}
			
		);
		
	}
	
	//
	public function populateContinentTable( $aCodes = NULL ) {
		return $this->_populateDbTable( $aCodes, 'getContinents', 'getContinentId' );
	}
	
	
	
}


