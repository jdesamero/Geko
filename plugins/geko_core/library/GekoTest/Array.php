<?php

//
class GekoTest_Array extends PHPUnit_Framework_TestCase
{
	
	protected $sTest = "Furdi, ,, , 0, Pudgy, Ratzo, 'Babew', Frank, \t\r\"Woodchuck\"";
	
	protected $aTest = array(
		'name' => 'John Smith',
		'colors' => array( 'Red', 'Green', 'Blue' ),
		13 => 'Thirteen',
		'yes' => array(
			'this' => array(
				'is' => array(
					'a' => 'path'
				)
			)
		)
	);
	
		
	
	//
	public function providerIsAssoc() {
		return array(
			array( TRUE, array( 'name' => 'furdi', 'type' => 'mouse', 'color' => 'gray' ) ),
			array( FALSE, array( 'furdi', 'mouse', 'gray' ) ),
			array( FALSE, array( 2 => 'furdi', 1 => 'mouse', 0 => 'gray' ) ),
			array( TRUE, array( 1 => 'furdi', 'mouse', 'gray' ) ),
			array( TRUE, array( 3 => 'furdi', 2 => 'mouse', 1 => 'gray' ) )
		);
	}
	
	
	/**
	 * @dataProvider providerIsAssoc
	 */
	public function testIsAssoc( $bRes, $aVals ) {
		$this->assertEquals( $bRes, Geko_Array::isAssoc( $aVals ) );
	}
	
	
	
	//
	public function providerExplodeTrim() {
		return array(
			array( 'Furdi,,,,0,Pudgy,Ratzo,\'Babew\',Frank,"Woodchuck"', ',', NULL ),
			array( "Furdi||, , 0, Pudgy, Ratzo, 'Babew', Frank, \t\r" . '"Woodchuck"', '|', array( 'limit' => 3 ) ),
			array( "Furdi,,,,0,Pudgy,Ratzo,Babew,Frank,\t\r\"Woodchuck", ',', array( 'trim_chars' => "'\" " ) ),
			array( "Furdi,,,,0,Pudgy,Ratzo,Babew,Frank,Woodchuck", ',', array( 'trim_chars' => "'\"##ws##" ) ),
			array( "Furdi,0,Pudgy,Ratzo,Babew,Frank,Woodchuck", ',', array( 'trim_chars' => "'\"##ws##", 'remove_empty' => TRUE ) ),
			array(
				"Furdi,Pudgy,Ratzo,Babew,Woodchuck", ',',
				array( 'trim_chars' => "'\"##ws##", 'remove_empty' => TRUE, 'empty_filter' => array( '', 0, 'Frank' ) )
			)
		);
	}
	
	
	/**
	 * @dataProvider providerExplodeTrim
	 */
	public function testExplodeTrim( $sResult, $sDelim, $aParams ) {
		$aRes = Geko_Array::explodeTrim( ',', $this->sTest, $aParams );
		$this->assertEquals( $sResult, implode( $sDelim, $aRes ) );
	}
	
	
	
	//
	public function providerValues() {
		return array(
			array( 'wordpress_7cb7ab86b8c6184d96acab53dec2f52a', array( 'beginswith' ) ),
			array( 'wordpress_logged_in_7cb7ab86b8c6184d96acab53dec2f52a', array( 'beginswith' ) ),
			array( '__utmb', array( 'beginswith', 'contains' ) ),
			array( '__utmc', array( 'beginswith', 'contains' ) ),
			array( 'wp-settings-3', array( 'beginswith' ) ),
			array( 'wp-settings-time-3', array( 'beginswith' ) ),
			array( 'page' ),
			array( 'searchsubmit' ),
			array( 'redirect_to' ),
			array( 'doing_wp_cron' ),
			array( 'wp-settings-1', array( 'beginswith' ) ),
			array( 'wp-settings-time-1', array( 'beginswith' ) ),
			array( 'wordpress_test_cookie', array( 'beginswith' ) ),
			array( '__utma', array( 'beginswith', 'contains' ) ),
			array( '__utmb', array( 'beginswith', 'contains' ) ),
			array( '__utmc', array( 'beginswith', 'contains' ) ),
			array( '__utmz', array( 'beginswith', 'contains' ) ),
			array( '__unam', array( 'beginswith', 'contains' ) ),
			array( 'wp-user-visits-time', array( 'beginswith' ) ),
			array( 'PHPSESSID' ),
			array( 'unsatisfactory', array( 'contains' ) ),
			array( 'fun', array( 'contains' ) )
		);
	}
	
	
	/**
	 * @dataProvider providerValues
	 */
	public function testBeginsWith( $sSubject, $aGroup = NULL ) {
		if ( $aGroup && in_array( 'beginswith', $aGroup ) ) {
			$this->assertEquals(
				Geko_Array::beginsWith(
					$sSubject,
					array( 'wordpress_', 'wp-settings-', '__ut', '__un', 'wp-user-' )
				),
				TRUE
			);
		}
	}
	
	/**
	 * @dataProvider providerValues
	 */
	public function testContains( $sSubject, $aGroup = NULL ) {
		if ( $aGroup && in_array( 'contains', $aGroup ) ) {
			$this->assertEquals(
				Geko_Array::contains( $sSubject, array( 'ut', 'un' ) ),
				TRUE
			);
		}
	}
	


	//
	public function providerGetElement() {
		return array(
			array( '[name]', 'John Smith' ),
			array( '[colors][1]', 'Green' ),
			array( '[13]', 'Thirteen' ),
			array( '[yes][this][is][a]', 'path' ),
			array( '[yes][this][is]', array( 'a' => 'path' ) )
		);
	}
	
	/**
	 * @dataProvider providerGetElement
	 */
	public function testGetElement( $sKey, $sResult ) {
		$this->assertEquals( $sResult,
			Geko_Array::getElement( $this->aTest, $sKey )
		);
	}
	
}

