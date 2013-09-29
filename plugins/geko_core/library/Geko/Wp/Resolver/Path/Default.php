<?php

//
class Geko_Wp_Resolver_Path_Default extends Geko_Wp_Resolver_Path
{
	
	protected $_aPrefixes = array( 'Gloc_Layout_', 'Geko_Layout_' );
	
	//
	public function isMatch() {
		return TRUE;
	}
		
}


