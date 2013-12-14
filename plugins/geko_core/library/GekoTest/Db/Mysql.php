<?php

//
class GekoTest_Db_Mysql extends Geko_PhpUnit_TestCase
{

	//
	public function testDate() {
		
		$this->assertEquals(
			Geko_Db_Mysql::getTimestamp( 1256179727 ),
			'2009-10-22 02:48:47'
		);
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$this->assertEquals(
			Geko_Db_Mysql::getTimestamp(),
			date( 'Y-m-d H:i:s' )
		);
		
	}

}


