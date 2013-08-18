<?php

//
class Geko_Sql_Callback
{
	protected $fCallback;
	protected $aArgs = array();
	
	//
	public function __construct() {
		
		$aArgs = func_get_args();
		
		$this->fCallback = array_shift( $aArgs );
		$this->aArgs = $aArgs;
	}
	
	//
	public function getArgs() {
		return $this->aArgs;
	}
	
	//
	public function setArgs( $aArgs ) {
		$this->aArgs = $aArgs;
		return $this;
	}
	
	//
	public function evaluate() {
		return call_user_func_array( $this->fCallback, $this->aArgs );
	}
	
}

