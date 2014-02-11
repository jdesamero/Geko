<?php

//
class Geko_Geography_Country
{
	public static $aContinents = array(
		'NA' => array(
			'title' => 'North American Countries',
			'name' => 'North America',
			'countries' => array(
				'CA' => 'Canada',
				'US' => 'United States',
				'AI' => 'Anguilla',
				'AG' => 'Antigua and Barbuda',
				'AW' => 'Aruba',
				'BS' => 'Bahamas',
				'BB' => 'Barbados',
				'BZ' => 'Belize',
				'BM' => 'Bermuda',
				'KY' => 'Cayman Islands',
				'CR' => 'Costa Rica',
				'CU' => 'Cuba',
				'DM' => 'Dominica',
				'DO' => 'Dominican Republic',
				'SV' => 'El Salvador',
				'GL' => 'Greenland',
				'GD' => 'Grenada',
				'GP' => 'Guadeloupe',
				'GT' => 'Guatemala',
				'HT' => 'Haiti',
				'HN' => 'Honduras',
				'JM' => 'Jamaica',
				'MQ' => 'Martinique',
				'MX' => 'Mexico',
				'MS' => 'Montserrat',
				'AN' => 'Netherlands Antilles',
				'NI' => 'Nicaragua',
				'PA' => 'Panama',
				'PR' => 'Puerto Rico',
				'KN' => 'Saint Kitts and Nevis',
				'LC' => 'Saint Lucia',
				'VC' => 'Saint Vincent and the Grenadines',
				'PM' => 'St. Pierre and Miquelon',
				'TT' => 'Trinidad and Tobago',
				'TC' => 'Turks and Caicos Islands',
				'VG' => 'Virgin Islands (British)',
				'VI' => 'Virgin Islands (U.S.)'
			)
		),
		'EU' => array(
			'title' => 'European Countries',
			'name' => 'Europe',
			'countries' => array(
				'GB' => 'United Kingdom',
				'AL' => 'Albania',
				'AD' => 'Andorra',
				'AM' => 'Armenia',
				'AT' => 'Austria',
				'AZ' => 'Azerbaijan',
				'BY' => 'Belarus',
				'BE' => 'Belgium',
				'BA' => 'Bosnia and Herzegowina',
				'BG' => 'Bulgaria',
				'HR' => 'Croatia (Hrvatska)',
				'CY' => 'Cyprus',
				'CZ' => 'Czech Republic',
				'DK' => 'Denmark',
				'EE' => 'Estonia',
				'FO' => 'Faroe Islands',
				'FI' => 'Finland',
				'FR' => 'France',
				'FX' => 'France, Metropolitan',
				'GE' => 'Georgia',
				'DE' => 'Germany',
				'GI' => 'Gibraltar',
				'GR' => 'Greece',
				'VA' => 'Holy See (Vatican City State)',
				'HU' => 'Hungary',
				'IS' => 'Iceland',
				'IE' => 'Ireland',
				'IT' => 'Italy',
				'KZ' => 'Kazakhstan',
				'LV' => 'Latvia',
				'LI' => 'Liechtenstein',
				'LT' => 'Lithuania',
				'LU' => 'Luxembourg',
				'MK' => 'Macedonia, The Former Yugoslav Republic of',
				'MT' => 'Malta',
				'MD' => 'Moldova, Republic of',
				'MC' => 'Monaco',
				'NL' => 'Netherlands',
				'NO' => 'Norway',
				'PL' => 'Poland',
				'PT' => 'Portugal',
				'RO' => 'Romania',
				'RU' => 'Russian Federation',
				'SM' => 'San Marino',
				'SK' => 'Slovakia (Slovak Republic)',
				'SI' => 'Slovenia',
				'ES' => 'Spain',
				'SJ' => 'Svalbard and Jan Mayen Islands',
				'SE' => 'Sweden',
				'CH' => 'Switzerland',
				'TR' => 'Turkey',
				'UA' => 'Ukraine',
				'YU' => 'Yugoslavia'
			)
		),
		'OC' => array(
			'title' => 'Oceanian Countries',
			'name' => 'Oceania',
			'countries' => array(
				'AU' => 'Australia',
				'AS' => 'American Samoa',
				'CK' => 'Cook Islands',
				'FJ' => 'Fiji',
				'PF' => 'French Polynesia',
				'GU' => 'Guam',
				'KI' => 'Kiribati',
				'MH' => 'Marshall Islands',
				'FM' => 'Micronesia, Federated States of',
				'NR' => 'Nauru',
				'NC' => 'New Caledonia',
				'NZ' => 'New Zealand',
				'NU' => 'Niue',
				'NF' => 'Norfolk Island',
				'MP' => 'Northern Mariana Islands',
				'PW' => 'Palau',
				'PG' => 'Papua New Guinea',
				'PN' => 'Pitcairn',
				'WS' => 'Samoa',
				'SB' => 'Solomon Islands',
				'TK' => 'Tokelau',
				'TO' => 'Tonga',
				'TV' => 'Tuvalu',
				'UM' => 'United States Minor Outlying Islands',
				'VU' => 'Vanuatu',
				'WF' => 'Wallis and Futuna Islands'
			)
		),
		'AS' => array(
			'title' => 'Asian Countries',
			'name' => 'Asia',
			'countries' => array(
				'AF' => 'Afghanistan',
				'BH' => 'Bahrain',
				'BD' => 'Bangladesh',
				'BT' => 'Bhutan',
				'IO' => 'British Indian Ocean Territory',
				'BN' => 'Brunei Darussalam',
				'KH' => 'Cambodia',
				'CN' => 'China',
				'CX' => 'Christmas Island',
				'CC' => 'Cocos (Keeling) Islands',
				'TP' => 'East Timor',
				'HK' => 'Hong Kong',
				'IN' => 'India',
				'ID' => 'Indonesia',
				'IR' => 'Iran (Islamic Republic of)',
				'IQ' => 'Iraq',
				'IL' => 'Israel',
				'JP' => 'Japan',
				'JO' => 'Jordan',
				'KP' => "Korea, Democratic People's Republic of",
				'KR' => 'Korea, Republic of',
				'KW' => 'Kuwait',
				'KG' => 'Kyrgyzstan',
				'LA' => "Lao People's Democratic Republic",
				'LB' => 'Lebanon',
				'MO' => 'Macau',
				'MY' => 'Malaysia',
				'MV' => 'Maldives',
				'MN' => 'Mongolia',
				'MM' => 'Myanmar',
				'NP' => 'Nepal',
				'OM' => 'Oman',
				'PK' => 'Pakistan',
				'PH' => 'Philippines',
				'QA' => 'Qatar',
				'SA' => 'Saudi Arabia',
				'SG' => 'Singapore',
				'LK' => 'Sri Lanka',
				'SY' => 'Syrian Arab Republic',
				'TW' => 'Taiwan, Province of China',
				'TJ' => 'Tajikistan',
				'TH' => 'Thailand',
				'TM' => 'Turkmenistan',
				'AE' => 'United Arab Emirates',
				'UZ' => 'Uzbekistan',
				'VN' => 'Viet Nam',
				'YE' => 'Yemen'
			)
		),
		'AF' => array(
			'title' => 'African Countries',
			'name' => 'Africa',
			'countries' => array(
				'DZ' => 'Algeria',
				'AO' => 'Angola',
				'BJ' => 'Benin',
				'BW' => 'Botswana',
				'BF' => 'Burkina Faso',
				'BI' => 'Burundi',
				'CM' => 'Cameroon',
				'CV' => 'Cape Verde',
				'CF' => 'Central African Republic',
				'TD' => 'Chad',
				'KM' => 'Comoros',
				'CG' => 'Congo',
				'CD' => 'Congo, the Democratic Republic of the',
				'CI' => "Cote d'Ivoire",
				'DJ' => 'Djibouti',
				'EG' => 'Egypt',
				'GQ' => 'Equatorial Guinea',
				'ER' => 'Eritrea',
				'ET' => 'Ethiopia',
				'GA' => 'Gabon',
				'GM' => 'Gambia',
				'GH' => 'Ghana',
				'GN' => 'Guinea',
				'GW' => 'Guinea-Bissau',
				'KE' => 'Kenya',
				'LS' => 'Lesotho',
				'LR' => 'Liberia',
				'LY' => 'Libyan Arab Jamahiriya',
				'MG' => 'Madagascar',
				'MW' => 'Malawi',
				'ML' => 'Mali',
				'MR' => 'Mauritania',
				'MU' => 'Mauritius',
				'YT' => 'Mayotte',
				'MA' => 'Morocco',
				'MZ' => 'Mozambique',
				'NA' => 'Namibia',
				'NE' => 'Niger',
				'NG' => 'Nigeria',
				'RE' => 'Reunion',
				'RW' => 'Rwanda',
				'ST' => 'Sao Tome and Principe',
				'SN' => 'Senegal',
				'SC' => 'Seychelles',
				'SL' => 'Sierra Leone',
				'SO' => 'Somalia',
				'ZA' => 'South Africa',
				'SH' => 'St. Helena',
				'SD' => 'Sudan',
				'SZ' => 'Swaziland',
				'TZ' => 'Tanzania, United Republic of',
				'TG' => 'Togo',
				'TN' => 'Tunisia',
				'UG' => 'Uganda',
				'EH' => 'Western Sahara',
				'ZM' => 'Zambia',
				'ZW' => 'Zimbabwe'
			)
		),
		'SA' => array(
			'title' => 'South American Countries',
			'name' => 'South America',
			'countries' => array(
				'AR' => 'Argentina',
				'BO' => 'Bolivia',
				'BR' => 'Brazil',
				'CL' => 'Chile',
				'CO' => 'Colombia',
				'EC' => 'Ecuador',
				'FK' => 'Falkland Islands (Malvinas)',
				'GF' => 'French Guiana',
				'GY' => 'Guyana',
				'PY' => 'Paraguay',
				'PE' => 'Peru',
				'SR' => 'Suriname',
				'UY' => 'Uruguay',
				'VE' => 'Venezuela'
			)
		),
		'AN' => array(
			'title' => 'Antarctican Territories',
			'name' => 'Antarctica',
			'countries' => array(
				'AQ' => 'Antarctica',
				'BV' => 'Bouvet Island',
				'TF' => 'French Southern Territories',
				'HM' => 'Heard and Mc Donald Islands',
				'GS' => 'South Georgia and the South Sandwich Islands'
			)
		)
	);
	
