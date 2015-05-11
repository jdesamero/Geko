<?php

//
class Geko_Mail_Delivery
{
	
	
	//
	public function send( $sEmail, $sFirstName = '', $sLastName = '', $aParams = array(), $aFields = array() ) {
	
	
	}
	
	
	//
	public function getParam( $aParams, $sKey, $mDefault ) {
		
		if ( isset( $aParams[ $sKey ] ) ) {
			return $aParams[ $sKey ];
		}
		
		return $mDefault;
	}
	

}


