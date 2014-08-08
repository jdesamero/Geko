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
		
		$oGeoCont = Geko_Geography_Continent::getInstance();
		
		$this->assertEquals(
			$oGeoCont->getNameFromCode( $sCode ),
			$sResult
		);
	}

}

