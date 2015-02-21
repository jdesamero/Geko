<?php

// based on CakePHP's Inflector

class Geko_Inflector
{
	
	////// static inflection properties
	
	// pluralization inflection rules
	private static $pluralRules = array(
		'pluralRules' => array(
			'/(s)tatus$/i' => '\1\2tatuses',
			'/(quiz)$/i' => '\1zes',
			'/^(ox)$/i' => '\1\2en',						// ox
			'/([m|l])ouse$/i' => '\1ice',					// mouse, louse
			'/(matr|vert|ind)(ix|ex)$/i'  => '\1ices',		// matrix, vertex, index
			'/(x|ch|ss|sh)$/i' => '\1es', 					// search, switch, fix, box, process, address
			'/([^aeiouy]|qu)y$/i' => '\1ies',				// query, ability, agency
			'/(hive)$/i' => '\1s',							// archive, hive
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',		// half, safe, wife
			'/sis$/i' => 'ses',								// basis, diagnosis
			'/([ti])um$/i' => '\1a',						// datum, medium
			'/(p)erson$/i' => '\1eople',					// person, salesperson
			'/(m)an$/i' => '\1en',							// man, woman, spokesman
			'/(c)hild$/i' => '\1hildren',					// child
			'/(buffal|tomat)o$/i' => '\1\2oes',				// buffalo, tomato
			'/us$/' => 'uses',								// us
			'/(alias)/i' => '\1es',							// alias
			'/(octop|vir)us$/i' => '\1i',					// octopus, virus - virus has no defined plural (according to Latin/dictionary.com), but viri is better than viruses/viruss
			'/(ax|cri|test)is$/i' => '\1es',				// axis, crisis
			'/s$/' => 's',									// no change (compatibility)
			'/$/' => 's'
		),
		'uninflected' => array(
			'.*[nrlm]ese',
			'.*deer',
			'.*fish',
			'.*measles',
			'.*ois',
			'.*pox',
			'.*sheep',
			'Amoyese',
			'bison',
			'Borghese',
			'bream',
			'breeches',
			'britches',
			'buffalo',
			'cantus',
			'carp',
			'chassis',
			'clippers',
			'cod',
			'coitus',
			'Congoese',
			'contretemps',
			'corps',
			'debris',
			'diabetes',
			'djinn',
			'eland',
			'elk',
			'equipment',
			'Faroese',
			'flounder',
			'Foochowese',
			'gallows',
			'Genevese',
			'Genoese',
			'Gilbertese',
			'graffiti',
			'headquarters',
			'herpes',
			'hijinks',
			'Hottentotese',
			'information',
			'innings',
			'jackanapes',
			'Kiplingese',
			'Kongoese',
			'Lucchese',
			'mackerel',
			'Maltese',
			'mews',
			'moose',
			'mumps',
			'Nankingese',
			'news',
			'nexus',
			'Niasese',
			'Pekingese',
			'Piedmontese',
			'pincers',
			'Pistoiese',
			'pliers',
			'Portuguese',
			'proceedings',
			'rabies',
			'rice',
			'rhinoceros',
			'salmon',
			'Sarawakese',
			'scissors',
			'sea[- ]bass',
			'series',
			'Shavese',
			'shears',
			'siemens',
			'species',
			'swine',
			'testes',
			'trousers',
			'trout',
			'tuna',
			'Vermontese',
			'Wenchowese',
			'whiting',
			'wildebeest',
			'Yengeese'
		),
		'irregular' => array(
			'atlas' => 'atlases',
			'beef' => 'beefs',
			'brother' => 'brothers',
			'child' => 'children',
			'corpus' => 'corpuses',
			'cow' => 'cows',
			'ganglion' => 'ganglions',
			'genie' => 'genies',
			'genus' => 'genera',
			'graffito' => 'graffiti',
			'hoof' => 'hoofs',
			'loaf' => 'loaves',
			'man' => 'men',
			'menu' => 'menus',
			'money' => 'monies',
			'mongoose' => 'mongooses',
			'move' => 'moves',
			'mythos' => 'mythoi',
			'numen' => 'numina',
			'occiput' => 'occiputs',
			'octopus' => 'octopuses',
			'opus' => 'opuses',
			'ox' => 'oxen',
			'penis' => 'penises',
			'person' => 'people',
			'sex' => 'sexes',
			'soliloquy' => 'soliloquies',
			'testis' => 'testes',
			'trilby' => 'trilbys',
			'turf' => 'turfs'
		)
	);
	
