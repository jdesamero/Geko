<?php

//
class Geko_Geography_CountryState
{
	public static $aCountries = array(
		'CA' => array(
			'title' => 'Canadian Provinces',
			'name' => 'Canada',
			'states' => array(
				'AB' => 'Alberta',
				'BC' => 'British Columbia',
				'MB' => 'Manitoba',
				'NB' => 'New Brunswick',
				'NF' => 'Newfoundland',
				'NT' => 'Northwest Territories',
				'NS' => 'Nova Scotia',
				'NU' => 'Nunavut',
				'ON' => 'Ontario',
				'PE' => 'Prince Edward Island',
				'QC' => 'Quebec',
				'SK' => 'Saskatchewan',
				'YT' => 'Yukon Territory'
			)
		),
		'US' => array(
			'title' => 'U.S. States',
			'name' => 'USA',
			'states' => array(
				'AK' => 'Alaska',
				'AL' => 'Alabama',
				'AR' => 'Arkansas',
				'AZ' => 'Arizona',
				'CA' => 'California',
				'CO' => 'Colorado',
				'CT' => 'Connecticut',
				'DC' => 'District of Columbia',
				'DE' => 'Delaware',
				'FL' => 'Florida',
				'GA' => 'Georgia',
				'HI' => 'Hawaii',
				'IA' => 'Iowa',
				'ID' => 'Idaho',
				'IL' => 'Illinois',
				'IN' => 'Indiana',
				'KS' => 'Kansas',
				'KY' => 'Kentucky',
				'LA' => 'Louisiana',
				'MA' => 'Massachusetts',
				'MD' => 'Maryland',
				'ME' => 'Maine',
				'MI' => 'Michigan',
				'MN' => 'Minnesota',
				'MO' => 'Missouri',
				'MS' => 'Mississippi',
				'MT' => 'Montana',
				'NC' => 'North Carolina',
				'ND' => 'North Dakota',
				'NE' => 'Nebraska',
				'NH' => 'New Hampshire',
				'NJ' => 'New Jersey',
				'NM' => 'New Mexico',
				'NV' => 'Nevada',
				'NY' => 'New York',
				'OH' => 'Ohio',
				'OK' => 'Oklahoma',
				'OR' => 'Oregon',
				'PA' => 'Pennsylvania',
				'PR' => 'Puerto Rico',
				'RI' => 'Rhode Island',
				'SC' => 'South Carolina',
				'SD' => 'South Dakota',
				'TN' => 'Tennessee',
				'TX' => 'Texas',
				'UT' => 'Utah',
				'VA' => 'Virginia',
				'VT' => 'Vermont',
				'WA' => 'Washington',
				'WI' => 'Wisconsin',
				'WV' => 'West Virginia',
				'WY' => 'Wyoming'				
			)
		),
		'AU' => array(
			'title' => 'Australian States',
			'name' => 'Australia',
			'states' => array(
				'ACT' => 'Australian Capital Territory',
				'NSW' => 'New South Wales',
				'NT' => 'Northern Territory',
				'QLD' => 'Queensland',
				'SA' => 'South Australia',
				'TAS' => 'Tasmania',
				'VIC' => 'Victoria',
				'WA' => 'Western Australia'
			)
		)
	);
	
	//
	public static $aStates = array();
	public static $aStateCountryHash = array();
	
	
	
	//// methods
	
	//
	private static function init() {
		if ( 0 == count( self::$aStates ) ) {
			foreach ( self::$aCountries as $sCountryCode => $aCountry ) {
				self::$aStates = array_merge( self::$aStates, $aCountry[ 'states' ] );
				foreach ( $aCountry[ 'states' ] as $sStateCode => $sStateName ) {
					self::$aStateCountryHash[ $sStateCode ] = $sCountryCode;
					self::$aStateCountryHash[ strtoupper( $sStateName ) ] = $sCountryCode;
				}
			}
		}
	}
	
	
	//
	public static function get() {
		return self::$aCountries;
	}
	
	//
	public static function getStates() {
		self::init();
		return self::$aStates;
	}
	
	//
	public static function getStateNameFromStateCode( $sStateCode ) {
		self::init();
		$sStateCodeNormalize = strtoupper( trim( $sStateCode ) );		// normalize
		$sRet = self::$aStates[ $sStateCodeNormalize ];
		return ( '' == $sRet ) ? $sStateCode : $sRet;
	}
	
	// alias
	public static function getNameFromCode( $sCode ) {
		return self::getStateNameFromStateCode( $sCode );
	}
	
	// $sState could be code or name
	public static function getCountryCodeFromState( $sState ) {
		self::init();
		$sState = strtoupper( trim( $sState ) );						// normalize
		return self::$aStateCountryHash[ $sState ];
	}

	// $sState could be code or name
	public static function getCountryNameFromState( $sState ) {
		self::init();
		return self::$aCountries[ self::getCountryCodeFromState( $sState ) ][ 'name' ];
	}
	
}


