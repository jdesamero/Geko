<?php

//
class GekoTest_Geography_Continent extends Geko_PhpUnit_TestCase
{
	
	//
	public function providerCodes() {
		return array(
			array( 'EU', 'Europe' ),
			array( 'AS', 'Asia' ),
			array( 'NA', 'North America' )
		);
	}
	
	/**
	 * @dataProvider providerCodes
	 */
	public function testGetNameFromCode( $sCode, $sResult ) {
		 $this->assertEquals(
		 	Geko_Geography_Continent::getNameFromCode( $sCode ),
		 	$sResult
		 );
	}

}

