<?php

//
class GekoTest_IndentedList_AssociativeArray extends Geko_PhpUnit_TestCase
{

	
	protected static $aFlat = array(
		array( 'level' => 1, 'item' => 'plant' ),
			array( 'level' => 2, 'item' => 'vine' ),
				array( 'level' => 3, 'item' => 'grape' ),
				array( 'level' => 3, 'item' => 'ivy' ),
				array( 'level' => 3, 'item' => 'sweet pea' ),
			array( 'level' => 2, 'item' => 'tree' ),
				array( 'level' => 3, 'item' => 'pine' ),
				array( 'level' => 3, 'item' => 'oak' ),
				array( 'level' => 3, 'item' => 'cedar' ),
				array( 'level' => 3, 'item' => 'maple' ),
				array( 'level' => 3, 'item' => 'mahogany' ),
				array( 'level' => 3, 'item' => 'birch' ),
		array( 'level' => 1, 'item' => 'animal' ),
			array( 'level' => 2, 'item' => 'rodent' ),
			array( 'level' => 2, 'item' => 'primate' ),
			array( 'level' => 2, 'item' => 'fish' ),
		array( 'level' => 1, 'item' => 'thing' ),
			array( 'level' => 2, 'item' => 'machine' ),
				array( 'level' => 3, 'item' => 'pulley' ),
				array( 'level' => 3, 'item' => 'lever' ),
				array( 'level' => 3, 'item' => 'wedge' ),
					array( 'level' => 4, 'item' => 'chisel' ),
					array( 'level' => 4, 'item' => 'axe' ),
					array( 'level' => 4, 'item' => 'cutlass' ),
					array( 'level' => 4, 'item' => 'knife' ),
				array( 'level' => 3, 'item' => 'screw' ),
			array( 'level' => 2, 'item' => 'furniture' ),
			array( 'level' => 2, 'item' => 'appliance' ),
		array( 'level' => 1, 'item' => 'country' ),
			array( 'level' => 2, 'item' => 'france' ),
			array( 'level' => 2, 'item' => 'uk' ),
			array( 'level' => 2, 'item' => 'usa' ),
			array( 'level' => 2, 'item' => 'germany' )
	);
	
	protected static $aTree;
	
	
	//
	public static function setUpBeforeClass() {
		$oTree = new Geko_IndentedList_AssociativeArray(
			self::$aFlat, 'children', 'level'
		);
		self::$aTree = $oTree->getTree();	
	}
	
	//
	public static function tearDownAfterClass() {
		self::$aTree = NULL;
	}
	
	
	//
	public function provider() {
		return array(
			array( '', array( 'plant', 'animal', 'thing', 'country' ) ),
			array( '[0][children]', array( 'vine', 'tree' ) ),
			array( '[1][children]', array( 'rodent', 'primate', 'fish' ) ),
			array( '[2][children]', array( 'machine', 'furniture', 'appliance' ) ),
			array( '[3][children]', array( 'france', 'uk', 'usa', 'germany' ) ),
			array( '[0][children][0][children]', array( 'grape', 'ivy', 'sweet pea' ) ),
			array( '[0][children][1][children]', array( 'pine', 'oak', 'cedar', 'maple', 'mahogany', 'birch' ) ),
			array( '[2][children][0][children]', array( 'pulley', 'lever', 'wedge', 'screw' ) ),
			array( '[2][children][0][children][2][children]', array( 'chisel', 'axe', 'cutlass', 'knife' ) )
		);
	}
	
	
	/**
	 * @dataProvider provider
	 */
	public function testBasic( $sKey, $aResult ) {
		$aItems = $this->getItems( Geko_Array::getElement( self::$aTree, $sKey ) );
		$this->assertEquals( $aItems, $aResult );
	}
	
	
	//
	public function getItems( $aTree ) {
		$aItems = array();
		foreach ( $aTree as $aItem ) {
			$aItems[] = $aItem[ 'item' ];
		}
		return $aItems;
	}
	
	
}

