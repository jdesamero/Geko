<?php

class Geko_Html_Element_Select extends Geko_Html_Element
{
	
	protected $_sElem = 'select';
	
	// standard
	protected $_aValidAtts = array( 'disabled', 'multiple', 'name', 'size' );
	
	// html 5 only
	protected $_aValidAtts5 = array( 'autofocus', 'form' );
	
	
	
	
}

