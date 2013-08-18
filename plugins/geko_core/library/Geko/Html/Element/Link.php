<?php

class Geko_Html_Element_Link extends Geko_Html_Element
{
	
	protected $_sElem = 'link';
	protected $_bHasContent = FALSE;
	
	
	
	// standard
	protected $_aValidAtts = array( 'href', 'hreflang', 'media', 'rel', 'type' );
	
	// html 4 only
	protected $_aValidAtts4 = array( 'charset', 'rev', 'target' );
	
	// html 5 only
	protected $_aValidAtts5 = array( 'sizes' );
	
	
	
}

