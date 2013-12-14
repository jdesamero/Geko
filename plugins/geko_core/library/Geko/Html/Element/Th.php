<?php

class Geko_Html_Element_Th extends Geko_Html_Element
{
	
	protected $_sElem = 'th';
	
	// standard
	protected $_aValidAtts = array( 'colspan', 'headers', 'rowspan', 'scope' );
	
	// html 4 only
	protected $_aValidAtts4 = array(
		'abbr', 'align', 'axis', 'bgcolor', 'char', 'charoff', 'height', 'nowrap', 'valign', 'width'
	);
	
	
	
}

