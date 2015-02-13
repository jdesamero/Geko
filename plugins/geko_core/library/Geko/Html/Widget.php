<?php

//
class Geko_Html_Widget
{
	
	protected static $aClassPrefixes = array( 'Geko_Html_Widget_' );
	
	
	protected $_aAtts = array();
	protected $_mValue;
	protected $_aParams = array();
	
	protected $_sWidget = '';
	
	
	
	
	// factory method
	public static function create( $sWidget, $aAtts = array(), $mValue = NULL, $aParams = array() ) {

		$bSetWidget = FALSE;
		
		$aClasses = array();
		
		foreach ( self::$aClassPrefixes as $sClassPrefix ) {
			$aClasses[] = sprintf( '%s%s', $sClassPrefix, Geko_Inflector::camelize( $sWidget ) );
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
		
		// parse $sKeyFlags
		list( $sKey, $sFlags ) = Geko_Array::explodeTrimEmpty( ':', $sKeyFlags );
		
		$sKey = trim( $sKey );
		
		$aFlags = Geko_Array::explodeTrimEmpty( '|', $sFlags );
		
		$mAttVal = $this->_aAtts[ $sKey ];
		
		
		//// apply formatting
		
		if ( in_array( 'str', $aFlags ) ) {
			
			$mAttVal = trim( $mAttVal );
			
			if ( !$mAttVal ) {
				
				$mAttVal = '';
				
			} else {
				
				if ( in_array( 'lc', $aFlags ) ) {
					$mAttVal = strtolower( $mAttVal );
				}
			
			}
			
		} elseif ( in_array( 'int', $aFlags ) ) {
			
			$mAttVal = intval( $mAttVal );
			
		} elseif ( in_array( 'bool', $aFlags ) ) {
			
			$mAttVal = intval( $mAttVal ) ? TRUE : FALSE ;
			
		}
		
		//// apply value mapping
		
		// $aValueMap
		
		if ( count( $aValueMap ) > 0 ) {
			
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

			if ( $mMapValue = $aValueMapFmt[ $mAttVal ] ) {
				$mAttVal = $mMapValue;
			}			
		}
		
		
		if ( !$mAttVal ) {
			$mAttVal = $mDefault;
		}
		
		return $mAttVal;
	}
	
	
	//
	public function formatAppendValue( $mValue ) {
		
		if ( $mValue instanceof Geko_Html_Widget ) {
			return $mValue->get();
		}
		
		return $mValue;
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


