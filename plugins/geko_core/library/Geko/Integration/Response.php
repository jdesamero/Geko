<?php

//
class Geko_Integration_Response extends Geko_Integration
{
	const FAIL_REQUEST = 1;
	const FAIL_RESPONSE = 2;
	const SUCCESS = 3;
	
	protected $iStatus;
	protected $aResults = array();
	
	
	//// is
	
	//
	public function isFailRequest() {
		return ($this->iStatus == self::FAIL_REQUEST);
	}

	//
	public function isFailResponse() {
		return ($this->iStatus == self::FAIL_RESPONSE);
	}

	//
	public function isSuccess() {
		return ($this->iStatus == self::SUCCESS);
	}
	
	
	
	//// setters
	
	//
	public function setStatus($iStatus) {
		$this->iStatus = $iStatus;
		return $this;
	}

	//
	public function setResults($aResults) {
		$this->aResults = array_merge( $this->aResults, $aResults );
		return $this;
	}
	
	//
	public function setResult($mValue) {
		$this->aResults[] = $mValue;
		return $this;
	}
	
	//
	public function setResponseItems($oRequest) {
		foreach ($this->aResults as $iIndex => $mResult) {
			$oRequest
				->getResponseItem( $iIndex )
				->setResult( $mResult )
			;
		}
		return $this;
	}
	
	
	//// getters

	//
	public function getStatus() {
		return $this->iStatus;
	}
	
	//
	public function getResult($iIndex) {
		if ( isset($this->aResults[$iIndex]) ) {
			return $this->aResults[$iIndex];
		} else {
			return NULL;
		}	
	}
	
	//
	public function getResults($iIndex = NULL) {
		if (NULL === $iIndex) {
			return $this->aResults;
		} else {
			return $this->getResult( $iIndex );
		}
	}
	
	
	
	//// json
	
	//
	public function toJson() {
		return Zend_Json::encode(array(
			'__class' => get_class($this),
			'status' => $this->iStatus,
			'results' => $this->aResults
		));
	}
	
	//
	public function fromJson($aParams) {
		$this->iStatus = $aParams['status'];
		$this->aResults = $aParams['results'];
		return $this;
	}	
	
	
	
	
}


