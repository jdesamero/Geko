<?php

//
class GekoTest_Sql_Select extends PHPUnit_Framework_TestCase
{
	//
    public function testBasicSelect() {
    	
		$oSel = new Geko_Sql_Select();
		$oSel->from( 'users', 'u' );
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT * FROM users AS u',
        	strval( $oSel )
        );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel
			->field( 'u.id', 'id' )
			->field( "CONCAT(u.first_name, ' ', u.last_name)", 'full_name' )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	"SELECT u.id AS id, CONCAT(u.first_name, ' ', u.last_name) AS full_name FROM users AS u",
        	strval( $oSel )
        );
    }
	
	//
    public function testParameterReplacement() {
    	
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.id = ?', 1 )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	"SELECT * FROM users AS u WHERE (u.id = '1')",
        	strval( $oSel )
        );
		
		unset( $oSel );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.name = ?', "d'arcy" )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	"SELECT * FROM users AS u WHERE (u.name = 'd\'arcy')",
        	strval( $oSel )
        );
		
		unset( $oSel );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.id IN ($)', '1, 2, 3' )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT * FROM users AS u WHERE (u.id IN (1, 2, 3))',
        	strval( $oSel )
        );
		
		unset( $oSel );

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.id IN ($)', array( 3, 4, 5 ) )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT * FROM users AS u WHERE (u.id IN (3, 4, 5))',
        	strval( $oSel )
        );
		
		unset( $oSel );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where(
				'u.name = :name1 OR u.name = :name2',
				array( 'name1' => 'rick', 'name2' => 'frank' )
			)
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	"SELECT * FROM users AS u WHERE (u.name = 'rick' OR u.name = 'frank')",
        	strval( $oSel )
        );
		
		unset( $oSel );		
    }

	//
    public function testAdvancedParameterReplacement() {
    	
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.slug * (?)', array( 'dave', 'martin', 'andy' ) )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	"SELECT * FROM users AS u WHERE (u.slug IN ('dave', 'martin', 'andy'))",
        	strval( $oSel )
        );
		
		unset( $oSel );

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.slug * (?)', 'dave' )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	"SELECT * FROM users AS u WHERE (u.slug = 'dave')",
        	strval( $oSel )
        );
		
		unset( $oSel );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.slug * (?)', array( 'dave' ) )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	"SELECT * FROM users AS u WHERE (u.slug = 'dave')",
        	strval( $oSel )
        );
		
		unset( $oSel );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.id * ($)', array( 3, 4, 5 ) )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT * FROM users AS u WHERE (u.id IN (3, 4, 5))',
        	strval( $oSel )
        );
		
		unset( $oSel );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.id * ($)', 3 )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT * FROM users AS u WHERE (u.id = 3)',
        	strval( $oSel )
        );
		
		unset( $oSel );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->from( 'users', 'u' )
			->where( 'u.id * ($)', array( 3 ) )
		;
 		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT * FROM users AS u WHERE (u.id = 3)',
        	strval( $oSel )
        );
		
		unset( $oSel );

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		// Code added August 10, 2013
		
		$oSel = new Geko_Sql_Select();
		$oSel
			->field( 'bar' )
			->from( 'foo' )
			->where( 'bar * (?)', array( 'apple', 'banana', 'grape' ) )
		;
		
		$this->assertEquals(
			"SELECT bar FROM foo WHERE (bar IN ('apple', 'banana', 'grape'))",
			strval( $oSel )
		);
        
		// remove all where clauses
		$oSel->unsetWhere();
		
		$oSel->where( 'bar * (?)', 'mouse' );

		$this->assertEquals(
			"SELECT bar FROM foo WHERE (bar = 'mouse')",
			strval( $oSel )
		);
		
		unset( $oSel );
		
	}
    
    //
    public function testDeferredSubselect() {
    	
		$oSel1 = new Geko_Sql_Select();
		$oSel2 = new Geko_Sql_Select();
		
		$oSel2
			->from( 'users', 'u' )
			->joinLeft( $oSel1, 'i' )
				->on( 'i.user_id = u.id' )
		;
    	
    	// late
    	$oSel1->from( 'invoice', 'i' );
    	
 		// echo strval( $oSel2 );
        $this->assertEquals(
        	'SELECT * FROM users AS u LEFT JOIN (SELECT * FROM invoice AS i) AS i ON (i.user_id = u.id)',
        	strval( $oSel2 )
        );
        
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
        
        $oSubSel = new Geko_Sql_Select();
        $oSubA = new Geko_Sql_Select();
        
		$oSel3 = new Geko_Sql_Select();
        $oSel3
        	->field( 'id' )
        	->field( 'name' )
        	->field( $oSubA, 'sub_a' )
        	->field( array( 'CONCAT(?)', array( 'Moe', 'Larry', 'Curly' ) ), 'stooges' )
        	->from( 'foo' )
        	->where( 'slug IN ?', $oSubSel )
        ;
        
        // defer
        $oSubSel
        	->field( 'slug' )
        	->from( 'bar' )
        	->where( 'type = 5' )	
        ;
		
		$oSubA
        	->field( 'slug' )
        	->from( 'baz' )
        	->where( 'id = 123' )	
        ;
		
        // echo strval( $oSel3 );
    }
    
    //
    public function testUnsetClause() {
    	
		$oSel = new Geko_Sql_Select();
		
		$oSel
			->field( 'u.id', 'id' )
			->field( 'u.first_name', 'first_name' )
			->field( 'u.last_name', 'last_name' )
			->field( 'u.id', 'id' )
			->from( 'users', 'u' )
		;
    	
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT u.id AS id, u.first_name AS first_name, u.last_name AS last_name FROM users AS u',
        	strval( $oSel )
        );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
    	
    	$oSel->unsetClause( Geko_Sql_Select::FIELD, 'last_name' );
		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT u.id AS id, u.first_name AS first_name FROM users AS u',
        	strval( $oSel )
        );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
    	$oSel->unsetClause( Geko_Sql_Select::FIELD );
		
 		//print strval( $oSel );
        $this->assertEquals(
        	'SELECT * FROM users AS u',
        	strval( $oSel )
        );
    }
    
    /* /
	throw new PHPUnit_Framework_IncompleteTestError(
		'This test has not been implemented yet.'
	);
    /* */
    
}


