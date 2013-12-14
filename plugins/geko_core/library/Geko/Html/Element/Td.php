<?php

class Geko_Html_Element_Td extends Geko_Html_Element
{
	
	protected $_sElem = 'td';
	
	// standard
	protected $_aValidAtts = array( 'colspan', 'headers', 'rowspan', 'scope' );
	
	// html 4 only
	protected $_aValidAtts4 = array(
		'abbr', 'align', 'axis', 'bgcolor', 'char', 'charoff', 'height', 'nowrap', 'valign', 'width'
	);	
	
	
	
}

