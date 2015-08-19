<?php

//
class Geko_Html_Widget
{
	
	protected static $aClassPrefixes = array( 'Geko_Html_Widget_' );
	
	
	protected $_aAtts = array();
	protected $_mValue;
	protected $_aParams = array();
	
	protected $_sWidget = '';
	
	protected $_sAttValueKey = NULL;			// if this is specified, then use as value parameter
												// eg: $mValue --> $_aAtts[ $_sAttValueKey ]
	
	
	
	
	// factory method
	public static function create( $sWidget, $aAtts = array(), $mValue = NULL, $aParams = array() ) {

		$bSetWidget = FALSE;
		
		$aClasses = array();
		
		foreach ( self::$aClassPrefixes as $sClassPrefix ) {
			$aClasses[] = sprintf( '%s%s', $sClassPrefix, Geko_Inflector::camelizeSlash( $sWidget ) );
		}
		
		if ( !$sClass = Geko_Class::existsCoalesce( $aClasses ) ) {
			$sClass = __CLASS__;
			$bSetWidget = TRUE;		
		}
		
		$oWidget = new $sClass( $aAtts, $mValue, $aParams );
		
		if ( $bSetWidget ) $oWidget->setWidget( $sWidget );
		
		return $oWidget;
	}
	
	//// static accessors
	
	//
	public static function prependClassPrefixes( $sPrefix ) {
		array_unshift( self::$aClassPrefixes, $sPrefix );
	}

	//
	public static function appendClassPrefixes( $sPrefix ) {
		self::$aClassPrefixes[] = $sPrefix;
	}
		
	//
	public static function setClassPrefixes( $aPrefixes ) {
		self::$aClassPrefixes = $aPrefixes;
	}
	
	
	
	//
	public function __construct( $aAtts = array(), $mValue = NULL, $aParams = array() ) {
		
		if ( $sAttValueKey = $this->_sAttValueKey ) {
			
			if ( !is_array( $aAtts ) ) {
				$aAttsFmt[ $sAttValueKey ] = $aAtts;			// format as assoc array
				$aAtts = $aAttsFmt;								// re-assign
			}
			
			if ( NULL === $mValue ) {
				$mValue = $aAtts[ $sAttValueKey ];
				unset( $aAtts[ $sAttValueKey ] );
			}
		
		}
		
		
		$this->_aAtts = $aAtts;
		$this->_mValue = $mValue;
		$this->_aParams = $aParams;
		
	}
	
	//
	public function setWidget( $sWidget ) {
		
		$this->_sWidget = $sWidget;
		
		return $this;
	}
	
	
	//// attribute formatting helper
	
