<?php

class Geko_Html_Element_Input extends Geko_Html_Element
{
	
	protected $_sElem = 'input';
	protected $_bHasContent = FALSE;
	
	// standard
	protected $_aValidAtts = array(
		'accept', 'alt', 'checked', 'disabled', 'maxlength', 'name', 'readonly', 'size', 'src',
		'type', 'value'
	);
	
	// html 4 only
	protected $_aValidAtts4 = array( 'align' );
	
	// html 5 only
	protected $_aValidAtts5 = array(
		'autocomplete', 'autofocus', 'form', 'formaction', 'formenctype', 'formmethod',
		'formnovalidate', 'formtarget', 'height', 'list', 'max', 'min', 'multiple',
		'pattern', 'placeholder', 'required', 'step', 'width'
	);
	
	
	
}


