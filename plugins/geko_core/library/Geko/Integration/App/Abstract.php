<?php

//
abstract class Geko_Integration_App_Abstract extends Geko_Integration
{
	protected $aParams;
	
	protected $sSubdir;
	protected $sCode;
	protected $sKey;
	
	//
	public function __construct( $aParams = array() ) {
		$this->setParams( $aParams );
	}
	
	//
	public function setParams( $aParams ) {

		$this->aParams = $aParams;
		
		$this->sKey = $this->_getKey();
		$this->sCode = Geko_String::coalesce( $this->sCode, $this->sSubdir, $this->sKey );
		
		if ( isset( $this->aParams['subdir'] ) ) {
			$this->sSubdir = $this->aParams['subdir'];
		} else {
			// default sub directory is same as app code, if not set
			$this->sSubdir = ( $this->sSubdir ) ? $this->sSubdir : $this->sCode;
		}
		
		return $this;
	}
	
	//
	abstract public function detect();
	
	//
	abstract public function _getKey();
	
	//
	abstract public function getDbConn();
	
	
	//
	public function getCode()
	{
		return $this->sCode;
	}

	//
	public function getKey()
	{
		return $this->sKey;
	}

	//
	public function getSubdir()
	{
		return $this->sSubdir;
	}
	
	
}



