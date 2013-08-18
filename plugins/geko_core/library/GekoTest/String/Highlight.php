<?php

//
class GekoTest_String_Highlight extends PHPUnit_Framework_TestCase
{
	//
    public function testHighlight() {
    	
		$sTest = 'This is a test.';
		
		$oHl = new Geko_String_Highlight();
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oHl->setParams( array( 'keywords' => 'this' ) );
		
        $this->assertEquals(
        	'<strong>This</strong> is a test.',
        	$oHl->searchHighlight( $sTest )
        );
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oHl->setParams( array( 'keywords' => 'test this' ) );
		
        $this->assertEquals(
        	'<strong>This</strong> is a <strong>test</strong>.',
        	$oHl->searchHighlight( $sTest )
        );

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$oHl->setParams( array(
			'keywords' => 'test this',
			'start_highlight' => '<i>',
			'end_highlight' => '</i>'			
		) );
		
        $this->assertEquals(
        	'<i>This</i> is a <i>test</i>.',
        	$oHl->searchHighlight( $sTest )
        );
        
	}
	
}

