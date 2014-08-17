<?php

//
class Geko_Geography_State extends Geko_Geography
{
	
	const FIELD_NAME = 1;
	const FIELD_COUNTRY = 2;
	const FIELD_ABBR = 3;
	const FIELD_VARIATIONS = 4;
	const FIELD_LATITUDE = 5;
	const FIELD_LONGITUDE = 6;
	const FIELD_DB_ID = 7;
	
	
	
	protected $_aStates = NULL;
	protected $_aNameAbbrHash = array();
	protected $_aCountries = array();
	
	protected $_sTableName = '##pfx##geko_location_province';
	
	
	
	
	//// methods
	
	//
	public function start( $bDbDep = FALSE ) {
		
		parent::start();
		
		
		// $sCode format is XX.XX(X)
		foreach ( $this->_aStates as $sCode => $aRow ) {
			
			$sStateAbbr = $aRow[ self::FIELD_ABBR ];
			$sCountry = $aRow[ self::FIELD_COUNTRY ];
			
			$this->_aNameAbbrHash[ $this->normalize( $aRow[ self::FIELD_NAME ] ) ] = $sCode;
			
			// might be potential collision here as different countries may share
			// the same state codes
			if ( !$this->_aNameAbbrHash[ $sStateAbbr ] ) {
				$this->_aNameAbbrHash[ $sStateAbbr ] = $sCode;
			}
			
			// group country codes
			if ( !in_array( $sCountry, $this->_aCountries ) ) {
				$this->_aCountries[] = $sCountry;
			}
		}
		
	}
	
	
	//
	public function runInitDb( $oDb ) {
		
		$this->init();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $this->_sTableName, 'p' )
			->fieldInt( 'province_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'province_name', array( 'size' => 256 ) )
			->fieldVarChar( 'province_abbr', array( 'size' => 16 ) )
			->fieldFloat( 'latitude', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'longitude', array( 'size' => '10,7', 'sgnd' ) )
			->fieldInt( 'country_id', array( 'unsgnd', 'key' ) )
			->fieldInt( 'rank', array( 'unsgnd' ) )
		;
		
		// if table was not created
		if ( !$oDb->tableCreateIfNotExists( $oSqlTable ) ) {
		
			//// populate with $this->_aStates with existing ids
			
			$oGeoCoun = Geko_Geography_Country::getInstance();

			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'p.province_id', 'id' )
				->field( 'p.province_abbr', 'abbr' )
				->field( 'c.country_abbr', 'country' )
				
				->from( $this->_sTableName, 'p' )
				->joinLeft( $oGeoCoun->getTableName(), 'c' )
					->on( 'c.country_id = p.country_id' )
			;
			
			$aRes = $oDb->fetchAll( strval( $oQuery ) );
			
			foreach ( $aRes as $aRow ) {
				
				$sCode = sprintf( '%s.%s', $aRow[ 'country' ], $aRow[ 'abbr' ] );
				$iId = intval( $aRow[ 'id' ] );
				
				if ( $this->_aStates[ $sCode ] ) {
					$this->_aStates[ $sCode ][ self::FIELD_DB_ID ] = $iId;
				}
			}
			
		}
	
	}
	
	
	
	// alias of getStates()
	public function get() {
		return $this->getStates();
	}
	
	
	//
	public function set( $aStates ) {
		
		$this->_aStates = $aStates;
		
		return $this;
	}
	
	
	
	//
	public function getStates() {
		
		$this->init();
		
		return $this->_aStates;
	}
	
	//
	public function getCountries() {
		
		$this->init();
		
		return $this->_aCountries;
	}
	
	
	
	//
	public function getStateNameFromStateCode( $sStateCode ) {
		
		$this->init();
		
		$sCode = $this->getCodeFromValue( $sStateCode );

		$sRet = $this->_aStates[ $sCode ][ self::FIELD_NAME ];
		
		return ( !$sRet ) ? $sStateCode : $sRet ;
	}
	
	// alias
	public function getNameFromCode( $sCode ) {
		return $this->getStateNameFromStateCode( $sCode );
	}
	
	
	
	
	//
	public function getCountryCodeFromState( $sCodeOrName ) {
		
		$this->init();
		
		$sCode = $this->getCodeFromValue( $sCodeOrName );
		
		return $this->_aStates[ $sCode ][ self::FIELD_COUNTRY ];
	}
	
	
	
	//
	public function getCountryNameFromState( $sState ) {
		
		$sCounCode = $this->getCountryCodeFromState( $sState );
		
		$oGeoCoun = Geko_Geography_Country::getInstance();
		
		return $oGeoCoun->getCountryNameFromCountryCode( $sCounCode );
	}
	
	
	
	
	//// db dependent

	// $sCountry, $sState can be code or name
	public function getStateId( $sCodeOrName ) {
		return $this->_getDbId( $sCodeOrName, '_aStates', self::FIELD_DB_ID );
	}
	
	//
	public function populateStateTable( $aCodes = NULL ) {
		return $this->_populateDbTable( $aCodes, 'getStates', 'getStateId' );
	}
	
	//
	public function _formatDbInsertData( $aRow, $sCode ) {

		$oGeoCoun = Geko_Geography_Country::getInstance();
		
		return array(
			'province_name' => $aRow[ self::FIELD_NAME ],
			'province_abbr' => $aRow[ self::FIELD_ABBR ],
			'latitude' => $aRow[ self::FIELD_LATITUDE ],
			'longitude' => $aRow[ self::FIELD_LONGITUDE ],
			'country_id' => $oGeoCoun->getCountryId( $aRow[ self::FIELD_COUNTRY ] )
		);	
	}

	
	
}


