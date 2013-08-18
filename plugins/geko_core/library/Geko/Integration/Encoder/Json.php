<?php

//
class Geko_Integration_Encoder_Json extends Geko_Integration_Encoder_Abstract
{
	
	public function encode($mData) {
		return Zend_Json::encode($mData);
	}
	
	public function decode($sData) {
		$aDecode = Zend_Json::decode($sData);
		if ( isset($aDecode['__class']) ) {
			$oCast = new $aDecode['__class'];
			if ( method_exists($oCast, 'fromJson') ) {
				return $oCast->fromJson( $aDecode );
			} else {
				return $aDecode;
			}
		} else {
			return $aDecode;
		}
	}

	
}


