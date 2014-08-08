<?php

//
class GekoTest_Geography_Country extends Geko_PhpUnit_TestCase
{
	
	//
	public function providerCodes() {
		return array(
			array( 'GB', 'United Kingdom' ),
			array( 'CA', 'Canada' ),
			array( 'PH', 'Philippines' )
		);
	}
	
	/**
	 * @dataProvider providerCodes
	 */
	public function testGetNameFromCode( $sCode, $sResult ) {
		
		$oGeoCoun = Geko_Geography_Country::getInstance();
		
		$this->assertEquals(
			$oGeoCoun->getNameFromCode( $sCode ),
			$sResult
		);
	}
	
}

