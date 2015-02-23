<?php

//
class Geko_Util_Rows
{
	
	protected $_mRows;
	
	//
	public function __construct( $mRows ) {
		
		$this->_mRows = $mRows;
		
	}
	
	//
	public function getFirst() {
		
		$mRows = $this->_mRows;
		
		if ( is_array( $mRows ) ) {
			
			return current( $mRows );
			
		} elseif ( $mRows instanceof Geko_Entity_Query ) {
			
			return $mRows->getOne();
		}
		
		return array();
	}
	
}


