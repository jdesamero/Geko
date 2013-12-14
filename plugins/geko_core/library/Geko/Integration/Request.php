<?php

//
class Geko_Integration_Request extends Geko_Integration
{
	const MATCH_EXACT = 0;
	const MATCH_PREG_KEY = 1;
	const MATCH_PREG_VALUE = 2;
	
	
	protected $sRequestPath;
	protected $aMimic = array();
	protected $aRequestStack = array();
	protected $aResponseItemStack = array();
	
	
	
	//// setters
	
	//
	public function setRequestPath($sRequestPath) {
		$this->sRequestPath = $sRequestPath;
		return $this;
	}	
	
	//
	public function setMimicValue($iType, $sKey, $mValue) {
		$this->aMimic[] = array(
			'type' => $iType,
			'key' => $sKey,
			'value' => $mValue
		);
		return $this;
	}
	
	//
	public function setMimic($iType, $sMatchKey = '', $iMatchType = self::MATCH_EXACT) {
		
		if ( self::MATCH_PREG_KEY == $iMatchType ) {

			$aSuper = $this->_getSuperValue( $iType );
			foreach ($aSuper as $sKey => $mValue) {
				if ( preg_match( $sMatchKey, $sKey ) ) {
					$this->setMimicValue( $iType, $sKey, $mValue );
				}
			}

		} elseif ( self::MATCH_PREG_VALUE == $iMatchType ) {

			$aSuper = $this->_getSuperValue( $iType );
			foreach ($aSuper as $sKey => $mValue) {
				if ( preg_match( $sMatchKey, strval($mValue) ) ) {
					$this->setMimicValue( $iType, $sKey, $mValue );
				}
			}
			
		} else {
			
			if ( '' == $sMatchKey ) {
				$aSuper = $this->_getSuperValue( $iType );
				foreach ($aSuper as $sKey => $mValue) {
					$this->setMimicValue( $iType, $sKey, $mValue );
				}
			} else {
				$this->setMimicValue( $iType, $sMatchKey, $this->_getSuperValue($iType, $sMatchKey) );		
			}
		
		}
		
		return $this;
	}
	
	//
	public function setRequest( $mRequest, $aParams = array(), $mMeta = NULL ) {
		
		$this->aRequestStack[] = array( $mRequest, $aParams, $mMeta );
		
		// create a corresponding stack of response items
		$this->aResponseItemStack[] = $oResponseItem = new Geko_Integration_ResponseItem();
		
		return $oResponseItem;
	}
	
	
	
	//// getters

	//
	public function getRequestPath() {
		return $this->sRequestPath;
	}
	
	//
	public function getMimic() {
		return $this->aMimic;
	}

	//
	public function getRequestStack() {
		return $this->aRequestStack;
	}
	
	// allow for retrieval of response items
	public function getResponseItem($iIndex) {
		if ( isset( $this->aResponseItemStack[$iIndex] ) ) {
			return $this->aResponseItemStack[$iIndex];
		} else {
			return new Geko_Integration_ResponseItem();
		}
	}
	
	
	//// json
	
	//
	public function toJson() {
		return Zend_Json::encode(array(
			'__class' => get_class($this),
			'request_path' => $this->sRequestPath,
			'mimic' => $this->aMimic,
			'request_stack' => $this->aRequestStack
		));
	}
	
	//
	public function fromJson($aParams) {
		$this->sRequestPath = $aParams['request_path'];
		$this->aMimic = $aParams['mimic'];
		$this->aRequestStack = $aParams['request_stack'];
		return $this;
	}
	
	
	//
	/* /
	public function debug() {
		print_r($this->aMimic);
	}
	/* */
	
}


