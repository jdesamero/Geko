<?php

//
class GekoTest_Entity extends Geko_PhpUnit_TestCase
{
	
	protected $oSimple;
	protected $oMultiKey;
	
	
	//
	public function setUp() {
		
		$aValues = array(
			'id' => 567,
			'title' => 'This is the title',
			'slug' => 'this-is-the-title',
			'content' => 'Lorem ipsum dolor "filler text" this is.',
			'uri' => 'apple.com'
		);
		
		$oSimple = new GekoX_Test_Simple( $aValues );
		
		$this->oSimple = $oSimple;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$aValues = array(
			'sec_id' => 20,
			'sec_idx' => 101,
			'title' => 'This is the title',
			'slug' => 'this-is-the-title',
			'content' => 'Lorem ipsum dolor "filler text" this is.',
			'color' => 'Red',
			'size' => 'Big',
			'age' => 'Old'
		);
		
		$oMultiKey = new GekoX_Test_MultiKey( $aValues );
		
		$this->oMultiKey = $oMultiKey;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$aValues = array(
			'id' => 567,
			'title' => 'This is the title',
			'slug' => 'this-is-the-##number##',
			'content' => 'Lorem ipsum dolor "##fruit##" this is.',
			'uri' => 'apple.com'
		);
		
		$aData = array(
			'placeholders' => array(
				'fruit' => 'Apple',
				'number' => 1006
			)
		);
		
		$oPlaceholder = new GekoX_Test_Simple( $aValues, NULL, $aData );
		
		$this->oPlaceholder = $oPlaceholder;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$aValues = array(
			'id' => 567,
			'title' => 'This is the title',
			'slug' => 'this-is-the-slug',
			'content' => 'What is your name?',
			'uri' => 'apple.com/en'
		);
		
		$aData = array(
			'lang_meta' => array(
				'content' => 'Quel est votre nom?',
				'uri' => 'apple.com/fr'
			),
			'lang_meta_fields' => array(
				'content', 'uri'
			)
		);
		
		$oLanguage = new GekoX_Test_Simple( $aValues, NULL, $aData );
		
		$this->oLanguage = $oLanguage;
		
	}
	
	//
	public function tearDown() {
		unset( $this->oSimple );
		unset( $this->oMultiKey );
		unset( $this->oPlaceholder );
		unset( $this->oLanguage );
	}
	
	
	//
	public function testGet() {
		
		$oSimple = $this->oSimple;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// simple
		$this->assertEquals( $oSimple->getId(), 567 );
		$this->assertEquals( 'http://apple.com', $oSimple->getUrl() );
		$this->assertEquals(
			"Lorem ipsum dolor &quot;filler text&quot; this is.",
			$oSimple->escgetContent()
		);
				
	}
	
	
	//
    public function testEcho() {
		
		$oSimple = $this->oSimple;
		
		$this->assertEquals( Geko_String::fromOb( array( $oSimple, 'echoId' ) ), 567 );
		$this->assertEquals( 'http://apple.com', Geko_String::fromOb( array( $oSimple, 'echoUrl' ) ) );
		$this->assertEquals(
			"Lorem ipsum dolor &quot;filler text&quot; this is.",
			Geko_String::fromOb( array( $oSimple, 'escechoContent' ) )
		);
		
	}
	
	//
	public function testMultiKey() {

		$oMultiKey = $this->oMultiKey;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// multi-key
		$this->assertEquals( $oMultiKey->getId(), '20:101' );
		$this->assertEquals( $oMultiKey->getTrait(), 'Red:Big:Old' );
		$this->assertEquals(
			$oMultiKey->getEntityMapping( 'trait' ),
			array( 'color', 'size', 'age' )
		);
		
	}
	
	//
	public function testPlaceholder() {

		$oPlaceholder = $this->oPlaceholder;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// placeholder
		
		$this->assertEquals(
			$oPlaceholder->getContent(),
			'Lorem ipsum dolor "Apple" this is.'
		);

		$this->assertEquals(
			$oPlaceholder->getSlug(),
			'this-is-the-1006'
		);
		
		$aPlaceholders = $oPlaceholder->getData( 'placeholders' );
		$aPlaceholders[ 'fruit' ] = 'Orange';
		$oPlaceholder->setData( 'placeholders', $aPlaceholders );
		
		$this->assertEquals(
			$oPlaceholder->getContent(),
			'Lorem ipsum dolor "Orange" this is.'
		);
		
	}

	//
	public function testLanguage() {
		
		$oLanguage = $this->oLanguage;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// placeholder
		
		$this->assertEquals( $oLanguage->getContent(), 'Quel est votre nom?' );
		$this->assertEquals( $oLanguage->getUri(), 'apple.com/fr' );
		
		$aLangMetaFields = $oLanguage->getData( 'lang_meta_fields' );
		$oLanguage->setData( 'lang_meta_fields', array() );
		
		$this->assertEquals( $oLanguage->getContent(), 'What is your name?' );
		$this->assertEquals( $oLanguage->getUri(), 'apple.com/en' );
				
	}
	
	//
	public function testMethods() {

		$oSimple = $this->oSimple;

		$this->assertEqualsHtml(
			$oSimple->getLink(),
			'<a href="http://apple.com">This is the title</a>'
		);
		
	}
	
}

