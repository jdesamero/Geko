<?php

//
class GekoTest_Uri extends Geko_PhpUnit_TestCase
{
	
	protected static $sUrl = 'http://www.geekoracle.com/some/script/?page=contact&section=main';
	protected static $oUrl;
	
	
	//
	public static function setUpBeforeClass() {
		self::$oUrl = new Geko_Uri( self::$sUrl );
	}
	
	//
	public static function tearDownAfterClass() {
		self::$oUrl = NULL;
	}
	
	
	//
	public function testBasic() {
		
		$oUrl = self::$oUrl;
		
		$this->assertEquals( strval( $oUrl ), self::$sUrl );
		$this->assertEquals( $oUrl->getVars(), array( 'page' => 'contact', 'section' => 'main' ) );
		$this->assertEquals( $oUrl->getPath(), '/some/script/' );
		$this->assertEquals( $oUrl->getVarCount(), 2 );
		
	}
	
	
	//
	public function testSame() {
		
		$oUrl = self::$oUrl;
		
		$sCompareUrl = 'http://www.geekoracle.com/some/*/';
		$oCompareUrl = new Geko_Uri( $sCompareUrl );
		
		$this->assertEquals( $oCompareUrl->same( 'path', $oUrl ), TRUE );
		
		// Comparison works one way, but not the other
		$this->assertEquals( $oUrl->same( 'path', $oCompareUrl ), FALSE );
		
	}
	
	
	//
	public function testSameVars() {
		
		$oUrl = self::$oUrl;
		
		// change order
		$sCompareUrl = 'http://www.geekoracle.com/some/script/?section=main&page=contact';
		$oCompareUrl = new Geko_Uri( $sCompareUrl );
		
		$this->assertEquals( $oCompareUrl->sameVars( $oUrl ), TRUE );
		
		// add vars
		$sCompareUrl = 'http://www.geekoracle.com/some/script/?section=main&page=contact&foo=1&bar=2';
		$oCompareUrl = new Geko_Uri( $sCompareUrl );
		
		$this->assertEquals( $oCompareUrl->sameVars( $oUrl ), FALSE );
		
		// one less var
		$sCompareUrl = 'http://www.geekoracle.com/some/script/?section=main';
		$oCompareUrl = new Geko_Uri( $sCompareUrl );
		
		$this->assertEquals( $oCompareUrl->sameVars( $oUrl ), TRUE );
		
		// strict comparison
		$this->assertEquals( $oCompareUrl->sameVars( $oUrl, TRUE ), FALSE );
		
		// strict comparison and ignore
		$this->assertEquals( $oCompareUrl->sameVars( $oUrl, TRUE, 'page' ), TRUE );
		
	}
	
	
	//
	public function testRegistry() {
		
		Geko_Uri::setUrl( array(
			'__unittest_read' => 'http://api.geekoracle.com/read.php',
			'__unittest_write' => 'http://api.geekoracle.com/write.php'
		) );
		
		$this->assertEquals( Geko_Uri::getUrl( '__unittest_read' ), 'http://api.geekoracle.com/read.php' );
		$this->assertEquals( Geko_Uri::getUrl( '__unittest_write' ), 'http://api.geekoracle.com/write.php' );
		
		Geko_Uri::setUrl( '__unittest_thumb', 'http://api.geekoracle.com/thumb.php' );
		
		$this->assertEquals( Geko_Uri::getUrl( '__unittest_thumb' ), 'http://api.geekoracle.com/thumb.php' );
		
		Geko_Uri::setUrl( array(
			'__unittest_write' => '',
			'__unittest_thumb' => ''
		) );
		
		Geko_Uri::setUrl( '__unittest_read', '' );
		
		$this->assertEquals( Geko_Uri::getUrl( '__unittest_read' ), '' );
		$this->assertEquals( Geko_Uri::getUrl( '__unittest_write' ), '' );
		$this->assertEquals( Geko_Uri::getUrl( '__unittest_thumb' ), '' );
		
	}
	
}

