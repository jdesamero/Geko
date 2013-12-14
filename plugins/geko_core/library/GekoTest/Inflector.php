<?php

//
class GekoTest_Inflector extends Geko_PhpUnit_TestCase
{
	
	//
	public function providerPluralize() {
		return array(
			array( 'categories', 'category' ),
			array( 'categories', 'categories' ),
			array( 'products', 'product' ),
			array( 'mice', 'mouse' ),
			array( 'oxen', 'ox' ),
			array( 'viruses', 'virus' )
		);
	}
	
	/**
	 * @dataProvider providerPluralize
	 */
    public function testPluralize( $sResult, $sSubject ) {
        $this->assertEquals( $sResult, Geko_Inflector::pluralize( $sSubject ) );
	}
	

	//
	public function providerSingularize() {
		return array(
			array( 'category', 'categories' ),
			array( 'category', 'category' ),
			array( 'product', 'products' ),
			array( 'mouse', 'mice' )
		);
	}
	
	/**
	 * @dataProvider providerSingularize
	 */
    public function testSingularize( $sResult, $sSubject ) {
        $this->assertEquals( $sResult, Geko_Inflector::singularize( $sSubject ) );
	}
	
    
    
}

