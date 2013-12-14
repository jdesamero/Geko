<?php

// abstract class that transforms an indented list into a tree structure
abstract class Geko_IndentedList_Abstract
{
	protected $_aLevels;
	
	protected $_aRelation;
	protected $_aTree;
	
	// constructor
	public function __construct( $aLevels ) {
				
		$this->_aLevels = $aLevels;
		
		$this->_aRelation = self::assignParent( $this->_aLevels );
		$this->_aTree = self::createTree( $this->_aRelation );
		
	}
	
	// transform $aLevels into a parent/child relation
	public static function assignParent( $aLevels ) {
		
		$aLastLevel = array();
		$aRelation = array();
		
		foreach ( $aLevels as $i => $iLevel ) {
			
			if ( isset( $aLastLevel[ $iLevel - 1 ] ) ) {
				$aRelation[ $i ] = $aLastLevel[ $iLevel - 1 ];
			} else {
				$aRelation[ $i ] = NULL;
			}
			
			$aLastLevel[ $iLevel ] = $i;
			$iPrevLevel = $iLevel;
		}
		
		return $aRelation;
		
	}
	
	// recursive, transform $aRelation into a nested array structure
	public static function createTree( $aRelation, $iIndex = NULL ) {
		
		$aTree = array();
		foreach ( $aRelation as $i => $iParentIndex ) {
			if ( $iIndex === $iParentIndex ) {
				unset( $aRelation[ $i ] );
				$aTree[ $i ] = self::createTree( $aRelation, $i );
			}
		}
		
		if ( count( $aTree ) > 0 ) {
			return $aTree;
		} else {
			return NULL;
		}
	}
	
	// recursive, transform $aRelation into a composite tree structure
	public static function createComposite( $aRelation, $oParams, $iIndex = NULL ) {
		
		if ( $oParams->class_name_callback ) {;
			$sClass = call_user_func_array( $oParams->class_name_callback, array( $oParams, $iIndex ) );
		} else {
			$sClass = $oParams->class_name;
		}
		
		$oComp = new $sClass;		// must implement Geko_Composite_Interface
		
		if ( $oComp instanceof Geko_Composite_Interface ) {
			
			foreach ( $aRelation as $i => $iParentIndex ) {
				if ( $iIndex === $iParentIndex ) {
					
					unset( $aRelation[ $i ] );
					
					$oCompChild = self::createComposite( $aRelation, $oParams, $i );
					$oCompChild
						->setParams( $oParams, $i )
						->setParent( $oComp )
						->setUp()
					;
					
					$oComp->setChild( $oCompChild );
				}
			}
			
			return $oComp;
			
		} else {
			throw new Exception( $sClass . ' does not implement Geko_Composite_Interface.' );
		}
		
	}
	
}

