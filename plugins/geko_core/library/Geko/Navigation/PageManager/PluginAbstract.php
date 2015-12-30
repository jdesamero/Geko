<?php
/*
 * "geko_navigation_management/includes/library/Geko/Navigation/PageManager/PluginAbstract.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
abstract class Geko_Navigation_PageManager_PluginAbstract
{
	protected $_iIndex;
	protected $_aParams = array();
	protected $_aJsOptions = array();
	
	
	//
	public function __construct( $iIndex = 0, $aParams = array() ) {
		
		$this
			->setIndex( $iIndex )
			->setParams( $aParams )
			->init()
		;
	}
	
	
	//
	public function setJsOption( $sKey, $sValue = NULL ) {
		
		if ( is_array( $aParams = $sKey ) ) {
			$this->_aJsOptions = array_merge( $this->_aJsOptions, $aParams );
		} else {
			$this->_aJsOptions[ $sKey ] = $sValue;		
		}
		
		return $this;
	}
	
	
	
	//
	public function setIndex( $iIndex ) {
		
		$this->_iIndex = $iIndex;
		
		return $this;
	}
	
	//
	public function getIndex() {
		return $this->_iIndex;
	}

	
	
	
	//
	public function setParams( $aParams ) {
		
		$this->_aParams = $aParams;
		
		return $this;		
	}
	
	//
	public function getParams() {
		return $this->_aParams;
	}
	
	
	
	
	//
	public function getDefaultParams() {
		return array(
			'target' => '',
			'type' => $this->_iIndex,
			'indent' => 0
		);
	}
	
	//
	public function getManagementData() {
		return array_merge( array(
			'__class' => get_class( $this )
		), $this->_aJsOptions );
	}
	
	
	
	public function init() { }
	public function postInit() { }
	
	public function outputStyle() { }		
	public function outputHtml() { }
	
	
	
	//
	abstract public static function getDescription();
	
	
	
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		if ( 0 === strpos( strtolower( $sMethod ), 'get' ) ) {
			
			// attempt to call echo*() method if it exists
			$sCall = substr_replace( $sMethod, 'output', 0, 3 );
			
			if ( method_exists( $this, $sCall ) ) {
				
				ob_start();
				$this->$sCall();
				$sOutput = ob_get_contents();
				ob_end_clean();
				
				return trim( $sOutput );
			}
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', __CLASS__, $sMethod ) );
	}
	
}


