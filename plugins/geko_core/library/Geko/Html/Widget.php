<?php

//
class Geko_Html_Widget
{
	
	protected $_aAtts = array();
	protected $_mValue;
	protected $_aParams = array();
	
	protected $_sWidget = '';
	
	// factory method
	public static function create( $sWidget, $aAtts, $mValue, $aParams ) {
		
		$sClass = 'Geko_Html_Widget_' . Geko_Inflector::camelize( $sWidget );
		$bSetWidget = FALSE;
		
		if ( !class_exists( $sClass ) ) {
			$sClass = __CLASS__;
			$bSetWidget = TRUE;
		}
		
		$oWidget = new $sClass( $aAtts, $mValue, $aParams );
		
		if ( $bSetWidget ) $oWidget->setWidget( $sWidget );
		
		return $oWidget;
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

}


