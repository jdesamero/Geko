<?php

//
class GekoTest_String extends Geko_PhpUnit_TestCase
{
	
	
	//
	public function providerCoalesce() {
		return array(
			array( NULL, array( NULL ) ),
			array( 'foo', array( NULL, 'foo' ) ),
			array( 'foo', array( 'foo', NULL ) ),
			array( 'foo', array( '', NULL, 'foo' ) )
		);
	}
	
	
	/**
	 * @dataProvider providerCoalesce
	 */
    public function testCoalesce( $mValue, $aParams ) {
		$this->assertEquals( $mValue,
			call_user_func_array( array( 'Geko_String', 'coalesce' ), $aParams )
		);		
	}
	
	//
	public function providerSprintfWrap() {
		return array(
			array(
				'<span>Span Test</span>',
				array( '<span>Span %s</span>', 'Test' )
			),
			array(
				'<span>Span Test</span>',
				array( '<span>Span %s</span>', 'Test' ),
				'sw'
			),
			array( '',
				array( '<span>Span %s</span>', '' )
			),
			array(
				'<span>Span Foo Bar</span>',
				array( '<span>Span %s %s</span>', 'Foo', 'Bar' )
			),
			array(
				'<span id="some-id">Some Name</span>',
				array( '<span id="%s$1">%s$0</span>', 'Some Name', 'some-id' )
			),
			array( '',
				array( '<span id="%s$1">%s$0</span>', '', 'some-id' )
			)
		);
	}
	
	/**
	 * @dataProvider providerSprintfWrap
	 */
	public function testSprintfWrap( $mValue, $aParams, $sMethod = 'sprintfWrap' ) {
		$this->assertEqualsHtml( $mValue,
			call_user_func_array( array( 'Geko_String', $sMethod ), $aParams )
		);
	}
	
	
	//
	public function providerReplacePlaceholders() {
		return array(
			array( array( 'fruit' => 'Apple' ), '##fruit## is tasty!', 'Apple is tasty!' ),
			array( array( 'fruit' => 'Apple' ), '##fruit## is tasty! Very tasty ##fruit##!', 'Apple is tasty! Very tasty Apple!' ),
			array( array( 'name' => 'apple', 'title' => 'The Apple' ), '<a name="###name##">##title##</a>', '<a name="#apple">The Apple</a>' )
		);
	}
	
	/**
	 * @dataProvider providerReplacePlaceholders
	 */
	public function testReplacePlaceholders( $aPlaceholders, $sContent, $sResult ) {
		$this->assertEqualsHtml( $sResult,
			Geko_String::replacePlaceholders( $aPlaceholders, $sContent )
		);
	}
	
	
}

