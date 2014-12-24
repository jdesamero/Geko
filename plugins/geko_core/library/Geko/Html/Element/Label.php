<?php

class Geko_Html_Element_Label extends Geko_Html_Element
{
	
	protected $_sElem = 'label';
	
	
	
	// standard
	protected $_aValidAtts = array( 'for' );
	
	// html 5 only
	protected $_aValidAtts5 = array( 'form' );
	
	
	
}

