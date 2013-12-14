<?php

//
class Geko_Integration_ResponseItem extends Geko_Integration
{
	protected $mResult;
	protected $oClient;
	
	//// accessors
		
	//
	public function setResult($mResult) {
		$this->mResult = $mResult;
		return $this;
	}
	
	// alias of setResult
	public function set($mResult) {
		return $this->setResult($mResult);
	}
	
	//
	public function setClient( $oClient ) {
		$this->oClient = $oClient;
		return $this;
	}
	
	//
	public function getResult() {
		return $this->mResult;
	}
	
	// alias of getResult
	public function get() {
		
		if ( is_object( $this->oClient ) ) {
			$this->oClient->triggerGet();
		}
		
		return $this->getResult();
	}
	
	
}


