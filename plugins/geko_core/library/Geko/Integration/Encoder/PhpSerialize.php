<?php

//
class Geko_Integration_Encoder_PhpSerialize extends Geko_Integration_Encoder_Abstract
{
	
	public function encode($sData) {
		return serialize($sData);
	}
	
	public function decode($sData) {
		return unserialize($sData);	
	}

	
}


