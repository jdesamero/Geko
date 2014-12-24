<?php

class Geko_Html_Element_Img extends Geko_Html_Element
{
	
	protected $_sElem = 'img';
	protected $_bHasContent = FALSE;
	
	
	
	// standard
	protected $_aValidAtts = array( 'alt', 'height', 'ismap', 'src', 'usemap', 'width' );
	
	// html 4 only
	protected $_aValidAtts4 = array( 'border', 'align', 'hspace', 'longdesc', 'vspace' );
	
	// html 5 only
	protected $_aValidAtts5 = array( 'crossorigin' );
	
	
	
}

