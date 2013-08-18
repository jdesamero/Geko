<?php

// listing
class Geko_Wp_Role_Types
	extends Geko_Singleton_Abstract
	implements Iterator, ArrayAccess
{
	
	//
	protected $_aRoleTypes = array();
	protected $_aRoleTypeHash = array();
	protected $_aOffsets = array();
	protected $_iPos = 0;
	
	
	
	//
	public function register( $mType )
	{
		if ( is_scalar( $mType ) && class_exists( $mType ) ) {
			$oType = Geko_Singleton_Abstract::getInstance( $mType );
		} elseif ( is_object( $mType ) ) {
			$oType = $mType;
		}
		
		if ( $oType ) {
			$oType->init();		// call init
			$this->_aOffsets[ $oType->getCode() ] = count( $this->_aRoleTypes );
			$this->_aRoleTypeHash[ $oType->getCode() ] = $oType;
			$this->_aRoleTypes[] = $oType;
		}
		
		return $this;
	}
	
	
	//
	public function getRoleTypeObject( $sTypeCode )
	{
		if ( isset( $this->_aRoleTypeHash[ $sTypeCode ] ) ) {
			return $this->_aRoleTypeHash[ $sTypeCode ];
		} else {
			return NULL;
		}
	}

	//
	public function getRoleTypeOffset( $sTypeCode )
	{
		if ( isset( $this->_aOffsets[ $sTypeCode ] ) ) {
			return $this->_aOffsets[ $sTypeCode ];
		} else {
			return NULL;
		}
	}
	
	// call reconcileAssigned() on all registered types
	public function reconcileAssigned()
	{
		foreach ( $this->_aRoleTypes as $oRoleTypes ) {
			$oRoleTypes->reconcileAssigned();
		}
		
		return $this;
	}
	
	//
	public function getPseudoRoleTypeId( $mParam )
	{
		if ( is_string( $mParam ) ) {
			$sSuffix = $mParam;
		} else {
			if ( $oType = $this->offsetGet( intval( $mParam ) ) ) {
				$sSuffix = $oType->getCode();
			}
		}
		
		// prefix with 'all-'
		// is 'all-' the only available pseudo type ???
		return ( $sSuffix ) ? 'all-' . $sSuffix : '';
	}
	
	//
	public function getCurrentType()
	{
		foreach ( $this->_aRoleTypes as $iOffset => $oType ) {
			if (
				( $oRewrite = $oType->getRoleRewrite() ) && 
				( $oRewrite->isList() ) && 
				( $sValue = $oType->getRoleDefaultEntityValue() )
			) {
				if ( 'all' == $sValue ) {
					return $this->getPseudoRoleTypeId( $iOffset );
				} else {
					return $sValue;
				}
			}
		}
	}
	
	
	//// Iterator interface methods
	
	//
	public function rewind()
	{
		$this->_iPos = 0;
	}
	
	//
	public function current()
	{
		return $this->_aRoleTypes[ $this->_iPos ];
	}

	//
	public function key()
	{
		return $this->_iPos;
	}
	
	//
	public function next()
	{
		++$this->_iPos;
	}

	//
	public function valid()
	{
		return isset( $this->_aRoleTypes[ $this->_iPos ] );
	}


	//// ArrayAccess interface methods
	
	//
	public function offsetSet( $iOffset, $mValue )
	{
		$this->_aRoleTypes[ $iOffset ] = $mValue;
	}
	
	//
	public function offsetExists( $iOffset )
	{
		return isset( $this->_aRoleTypes[ $iOffset ] );
	}
	
	//
	public function offsetUnset( $iOffset )
	{
		unset( $this->_aRoleTypes[ $iOffset ] );
	}
	
	//
	public function offsetGet( $iOffset )
	{
		return isset( $this->_aRoleTypes[ $iOffset ] ) ? 
			$this->_aRoleTypes[ $iOffset ] :
			NULL;
	}
	
	
}


