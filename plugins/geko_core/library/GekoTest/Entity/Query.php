<?php

//
class GekoTest_Entity_Query extends Geko_PhpUnit_TestCase
{
	
	protected static $aSimple;
	
	//
	public static function setUpBeforeClass() {
		
		$aColors = array(
			'red' => 1,
			'green' => 2,
			'yellow' => 3,
			'orange' => 4
		);
		
		$aSimple = new GekoX_Test_Simple_Query();
		$aSimple->setRawEntities( array(
			array( 'id' => 1, 'title' => 'Apple', 'type' => 'fruit', 'color_id' => $aColors[ 'red' ] ),
			array( 'id' => 2, 'title' => 'Broccoli', 'type' => 'vegetable', 'color_id' => $aColors[ 'green' ] ),
			array( 'id' => 3, 'title' => 'Strawberry', 'type' => 'fruit', 'color_id' => $aColors[ 'red' ] ),
			array( 'id' => 5, 'title' => 'Bacon', 'type' => 'meat', 'color_id' => $aColors[ 'red' ] ),
			array( 'id' => 20, 'title' => 'Steak', 'type' => 'meat', 'color_id' => $aColors[ 'red' ] ),
			array( 'id' => 7, 'title' => 'Banana', 'type' => 'fruit', 'color_id' => $aColors[ 'yellow' ] ),
			array( 'id' => 11, 'title' => 'Carrot', 'type' => 'vegetable', 'color_id' => $aColors[ 'orange' ] )
		) );
		
		self::$aSimple = $aSimple;
	}
	
	//
	public static function tearDownAfterClass() {
		self::$aSimple = NULL;
	}


	//
	public function testBasic() {	
		
		$aSimple = self::$aSimple;
		
		$this->assertEquals( $aSimple->count(), 7 );
	}
	
	//
	public function testGather() {

		$aSimple = self::$aSimple;
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$this->assertEquals(
			$aSimple->gatherId(),
			array( 1, 2, 3, 5, 20, 7, 11 )
		);
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$this->assertEquals(
			$aSimple->gather( '##Id##' ),
			array( 1, 2, 3, 5, 20, 7, 11 )
		);
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$this->assertEquals(
			$aSimple->gather( '##Id## - ##Title##' ),
			array(
				'1 - Apple',
				'2 - Broccoli',
				'3 - Strawberry',
				'5 - Bacon',
				'20 - Steak',
				'7 - Banana',
				'11 - Carrot'
			)
		);
		
	}

	//
	public function testSubset() {
		
		$aSimple = self::$aSimple;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$this->assertEquals(
			$aSimple->subsetType(),
			array( 'fruit', 'vegetable', 'meat' )
		);
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$aVeg = $aSimple->subsetType( 'vegetable' );
		
		$this->assertEquals(
			$aVeg->gatherTitle(),
			array( 'Broccoli', 'Carrot' )
		);
		
		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$aRed = $aSimple->subsetColorId( 1 );
		
		$this->assertEquals(
			$aRed->gatherTitle(),
			array( 'Apple', 'Strawberry', 'Bacon', 'Steak' )
		);
		
	}
	
	//
	public function testImplode() {

		$aSimple = self::$aSimple;

		// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  //
		
		$aFruit = $aSimple->subsetType( 'fruit' );
		
		$this->assertEquals( $aFruit->implodeId(), '1, 3, 7' );
		$this->assertEquals( $aFruit->implode( '##Title##' ), 'Apple, Strawberry, Banana' );
		
		$this->assertEquals(
			$aFruit->implode( array( '<a href="###Id##">##Title##</a>', '|' ) ),
			'<a href="#1">Apple</a>|<a href="#3">Strawberry</a>|<a href="#7">Banana</a>'
		);
		
	}
	
}


