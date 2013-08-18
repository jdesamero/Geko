<?php

class Geko_Html_Element_Ol extends Geko_Html_Element
{
	
	protected $_sElem = 'ol';
	
	
	
	// standard
	protected $_aGlobalAtts = array( 'start', 'type' );
	
	// html 4 only
	protected $_aValidAtts4 = array( 'compact' );
	
	// html 5 only
	protected $_aGlobalAtts5 = array( 'reversed' );
	
	
	
}