	//
	public static $aCountries = array();
	public static $aCountryContinentHash = array();
	
	// hard coded exceptions
	public static $aCountryCodeHash = array(
		'Russia' => 'RU',
		'Slovenia' => 'SI',
		'Slovakia' => 'SK',
		'USA' => 'US'
	);
	
	
	
	// three letter country code
	public static $a3LetterCode = array(
		'AST' => 'AT',
		'CAN' => 'CA',
		'CHE' => 'CH',
		'CZE' => 'CZ',
		'FIN' => 'FI',
		'LVA' => 'LV',
		'NOR' => 'NO',
		'RUS' => 'RU',
		'SLO' => 'SI',
		'SVK' => 'SK',
		'SWE' => 'SE',
		'USA' => 'US'
	);
	
	
	public static $aCountryNameVariations = array(
		1 => array(
			'VN' => 'Vietnam'
		)
	);
	
	
	
	//
	private static function init() {
		
		if ( 0 == count( self::$aCountries ) ) {
			
			$aNorm = array();
			
			foreach ( self::$aCountryCodeHash as $sKey => $sValue ) {
				$aNorm[ strtoupper( $sKey ) ] = $sValue;
			}
			
			self::$aCountryCodeHash = $aNorm;
			
			foreach ( self::$aContinents as $sContinentCode => $aContinent ) {
				
				self::$aCountries = array_merge( self::$aCountries, $aContinent[ 'countries' ] );
				
				foreach ( $aContinent[ 'countries' ] as $sCountryCode => $sCountryName ) {
					
					$sCountryNameNorm = strtoupper( $sCountryName );
					
					self::$aCountryContinentHash[ $sCountryCode ] = $sContinentCode;
					self::$aCountryContinentHash[ $sCountryNameNorm ] = $sContinentCode;
					self::$aCountryCodeHash[ $sCountryNameNorm ] = $sCountryCode;
					
				}
			}
		}
	}

	
	//
	public static function get() {
		return self::$aContinents;
	}

