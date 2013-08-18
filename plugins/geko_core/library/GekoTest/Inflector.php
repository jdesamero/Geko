<?php

//
class GekoTest_Inflector extends PHPUnit_Framework_TestCase
{
	//
    public function testPluralize() {
    	
		//
        $this->assertEquals(
        	'categories',
        	Geko_Inflector::pluralize( 'category' )
        );
		
		//
        $this->assertEquals(
        	'categories',
        	Geko_Inflector::pluralize( 'categories' )
        );
		
		//
        $this->assertEquals(
        	'products',
        	Geko_Inflector::pluralize( 'product' )
        );
		
		//
        $this->assertEquals(
        	'mice',
        	Geko_Inflector::pluralize( 'mouse' )
        );
        
	}

	//
    public function testSingularize() {
    	
		//
        $this->assertEquals(
        	'category',
        	Geko_Inflector::singularize( 'categories' )
        );
		
		//
        $this->assertEquals(
        	'category',
        	Geko_Inflector::singularize( 'category' )
        );
		
		//
        $this->assertEquals(
        	'product',
        	Geko_Inflector::singularize( 'products' )
        );
        
		//
        $this->assertEquals(
        	'mouse',
        	Geko_Inflector::singularize( 'mice' )
        );
        
	}
	
    
}

