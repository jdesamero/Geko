<?php

class Geko_Html_Element_A extends Geko_Html_Element
{
	
	protected $_sElem = 'a';
	
	// standard
	protected $_aValidAtts = array( 'href', 'hreflang', 'rel', 'target' );
	
	// html 4 only
	protected $_aValidAtts4 = array( 'charset', 'coords', 'name', 'rev', 'shape' );
	
	// html 5 only
	protected $_aValidAtts5 = array( 'media', 'type' );
	
	
	
	
}

