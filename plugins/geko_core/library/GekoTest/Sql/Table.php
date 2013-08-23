<?php

//
class GekoTest_Sql_Table extends Geko_PhpUnit_TestCase
{
	//
    public function testBasicCreate() {
    	
		$oTbl = new Geko_Sql_Table();
		$oTbl
			->create( 'foo', 'f' )
			->fieldBigInt( 'id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'title', array( 'size' => 256 ) )
			->fieldLongText( 'content' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
			->fieldBool( 'is_active', array( 'default' => 1 ) )
		;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
			
        $this->assertEquals(
        	"CREATE TABLE foo ( id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , title VARCHAR(256) , content LONGTEXT , date_created DATETIME , date_modified DATETIME , is_active BOOL DEFAULT '1'  , PRIMARY KEY(id) )",
        	strval( $oTbl )
        );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
			
        $this->assertEquals( $oTbl->hasField( 'mmm' ), FALSE );
        $this->assertEquals( $oTbl->hasField( 'title' ), TRUE );
        $this->assertEquals( $oTbl->hasField( 'TiTle' ), FALSE );
        
    }
	
    
    /* /
	throw new PHPUnit_Framework_IncompleteTestError(
		'This test has not been implemented yet.'
	);
    /* */
    
}


