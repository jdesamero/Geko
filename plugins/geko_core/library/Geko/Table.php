<?php

class Geko_Table
{
	
	private $aData = array();
	private $aMeta = array();
	private $aParams = array();
	
	private $oOutput;
	
	//
	public function __construct( $aData, $aMeta, $aParams = array(), $oOutput = NULL ) {
		
		if ( !$oOutput ) {
			$oOutput = Geko_Table_Output_Default::getInstance();
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
		$this->aData = $aData;
		return $this;
	}
	
	//
	public function getData() {
		return $this->aData;
	}
	
	//
	public function setMeta( $aMeta ) {
		$this->aMeta = $aMeta;
		return $this;
	}
	
	//
	public function getMeta() {
		return $this->aMeta;
	}
	
	//
	public function setParams( $aParams ) {
		$this->aParams = array_merge( $this->aParams, $aParams );	
		return $this;
	}
	
	//
	public function getParams() {
		return $this->aParams;
	}
	
	//
	public function setOutput( $oOutput ) {
		$this->oOutput = $oOutput;
		return $this;
	}
	
	//
	public function getOutput() {
		return $this->oOutput;
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
		$this->oOutput->output( $this );	
		return $this;
	}
	

}

