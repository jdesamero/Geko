<?php

//
class GekoTest_Wp_Layout extends Geko_PhpUnit_TestCase
{
	
	protected static $oLayout;
	
	//
	public static function setUpBeforeClass() {
	
		$oLayout = GekoX_Test_Wp_Layout::getInstance()->init();
		$oLayout
			->addTemplate( 'about_us.php', 'page', 'public' )
			->addTemplate( 'contact_us.php', 'page', 'public' )
			->addTemplate( 'member_dashboard.php', 'page', 'protected' )
			->addTemplate( 'news.php', 'listing', 'public' )
			->addTemplate( 'events.php', 'listing', 'public' )
			->addTemplate( 'member_news.php', 'listing', 'protected' )
			->addTemplate( 'member_events.php', 'listing', 'protected' )
		;
	
		self::$oLayout = $oLayout;
	}
	
	//
	public static function tearDownAfterClass() {
		self::$oLayout = NULL;
	}
	
	
	//
	public function providerTemplates() {
		return array(
			array( 'about_us.php|contact_us.php|member_dashboard.php|news.php|events.php|member_news.php|member_events.php', array() ),
			array( 'about_us.php|contact_us.php|member_dashboard.php', array( 'page' ) ),
			array( 'news.php|events.php|member_news.php|member_events.php', array( 'listing' ) ),
			array( 'member_dashboard.php|member_news.php|member_events.php', array( 'protected' ) ),
			array( 'about_us.php|contact_us.php', array( 'page', 'public' ) ),
			array( 'member_dashboard.php', array( 'page', 'protected' ) ),
			array( 'news.php|events.php', array( 'listing', 'public' ) ),
			array( 'member_news.php|member_events.php', array( 'listing', 'protected' ) )
		);
	}
	
	/**
	 * @dataProvider providerTemplates
	 */
    public function testTemplates( $sResult, $aParams ) {
    	$oLayout = self::$oLayout;
		$this->assertEquals( $sResult,
			call_user_func_array( array( $oLayout, 'getTemplateList' ), $aParams )
		);
	}
	
	
}