	private static $pluralized = array();
	
	
	// singularization
	private static $singularRules = array(
		'singularRules' => array(
			'/(s)tatuses$/i' => '\1\2tatus',
			'/(quiz)zes$/i' => '\\1',
			'/(matr)ices$/i' => '\1ix',
			'/(vert|ind)ices$/i' => '\1ex',
			'/^(ox)en/i' => '\1',
			'/(alias)es$/i' => '\1',
			'/([octop|vir])i$/i' => '\1us',
			'/(cris|ax|test)es$/i' => '\1is',
			'/(shoe)s$/i' => '\1',
			'/(o)es$/i' => '\1',
			'/ouses$/' => 'ouse',
			'/uses$/' => 'us',
			'/([m|l])ice$/i' => '\1ouse',
			'/(x|ch|ss|sh)es$/i' => '\1',
			'/(m)ovies$/i' => '\1\2ovie',
			'/(s)eries$/i' => '\1\2eries',
			'/([^aeiouy]|qu)ies$/i' => '\1y',
			'/([lr])ves$/i' => '\1f',
			'/(tive)s$/i' => '\1',
			'/(hive)s$/i' => '\1',
			'/(drive)s$/i' => '\1',
			'/([^f])ves$/i' => '\1fe',
			'/(^analy)ses$/i' => '\1sis',
			'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
			'/([ti])a$/i' => '\1um',
			'/(p)eople$/i' => '\1\2erson',
			'/(m)en$/i' => '\1an',
			'/(c)hildren$/i' => '\1\2hild',
			'/(n)ews$/i' => '\1\2ews',
			'/s$/i' => ''
		),
		'uninflected' => array(
			'.*[nrlm]ese',
			'.*deer',
			'.*fish',
			'.*measles',
			'.*ois',
			'.*pox',
			'.*sheep',
			'.*us',
			'.*ss',
			'Amoyese',
			'bison',
			'Borghese',
			'bream',
			'breeches',
			'britches',
			'buffalo',
			'cantus',
			'carp',
			'chassis',
			'clippers',
			'cod',
			'coitus',
			'Congoese',
			'contretemps',
			'corps',
			'debris',
			'diabetes',
			'djinn',
			'eland',
			'elk',
			'equipment',
			'Faroese',
			'flounder',
			'Foochowese',
			'gallows',
			'Genevese',
			'Genoese',
			'Gilbertese',
			'graffiti',
			'headquarters',
			'herpes',
			'hijinks',
			'Hottentotese',
			'information',
			'innings',
			'jackanapes',
			'Kiplingese',
			'Kongoese',
			'Lucchese',
			'mackerel',
			'Maltese',
			'mews',
			'moose',
			'mumps',
			'Nankingese',
			'news',
			'nexus',
			'Niasese',
			'Pekingese',
			'Piedmontese',
			'pincers',
			'Pistoiese',
			'pliers',
			'Portuguese',
			'proceedings',
			'rabies',
			'rice',
			'rhinoceros',
			'salmon',
			'Sarawakese',
			'scissors',
			'sea[- ]bass',
			'series',
			'Shavese',
			'shears',
			'siemens',
			'species',
			'swine',
			'testes',
			'trousers',
			'trout',
			'tuna',
			'Vermontese',
			'Wenchowese',
			'whiting',
			'wildebeest',
			'Yengeese'
		),
		'irregular' => array(
         	'atlases' => 'atlas',
			'beefs' => 'beef',
			'brothers' => 'brother',
			'children' => 'child',
			'corpuses' => 'corpus',
			'cows' => 'cow',
			'ganglions' => 'ganglion',
			'genies' => 'genie',
			'genera' => 'genus',
			'graffiti' => 'graffito',
			'hoofs' => 'hoof',
			'loaves' => 'loaf',
			'men' => 'man',
			'menus' => 'menu',
			'monies' => 'money',
			'mongooses' => 'mongoose',
			'moves' => 'move',
			'mythoi' => 'mythos',
			'numina' => 'numen',
			'occiputs' => 'occiput',
			'octopuses' => 'octopus',
			'opuses' => 'opus',
			'oxen' => 'ox',
			'penises' => 'penis',
			'people' => 'person',
			'sexes' => 'sex',
			'soliloquies' => 'soliloquy',
			'testes' => 'testis',
			'trilbys' => 'trilby',
			'turfs' => 'turf'
		)
	);
	
	private static $singularized = array();
	
	
	
	// prevent instantiation
	private function __construct() {
		// do nothing
	}
	
	
	
	
	////// inflection methods
	
