<?php

//
class GekoTest_Wp_Admin_Menu extends Geko_PhpUnit_TestCase
{
	
	protected static $sMenuOutput;
	
	//
	public static function setUpBeforeClass() {
		
		$aLinks = self::providerBasic();
		
		foreach ( $aLinks as $aLink ) {
			Geko_Wp_Admin_Menu::addMenu( 'foo', $aLink[ 0 ], $aLink[ 1 ] );	
		}
		
		self::$sMenuOutput = Geko_String::fromOb( array( 'Geko_Wp_Admin_Menu', 'showMenu' ), array( 'foo' ) );
	}
	
	//
	public static function tearDownAfterClass() {
	
	}
	
	
	//
	public function providerBasic() {
		$sBloginfoUrl = get_bloginfo( 'url' );
		return array(
			array(
				'Foo Bar', '/foo/bar.php', 
				'<a class="current" href="' . $sBloginfoUrl . '/foo/bar.php"><span>Foo Bar</span></a>'
			),
			array(
				'Foo Baz', '/foo/baz.php',
				'<a class="current" href="' . $sBloginfoUrl . '/foo/baz.php"><span>Foo Baz</span></a>'
			),
			array(
				'Apple', 'http://apple.com',
				'<a class="current" href="http://apple.com"><span>Apple</span></a>'
			),
			array(
				'Geek', 'http://dev.geekoracle.com/cool/stuff/',
				'<a class="current" href="http://dev.geekoracle.com/cool/stuff/"><span>Geek</span></a>'
			)
		);
	}
	
	
	/**
	 * @dataProvider providerBasic
	 */
	public function testBasic( $sTitle, $sUrl, $sResult ) {
		$this->assertEquals( TRUE, ( FALSE !== strpos( self::$sMenuOutput, $sResult ) ) );
	}
	
	
	
}

