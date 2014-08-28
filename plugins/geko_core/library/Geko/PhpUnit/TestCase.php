<?php

require_once( sprintf(
	'%s/external/libs/pearpkgs/PHPUnit-3.4.14/library/PHPUnit/Framework.php',
	dirname( dirname( dirname( dirname( __FILE__ ) ) ) )
) );

//
class Geko_PhpUnit_TestCase extends PHPUnit_Framework_TestCase
{

	// apply htmlspecialchars to values
	public function assertEqualsHtml( $sVal1, $sVal2 ) {
		return $this->assertEquals(
			htmlspecialchars( $sVal1 ),
			htmlspecialchars( $sVal2 )
		);
	}

}