	//
	public static function pluralize( $word ) {
	
		if ( isset( self::$pluralized[ $word ] ) ) {
			return self::$pluralized[ $word ];
		}
		
		extract( self::$pluralRules );
		
		if ( !isset( $regexUninflected ) || !isset( $regexIrregular ) ) {
			$regexUninflected = self::_enclose( join( '|', $uninflected ) );
			$regexIrregular = self::_enclose( join( '|', array_keys( $irregular ) ) );
			self::$pluralRules[ 'regexUninflected' ] = $regexUninflected;
			self::$pluralRules[ 'regexIrregular' ] = $regexIrregular;
		}
		
		if ( preg_match( sprintf( '/(.*)\\b(%s)$/i', $regexIrregular ), $word, $regs ) ) {
			self::$pluralized[ $word ] = sprintf( '%s%s', $regs[ 1 ], $irregular[ strtolower( $regs[ 2 ] ) ] );
			return self::$pluralized[ $word ];
		}
		
		if ( preg_match( sprintf( '/^(%s)$/i', $regexUninflected ), $word, $regs ) ) {
			self::$pluralized[ $word ] = $word;
			return $word;
		}
		
		foreach( $pluralRules as $rule => $replacement ) {
			if ( preg_match( $rule, $word ) ) {
				self::$pluralized[ $word ] = preg_replace( $rule, $replacement, $word );
				return self::$pluralized[ $word ];
			}
		}
		
		self::$pluralized[ $word ] = $word;
		return $word;
	}
	
	
	//
	public static function singularize( $word ) {
		
		if ( isset( self::$singularized[ $word ] ) ) {
			return self::$singularized[ $word ];
		}
		
		extract( self::$singularRules );
		
		if ( !isset( $regexUninflected ) || !isset( $regexIrregular ) ) {
			$regexUninflected = self::_enclose( join( '|', $uninflected ) );
			$regexIrregular = self::_enclose( join( '|', array_keys( $irregular ) ) );
			self::$singularRules[ 'regexUninflected' ] = $regexUninflected;
			self::$singularRules[ 'regexIrregular' ] = $regexIrregular;
		}
		
		if ( preg_match( sprintf( '/(.*)\\b(%s)$/i', $regexIrregular ), $word, $regs ) ) {
			self::$singularized[ $word ] = sprintf( '%s%s', $regs[ 1 ], $irregular[ strtolower( $regs[ 2 ] ) ] );
			return self::$singularized[ $word ];
		}
		
		if ( preg_match( sprintf( '/^(%s)$/i', $regexUninflected ), $word, $regs ) ) {
			self::$singularized[ $word ] = $word;
			return $word;
		}
		
		foreach( $singularRules as $rule => $replacement ) {
			if ( preg_match( $rule, $word ) ) {
				self::$singularized[$word] = preg_replace( $rule, $replacement, $word );
				return self::$singularized[ $word ];
			}
		}
		
		self::$singularized[ $word ] = $word;
		return $word;
	}
	
	//
	public static function camelize( $lowerCaseAndUnderscoredWord ) {
		$replace = str_replace(
			' ', '', ucwords( str_replace( '_', ' ', $lowerCaseAndUnderscoredWord ) )
		);		
		return $replace;
	}
	
	// basically, convert something like: some_class/sub_item
	// to: SomeClass_SubItem
	public static function camelizeSlash( $sValue ) {
		
		$sValue = self::camelize( $sValue );
		
		return preg_replace_callback( '/\/([a-z])/', function( $aMatches ) {
			return sprintf( '_%s', strtoupper( $aMatches[ 1 ] ) );
		}, $sValue );
	}
	
	//
	public static function underscore( $camelCasedWord ) {
		$replace = strtolower( preg_replace( '/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord ) );
		$replace = preg_replace( '/[\s]+/', '_', $replace );
		return $replace;
	}
	
	//
	public static function humanize( $lowerCaseAndUnderscoredWord ) {
		$replace = ucwords( str_replace( '_', ' ', $lowerCaseAndUnderscoredWord ) );
		return $replace;
	}
	
	//
	public static function tableize( $className ) {
		$replace = self::pluralize( self::underscore( $className ) );
		return $replace;
	}
	
	//
	public static function classify( $tableName ) {
		$replace = self::camelize( self::singularize( $tableName ) );
		return $replace;
	}
	
	//
	public static function variable( $string ) {
		$string = self::camelize( self::underscore( $string ) );
		$replace = strtolower( substr( $string, 0, 1 ) );
		$variable = preg_replace( '/\\w/', $replace, $string, 1 );
		return $variable;
	}
	
	// ala Wordpress slug
	public static function sanitize( $sString ) {
		
		$sRet = strtolower( $sString );
		$sRet = preg_replace( '/[^a-z0-9-]/', '-', $sRet );
		
		return $sRet;
	}
	
	//
	protected static function _enclose( $string ) {
		return sprintf( '(?:%s)', $string );
	}
	
	
}

