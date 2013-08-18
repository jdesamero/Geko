<?php

class Geko_Html_Element_Script extends Geko_Html_Element
{
	
	protected $_sElem = 'script';
	
	
	
	// standard
	protected $_aValidAtts = array( 'charset', 'defer', 'src', 'type' );
	
	// html 4 only
	protected $_aValidAtts4 = array( 'xml:space' );
	
	// html 5 only
	protected $_aValidAtts5 = array( 'async' );
	
	
	
	
}