	//
	public static function getCountries() {
		
		self::init();
		
		return self::$aCountries;
	}
	
	//
	public static function getCountryNameFromCountryCode( $sCountryCode, $iVariation = 0 ) {
		
		self::init();
		
		if ( $iVariation ) {
			$sRet = self::$aCountryNameVariations[ $iVariation ][ $sCountryCode ];
		}
		
		if ( !$sRet ) {
			$sCountryCodeNormalize = strtoupper( trim( $sCountryCode ) );		// normalize
			$sRet = self::$aCountries[ $sCountryCodeNormalize ];
		}
		
		return ( '' == $sRet ) ? $sCountryCode : $sRet;
	}
	
	
	//
	public static function getCountryCodeFromCountryName( $sCountryName ) {
		
		self::init();
		
		$sCountryNameNormalize = strtoupper( trim( $sCountryName ) );		// normalize
		$sRet = self::$aCountryCodeHash[ $sCountryNameNormalize ];
		
		return ( '' == $sRet ) ? $sCountryName : $sRet;
	}
	
	
	// alias
	public static function getNameFromCode( $sCode ) {
		return self::getCountryNameFromCountryCode( $sCode );
	}
	
	// $sCountry could be code or name
	public static function getContinentCodeFromCountry( $sCountry ) {
		
		self::init();
		
		$sCountry = strtoupper( trim( $sCountry ) );						// normalize
		
		return self::$aCountryContinentHash[ $sCountry ];
	}

	// $sState could be code or name
	public static function getContinentNameFromCountry( $sCountry ) {
		
		self::init();
		
		return self::$aContinents[ self::getContinentCodeFromCountry( $sCountry ) ][ 'name' ];
	}
	
	
	//
	public static function getCountryCodeFrom3Letter( $s3Letter ) {
		return self::$a3LetterCode[ strtoupper( $s3Letter ) ];
	}
	
	
	
}


