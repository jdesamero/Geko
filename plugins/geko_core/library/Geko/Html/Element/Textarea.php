<?php

class Geko_Html_Element_Textarea extends Geko_Html_Element
{
	
	protected $_sElem = 'textarea';
	
	// standard
	protected $_aValidAtts = array( 'cols', 'disabled', 'name', 'readonly', 'rows' );
	
	// html 4 only
	protected $_aValidAtts4 = array();
	
	// html 5 only
	protected $_aValidAtts5 = array(
		'autofocus', 'form', 'maxlength', 'placeholder', 'required', 'wrap'
	);
	
	
	
}

