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
	public static function create( $sWidget, $aAtts, $mValue, $aParams ) {

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
		self::$aClassPrefixes[] = $sPrefix;
	}

	//
	public static function appendClassPrefixes( $sPrefix ) {
		array_unshift( self::$aClassPrefixes, $sPrefix );
	}
		
	//
	public static function setClassPrefixes( $aPrefixes ) {
		self::$aClassPrefixes = $aPrefixes;
	}
	
	
	
	//
	public function __construct( $aAtts, $mValue, $aParams ) {
		
		$this->_aAtts = $aAtts;
		$this->_mValue = $mValue;
		$this->_aParams = $aParams;
		
	}
	
	//
	public function setWidget( $sWidget ) {
		
		$this->_sWidget = $sWidget;
		
		return $this;
	}
	
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


