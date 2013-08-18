<?php

// convert a flat array with a numeric field signifying levels to a nested array
class Geko_IndentedList_AssociativeArray extends Geko_IndentedList_Abstract
{
	protected $aFields;
	
	protected $sChildrenKey;
	protected $sLevelsKey;
	
	
	// constructor
	public function __construct( $aFlat, $sChildrenKey = 'children', $sLevelsKey = 'levels' ) {
		
		$this->sLevelsKey = $sLevelsKey;
		$this->sChildrenKey = $sChildrenKey;
		
		//
		
		$aLevels = array();
		$aFields = array();
		
		foreach ( $aFlat as $i => $aItem ) {
			$aLevels[ $i ] = $aItem[ $this->sLevelsKey ];
			unset( $aItem[ $this->sLevelsKey ] );
			$aFields[ $i ] = $aItem;
		}
		
		$this->aFields = $aFields;
		
		parent::__construct( $aLevels );
		
	}
	
	//
	public function getTree() {
		return $this->buildTree( $this->_aTree );
	}
	
	//
	public function buildTree( $aItems ) {
		
		$aRes = array();
		
		foreach ( $aItems as $i => $aChildren ) {
			
			if ( is_array( $aChildren ) ) {
				$this->aFields[ $i ][ $this->sChildrenKey ] = $this->buildTree( $aChildren );
			}
			
			$aRes[] = $this->aFields[ $i ];
		}
		
		return $aRes;
	}
	
}

