<?php

//
class GekoTest_Class extends Geko_PhpUnit_TestCase
{
	
	
	
	//
	public function providerExistsCoalesce() {
		return array(
			array( array( 'GekoX_Test_Bogus', 'GekoX_Test_Simple', 'GekoX_Test_MultiKey' ), 'GekoX_Test_Simple' ),
			array( array( array( 'GekoX_Test_Bogus', 'GekoX_Test_Simple' ), 'GekoX_Test_MultiKey' ), 'GekoX_Test_Simple' ),			
			array( array( 'GekoX_Test_Bogus', 'GekoX_Test_BogusAgain', 'GekoX_Test_MultiKey' ), 'GekoX_Test_MultiKey' )
		);
	}
	
	
	/**
	 * @dataProvider providerExistsCoalesce
	 */
	public function testExistsCoalesce( $aArgs, $sResult ) {
		$this->assertEquals( $sResult,
			call_user_func_array( array( 'Geko_Class', 'existsCoalesce' ), $aArgs )
		);
	}
	
	
	//
	public function providerResolveRelatedClass() {
		return array(
			array( 'GekoX_Test_Simple', '', '_Query', '', 'GekoX_Test_Simple_Query' ),
			array( 'GekoX_Test_Simple_Query', '_Query', '', '', 'GekoX_Test_Simple' ),
			array( 'GekoX_Test_Simple_QueryFoo', '_Query*', '', '', 'GekoX_Test_Simple' ),
			array( 'GekoX_Test_Simple_QueryBar', '_Query*', '', '', 'GekoX_Test_Simple' ),
			array( 'GekoX_Test_Simple_Query', '_Query', '_Manage', '', 'GekoX_Test_Simple_Manage' ),
			array( '', '', '', 'GekoX_Test_Simple', 'GekoX_Test_Simple' )
		);
	}
	
	
	/**
	 * @dataProvider providerResolveRelatedClass
	 */
	public function testResolveRelatedClass( $mBaseClass, $sBaseSuffix, $sRelatedSuffix, $sResolvedClass, $sResult ) {
		$this->assertEquals( $sResult,
			Geko_Class::resolveRelatedClass( $mBaseClass, $sBaseSuffix, $sRelatedSuffix, $sResolvedClass )
		);
	}
	
	
}