	//
	public function formatAtt( $sKeyFlags, $mDefault = NULL, $aValueMap = array() ) {
		
		$aArgs = func_get_args();
		
		if ( is_array( $aArgs[ 0 ] ) ) {
			list( $aSubject, $sKeyFlags, $mDefault, $aValueMap ) = $aArgs;
		} else {
			list( $sKeyFlags, $mDefault, $aValueMap ) = $aArgs;
			$aSubject = $this->_aAtts;
		}
		
		if ( !$aValueMap ) $aValueMap = array(); 
		
		
		
		// parse $sKeyFlags
		list( $sKey, $sFlags ) = Geko_Array::explodeTrimEmpty( ':', $sKeyFlags );
		
		$sKey = trim( $sKey );
		
		$oFlags = new Geko_Util_Flag( $sFlags );
		
		$mAttVal = $aSubject[ $sKey ];
		
		
		//// apply formatting
		
		if ( $mCustomAttVal = $this->getCustomAttVal( $sKey, $mAttVal, $mDefault, $oFlags, $aValueMap ) ) {
			
			return $mCustomAttVal;
			
		} elseif ( $oFlags->has( 'str' ) ) {
			
			$mAttVal = trim( $mAttVal );
			
			if ( !$mAttVal ) {
				
				$mAttVal = '';
				
			} else {
				
				if ( $oFlags->has( 'lc' ) ) {
					$mAttVal = strtolower( $mAttVal );
				}
			
			}
			
		} elseif ( $oFlags->has( 'int' ) ) {
			
			$mAttVal = intval( $mAttVal );
			
		} elseif ( $oFlags->has( 'bool' ) ) {
			
			$mAttVal = intval( $mAttVal ) ? TRUE : FALSE ;
			
		} elseif ( $oFlags->has( 'array' ) ) {
			
			if ( is_object( $mAttVal ) ) {
				$mAttVal = ( array ) $mAttVal;
			} else {
				if ( !is_array( $mAttVal ) ) {
					$mAttVal = array();
				}
			}
			
		} elseif ( $oFlags->has( 'flag' ) ) {
		
			$mAttVal = new Geko_Util_Flag( $mAttVal );
		}
		
		
		//// apply value mapping
		
		// $aValueMap
		
		if ( count( $aValueMap ) > 0 ) {
			$mAttVal = $this->getValueFromMap( $mAttVal, $aValueMap );
		}
		
		
		if ( !$mAttVal ) {
			$mAttVal = $mDefault;
		}
		
		return $mAttVal;
	}
	
	
	// TO DO: potentially cache this
	public function getValueFromMap( $sValue, $aValueMap ) {
	
		// parse the value map
		
		$aValueMapFmt = array();
		
		foreach ( $aValueMap as $sValueMap ) {
			
			list( $sValueDest, $sValueAliases ) = Geko_Array::explodeTrimEmpty( ':', $sValueMap );
			
			$aValueAliases = Geko_Array::explodeTrimEmpty( '|', $sValueAliases );
			
			$aValueMapFmt[ $sValueDest ] = $sValueDest;
			
			foreach ( $aValueAliases as $sValueAlias ) {
				$aValueMapFmt[ $sValueAlias ] = $sValueDest;				
			}
		}

		if ( $sMapValue = $aValueMapFmt[ $sValue ] ) {
			return $sMapValue;
		}	
		
		return NULL;
	}
	
	// hook method to be implemented by sub-class
	public function getCustomAttVal( $sKey, $mAttVal, $mDefault, $oFlags, $aValueMap ) {
		
		return NULL;
	}
	
	// do nothing, for now
	public function formatAppendValue( $mValue ) {
		
		return $mValue;
	}
	
	
	//
	public function replacePlaceholder( $sKey, $sValue ) {
		
		$sPh = sprintf( '##%s##', $sKey );
		
		if ( FALSE !== strpos( $sValue, $sPh ) ) {
			
			// perform replacement
			$sMethod = sprintf( 'get%s', Geko_Inflector::camelize( $sKey ) );
			
			if ( method_exists( $this, $sMethod ) ) {
				$sValue = str_replace( $sPh, $this->$sMethod(), $sValue );
			}
			
		}
		
		return $sValue;
	}
	
	
	// long way: $bStriped = $oFormat->has( 'striped' ) || $this->formatAtt( 'striped:bool' );
	// shorter equivalent: $bStriped = $this->hasFlag( 'striped', $oFormat );
	public function hasFlag( $sKey, $oFlag ) {
		return $oFlag->has( $sKey ) || $this->formatAtt( sprintf( '%s:bool', $sKey ) );
	}
	
	
	
	//// main methods
	
	//
	public function get() {
		
		$oDiv = _ge( 'div' );
		
		ob_start();
		
		$aAtts = $this->_aAtts;
		$mValue = $this->_mValue;
		$aParams = $this->_aParams;
		
		print_r( $this->_sWidget );
		print_r( $aAtts );
		print_r( $mValue );
		print_r( $aParams );
		
		$sOut = ob_get_contents();
		ob_end_clean();
		
		$oDiv->append( $sOut );
		
		return $oDiv;
	}
	
	//
	public function output() {
		echo strval( $this->get() );
	}
	
}


