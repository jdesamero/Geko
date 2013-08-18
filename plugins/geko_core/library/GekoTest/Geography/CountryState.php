<?php

//
class GekoTest_Geography_CountryState extends Geko_PhpUnit_TestCase
{

	//
	public function providerCodes() {
		return array(
			array( 'ON', 'Ontario' ),
			array( 'AZ', 'Arizona' ),
			array( 'BC', 'British Columbia' )
		);
	}
	
	
	/**
	 * @dataProvider providerCodes
	 */
	public function testGetNameFromCode( $sCode, $sResult ) {
		 $this->assertEquals(
		 	Geko_Geography_CountryState::getNameFromCode( $sCode ),
		 	$sResult
		 );
	}
	
	
}

