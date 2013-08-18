<?php

//
class Geko_Wp_Location extends Geko_Wp_Entity
{
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this->setEntityMapping( 'id', 'address_id' );
		
		return $this;
	}
	
	
	//
	public function getAddressLine1() {
		return $this->getEntityPropertyValue( 'address_line_1' );
	}
	
	//
	public function getAddressLine2() {
		return $this->getEntityPropertyValue( 'address_line_2' );
	}
	
	//
	public function getAddressLine3() {
		return $this->getEntityPropertyValue( 'address_line_3' );
	}
	
	//
	public function getCityProvinceName() {
		return self::getLocationPair(
			$this->getEntityPropertyValue( 'city' ),
			$this->getEntityPropertyValue( 'province_name' )
		);
	}
	
	//
	public function getCityProvinceAbbr() {
		return self::getLocationPair(
			$this->getEntityPropertyValue( 'city' ),
			$this->getEntityPropertyValue( 'province_abbr' )
		);
	}
	
	//
	public static function getLocationPair( $sVal1, $sVal2 ) {
		$sVal1 = trim( $sVal1 );
		$sVal2 = trim( $sVal2 );
		if ( $sVal1 ) {
			if ( $sVal2 ) $sVal1 .= ', ' . $sVal2;
		} else {
			$sVal1 = $sVal2;
		}
		return $sVal1;
	}
	
}



