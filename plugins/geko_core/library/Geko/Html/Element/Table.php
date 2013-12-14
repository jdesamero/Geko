<?php

class Geko_Html_Element_Table extends Geko_Html_Element
{
	
	protected $_sElem = 'table';
	
	// html 4 only
	protected $_aValidAtts4 = array(
		'align', 'bgcolor', 'border', 'cellpadding', 'cellspacing', 'frame', 'rules',
		'summary', 'width'
	);
	
	
	
}

