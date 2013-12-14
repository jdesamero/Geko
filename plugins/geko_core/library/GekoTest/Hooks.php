<?php

//
class GekoTest_Hooks extends Geko_PhpUnit_TestCase
{
	
	protected $aChanges;
	
	
	//
	public function setUp() {
		$this->aChanges = array();
	}
	
	//
	public function tearDown() {
		unset( $this->aChanges );
	}
	
	
	//// test actions
	
	//
	public function testBasicAction() {
		
		$sAction = 'do_basic';
		
		Geko_Hooks::addAction( $sAction, array( $this, 'doStuff' ) );
		
		$this->assertEquals( Geko_Hooks::hasAction( $sAction ), TRUE );
		
		$this->assertEquals( Geko_Hooks::didAction( $sAction ), FALSE );
		
		// make changes to the fixture
		Geko_Hooks::doAction( $sAction );
		
		// check fixture
		$this->assertEquals( $this->aChanges, array( 'doStuff' => 'Was Called!' ) );
		
		$this->assertEquals( Geko_Hooks::didAction( $sAction ), TRUE );
		
		// remove action
		Geko_Hooks::removeAction( $sAction );
		
		$this->assertEquals( Geko_Hooks::hasAction( $sAction ), FALSE );
				
	}
	
	//
	public function testActionPriority() {
		
		$sAction = 'do_priority';
		
		// 500 is default priority
		Geko_Hooks::addAction( $sAction, array( $this, 'doStuff' ) );
		Geko_Hooks::addAction( $sAction, array( $this, 'doBeforeStuff' ), array(), 400 );
		
		Geko_Hooks::doAction( $sAction );
		
		$this->assertEquals( $this->aChanges,
			array(
				'doBeforeStuff' => 'Was Called Before doStuff!',
				'doStuff' => 'Was Called!'
			)
		);
		
		// clean-up
		Geko_Hooks::removeAction( $sAction );
		
	}
	
	//
	public function testActionParameters() {
		
		$sAction = 'do_parameters';
		
		Geko_Hooks::addAction( $sAction, array( $this, 'addStuff' ), array( 'One' ) );
		Geko_Hooks::addAction( $sAction, array( $this, 'addStuff' ), array( 'Two' ) );
		Geko_Hooks::addAction( $sAction, array( $this, 'addStuff' ), array( 'Three' ) );
		
		Geko_Hooks::doAction( $sAction );
		
		$this->assertEquals( $this->aChanges, array( 'One', 'Two', 'Three' ) );
		
		// clean-up
		Geko_Hooks::removeAction( $sAction );
		
	}
	
	
	//// test filters
	
	//
	public function testBasicFilter() {
		
		$sFilter = 'apply_basic';
		
		Geko_Hooks::addFilter( $sFilter, array( $this, 'applyStuff' ) );
		
		$this->assertEquals( Geko_Hooks::hasFilter( $sFilter ), TRUE );
		
		$this->assertEquals( Geko_Hooks::appliedFilter( $sFilter ), FALSE );
		
		// apply filter to value
		$sSubject = 'Subject';
		$sSubject = Geko_Hooks::applyFilter( $sFilter, $sSubject );
		
		// check fixture
		$this->assertEqualsHtml( $sSubject, '<span class="stuff">Subject</span>' );
		
		$this->assertEquals( Geko_Hooks::appliedFilter( $sFilter ), TRUE );
		
		// remove action
		Geko_Hooks::removeFilter( $sFilter );
		
		$this->assertEquals( Geko_Hooks::hasFilter( $sFilter ), FALSE );
		
	}
	
	
	//
	public function testFilterPriority() {
		
		$sFilter = 'apply_priority';
		
		// 500 is default priority
		Geko_Hooks::addFilter( $sFilter, array( $this, 'applyStuff' ) );
		Geko_Hooks::addFilter( $sFilter, array( $this, 'applyBeforeStuff' ), array(), 400 );
		
		$sSubject = 'Subject';
		$sSubject = Geko_Hooks::applyFilter( $sFilter, $sSubject );
		
		$this->assertEqualsHtml( $sSubject,
			'<div class="before_stuff"><span class="stuff">Subject</span></div>'
		);
		
		// clean-up
		Geko_Hooks::removeFilter( $sFilter );
		
	}
	
	
	//
	public function testFilterParameters() {
		
		$sFilter = 'do_parameters';
		
		Geko_Hooks::addFilter( $sFilter, array( $this, 'wrapStuff' ), array( 'one' ), 1 );
		Geko_Hooks::addFilter( $sFilter, array( $this, 'wrapStuff' ), array( 'two' ), 1 );
		Geko_Hooks::addFilter( $sFilter, array( $this, 'wrapStuff' ), array( 'three' ), 1 );
		
		$sSubject = 'Subject';
		$sSubject = Geko_Hooks::applyFilter( $sFilter, $sSubject );
		
		$this->assertEqualsHtml( $sSubject,
			'<div class="three"><div class="two"><div class="one">Subject</div></div></div>'
		);
		
		// clean-up
		Geko_Hooks::removeFilter( $sFilter );

		Geko_Hooks::addFilter( $sFilter, array( $this, 'decorate' ), array( 'em' ), 1 );
		Geko_Hooks::addFilter( $sFilter, array( $this, 'decorate' ), array( 'span', null, 'word' ), 1 );
		Geko_Hooks::addFilter( $sFilter, array( $this, 'decorate' ), array( 'div', null, 'cool' ), 1 );

		$sSubject = 'Cool';
		$sSubject = Geko_Hooks::applyFilter( $sFilter, $sSubject );
		
		$this->assertEqualsHtml( $sSubject,
			'<div class="cool"><span class="word"><em>Cool</em></span></div>'
		);
		
		// clean-up
		Geko_Hooks::removeFilter( $sFilter );
		
	}
	
	
	
	//// callbacks
	
	//
	public function doStuff() {
		$this->aChanges[ 'doStuff' ] = 'Was Called!';
	}
	
	//
	public function doBeforeStuff() {
		$this->aChanges[ 'doBeforeStuff' ] = 'Was Called Before doStuff!';	
	}
	
	//
	public function addStuff( $sValue ) {
		$this->aChanges[] = $sValue;	
	}
	
	//
	public function applyStuff( $sValue ) {
		return '<span class="stuff">' . $sValue . '</span>';
	}
	
	//
	public function applyBeforeStuff( $sValue ) {
		return '<div class="before_stuff">' . $sValue . '</div>';
	}	
	
	//
	public function wrapStuff( $sClass, $sValue ) {
		return '<div class="' . $sClass . '">' . $sValue . '</div>';
	}
	
	//
	public function decorate( $sTag, $sValue, $sClass = '' ) {
		if ( $sClass ) $sClass = ' class="' . $sClass . '"';
		return '<' . $sTag . $sClass . '>' . $sValue . '</' . $sTag . '>';
	}
	
}

