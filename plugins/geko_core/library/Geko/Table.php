<?php
/*
 * "geko_core/library/Geko/Table.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Table
{
	
	protected $_aData = array();
	protected $_aMeta = array();
	protected $_aParams = array();
	
	protected $_oOutput;
	
	//
	public function __construct( $aData, $aMeta, $aParams = array(), $oOutput = NULL ) {
		
		if ( !$oOutput ) {
			
			$sOutputType = ( $aParams[ 'output' ] ) ? $aParams[ 'output' ] : 'Default' ;
			
			$sClass = sprintf( 'Geko_Table_Output_%s', $sOutputType );
			
			if ( !class_exists( $sClass ) ) {
				$sClass = 'Geko_Table_Output_Default';
			}
			
			$oOutput = Geko_Singleton_Abstract::getInstance( $sClass );
		}
		
		$this
			->setData( $aData )
			->setMeta( $aMeta )
			->setParams( $aParams )
			->setOutput( $oOutput )
		;
		
	}
	
	
	//// accessors
	
	//
	public function setData( $aData ) {
		$this->_aData = $aData;
		return $this;
	}
	
	//
	public function getData() {
		return $this->_aData;
	}
	
	//
	public function setMeta( $aMeta ) {
		$this->_aMeta = $aMeta;
		return $this;
	}
	
	//
	public function getMeta() {
		return $this->_aMeta;
	}
	
	//
	public function setParams( $aParams ) {
		$this->_aParams = array_merge( $this->_aParams, $aParams );	
		return $this;
	}
	
	//
	public function getParams() {
		return $this->_aParams;
	}
	
	//
	public function setOutput( $oOutput ) {
		$this->_oOutput = $oOutput;
		return $this;
	}
	
	//
	public function getOutput() {
		return $this->_oOutput;
	}
	
	
	
	//
	public function getTheMeta() {
		return $this->getMeta();
	}
	
	//
	public function getTheRow( $aRow ) {
		return $aRow;	
	}
	
	//
	public function output() {
		$this->_oOutput->output( $this );	
		return $this;
	}
	

}

