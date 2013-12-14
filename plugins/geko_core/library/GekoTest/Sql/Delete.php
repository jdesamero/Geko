<?php

//
class GekoTest_Sql_Delete extends Geko_PhpUnit_TestCase
{
	//
    public function testBasicDelete() {

		$oDel = new Geko_Sql_Delete();
		$oDel
			->from( 'foo' )
			->where( 'bar * (?)', array( 'apple', 'banana', 'grape' ) )
			->where( 'goo = ?', 'foot' )
		;
		
		$this->assertEquals(
			"DELETE FROM foo WHERE (bar IN ('apple', 'banana', 'grape'))  AND (goo = 'foot')",
			strval( $oDel )
		);
				
    }
    
    /* /
	throw new PHPUnit_Framework_IncompleteTestError(
		'This test has not been implemented yet.'
	);
    /* */
    
}


