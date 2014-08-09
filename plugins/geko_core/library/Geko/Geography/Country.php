<?php

//
class Geko_Geography_Country extends Geko_Geography
{	
	
	const FIELD_NAME = 1;
	const FIELD_CONTINENT = 2;
	const FIELD_STATE_LABEL = 3;
	const FIELD_ALT_ABBR = 4;
	const FIELD_VARIATIONS = 5;
	const FIELD_LATITUDE = 6;
	const FIELD_LONGITUDE = 7;
	const FIELD_DB_ID = 8;
	
	
	
	protected $_sTableName = '##pfx##geko_location_country';

	protected $_aCountries = NULL;				// flat listing of countries
	protected $_aNameAbbrHash = NULL;
	protected $_aAltAbbrHash = NULL;
	
	
	protected $_aCountryNameVariations = array(
		1 => array(
			'VN' => 'Vietnam'
		)
	);
	
	
	
	//
	public function start() {
		
		parent::start();
		
		
		//
		foreach ( $this->_aCountries as $sCode => $aRow ) {
			
			$sName = $aRow[ self::FIELD_NAME ];
			$sAltAbbr = $aRow[ self::FIELD_ALT_ABBR ];
			
			$this->_aNameAbbrHash[ $this->normalize( $sName ) ] = $sCode;
			
			if ( is_array( $aVariations = $aRow[ self::FIELD_VARIATIONS ] ) ) {
				
				foreach ( $aVariations as $sVary ) {
					$this->_aNameAbbrHash[ $this->normalize( $sVary ) ] = $sCode;		
				}
			}
			
			$this->_aAltAbbrHash[ $sAltAbbr ] = $sCode;			
		}
		
	}
	
	
	//
	public function runInitDb( $oDb ) {
	
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $this->_sTableName, 'c' )
			->fieldInt( 'country_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'country_name', array( 'size' => 256 ) )
			->fieldVarChar( 'country_abbr', array( 'size' => 16 ) )
			->fieldFloat( 'latitude', array( 'size' => '10,7', 'sgnd' ) )
			->fieldFloat( 'longitude', array( 'size' => '10,7', 'sgnd' ) )
			->fieldInt( 'continent_id', array( 'unsgnd', 'key' ) )
			->fieldInt( 'rank', array( 'unsgnd' ) )
		;
		
		// if table was not created
		if ( !$oDb->tableCreateIfNotExists( $oSqlTable ) ) {
			
			//// populate with $this->_aCountries with existing ids
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				->field( 'c.country_id', 'id' )
				->field( 'c.country_abbr', 'abbr' )
				->from( $this->_sTableName, 'c' )
			;
			
			$aRes = $oDb->fetchAll( strval( $oQuery ) );
			
			foreach ( $aRes as $aRow ) {
				
				$sCode = $aRow[ 'abbr' ];
				$iId = intval( $aRow[ 'id' ] );
				
				if ( $this->_aCountries[ $sCode ] ) {
					$this->_aCountries[ $sCode ][ self::FIELD_DB_ID ] = $iId;
				}
			}
			
		}
		
	}
	
	
	//// accessors
	
	// alias of getCountries()
	public function get() {
		return $this->getCountries();
	}
	
	
	//
	public function set( $aCountries ) {
		
		$this->_aCountries = $aCountries;
		
		return $this;
	}
	
	
	
	// alias of get()
	public function getCountries() {
		
		$this->init();
		
		return $this->_aCountries;
	}
	
	
	//
	public function getAltAbbr() {
		
		$this->init();
		
		return $this->_aAltAbbrHash;	
	}
	
	// depracated alias for getAltAbbr()
	public function getThreeLetterCodes() {
		return $this->getAltAbbr();
	}
	
	
	
	// get 2 letter country codes from country names and variants
	public function getCountryCodeHash() {

		$this->init();
		
		return $this->_aNameAbbrHash;	
	}
	
	
	//
	public function getCountryNameFromCountryCode( $sCountryCode, $iVariation = 0 ) {
		
		$this->init();
		
		if ( $iVariation ) {
			$sRet = $this->_aCountryNameVariations[ $iVariation ][ $sCountryCode ];
		}
		
		if ( !$sRet ) {
			$sRet = $this->_aCountries[ $this->normalize( $sCountryCode ) ][ self::FIELD_NAME ];
		}
		
		return ( !$sRet ) ? $sCountryCode : $sRet ;
	}
	
	
	//
	public function getCountryCodeFromCountryName( $sCountryName ) {
		
		$this->init();
		
		$sCountryName = $this->normalize( $sCountryName );
		
		$sRet = $this->_aNameAbbrHash[ $sCountryName ];
		
		return ( !$sRet ) ? $sCountryName : $sRet ;
	}
	
	
	// alias
	public function getNameFromCode( $sCode ) {
		return $this->getCountryNameFromCountryCode( $sCode );
	}
	
	
	//
	public function getContinentCodeFromCountry( $sCodeOrName ) {
		
		$this->init();
		
		$sCode = $this->getCodeFromValue( $sCodeOrName );
		
		return $this->_aCountries[ $sCode ][ self::FIELD_CONTINENT ];
	}
	
	
	//
	public function getContinentNameFromCountry( $sCountry ) {
		
		$sContCode = $this->getContinentCodeFromCountry( $sCountry );
		
		$oGeoCont = Geko_Geography_Continent::getInstance();
		
		return $oGeoCont->getNameFromCode( $sContCode );;
	}
	
	
	
	//
	public function getCountryCodeFromAltAbbr( $sAltAbbr ) {
		
		$this->init();
		
		return $this->_aAltAbbrHash[ $this->normalize( $sAltAbbr ) ];
	}
	
	
	// depracated alias of getCountryCodeFromAltAbbr
	public function getCountryCodeFrom3Letter( $s3Letter ) {
		
		return $this->getCountryCodeFromAltAbbr( $s3Letter );
	}
	
	
	
	
	
	
	//// db dependent
	
	//
	public function getCountryId( $sCodeOrName ) {
		
		$this->initDb();
		
		$iDbId = NULL;
		
		if ( $oDb = Geko::get( 'db' ) ) {
			
			$sCode = $this->getCodeFromValue( $sCodeOrName );
			
			if (
				( $aRow = $this->_aCountries[ $sCode ] ) &&
				( !$iDbId = $aRow[ self::FIELD_DB_ID ] )
			) {
				
				$oGeoCont = Geko_Geography_Continent::getInstance();
				
				$aData = array(
					'country_name' => $aRow[ self::FIELD_NAME ],
					'country_abbr' => $sCode,
					'latitude' => $aRow[ self::FIELD_LATITUDE ],
					'longitude' => $aRow[ self::FIELD_LONGITUDE ],
					'continent_id' => $oGeoCont->getContinentId( $aRow[ self::FIELD_CONTINENT ] )
				);
				
				$oDb->insert( $this->_sTableName, $aData );
				
				$iDbId = $oDb->lastInsertId();				
				
				// track the id
				$this->_aCountries[ $sCode ][ self::FIELD_DB_ID ] = $iDbId;
			}
		
		}
		
		return $iDbId;
	}
	
	//
	public function populateCountryTable( $aCodes ) {
		
		if ( NULL === $aCodes ) {
			$aCodes = array_keys( $this->getCountries() );
		}
		
		//
		foreach ( $aCodes as $sCode ) {
			$this->getCountryId( $sCode );
		}
		
		return $this;
	}

	
	
}


