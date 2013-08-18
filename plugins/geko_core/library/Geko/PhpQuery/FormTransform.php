<?php

// a set of static classes that transform form elements of a phpQuery $doc
class Geko_PhpQuery_FormTransform
{
	
	//
	protected static $aPlugins = array();
	
	
	//
	public static function registerPlugin( $sPluginClass ) {
		if (
			( !self::$aPlugins[ $sPluginClass ] ) && 
			( @class_exists( $sPluginClass ) ) && 
			( is_subclass_of( $sPluginClass, __CLASS__ . '_Plugin_Abstract' ) )
		) {
			self::$aPlugins[ $sPluginClass ] = 1;
		}
	}
	
	
	//
	public static function createDoc( $sHtml ) {
		// return $oDoc = phpQuery::newDocumentXHTML( $sHtml );				// stricter, so bound to cause more problems?
		return phpQuery::newDocument( $sHtml );
	}
	
	//
	public static function modifyDoc( $oDoc, $aOptions ) {
		foreach ( self::$aPlugins as $sClassName => $iUnused ) {
			list( $oDoc, $aOptions ) = call_user_func( array( $sClassName, 'modifyDoc' ), $oDoc, $aOptions );
		}
		
		return array( $oDoc, $aOptions );
	}
	
	// group the form elements together as needed
	public static function getGroupedFormElems( $oPqFormElems, $sPrefix = '' ) {
		
		$aElemsGroup = array();
		
		foreach ( $oPqFormElems as $oElem ) {
			
			$oPqElem = pq( $oElem );
			
			// get the type
			$sNodeName = $oElem->nodeName;
			$sType = $sNodeName;
			$sSubType = '';
			
			if ( 'input' == $sType ) {
				
				$sInputType = $oPqElem->attr( 'type' );
				$sType .= ':' . ( ( '' == $sInputType ) ? 'text' : $sInputType );
				$sSubType = $sInputType;
				
			} elseif ( 'select' == $sType ) {
				
				$sMultiple = $oPqElem->attr( 'multiple' );
				if ( '' != $sMultiple ) {
					$sType .= ':multiple';
					$sSubType = $sMultiple;
				}
				
			} elseif ( 'label' == $sType ) {
				
				// don't bother to do anything id $sUnprefixedFor is empty
				if ( $sUnprefixedFor = $oPqElem->attr( 'for' ) ) {
					if ( $sPrefixedFor = trim( $sPrefix . $sUnprefixedFor ) ) {
						$oPqElem->attr( 'for', $sPrefixedFor );
					}
				}
			}
			
			
			// prefix the name and id
			
			// don't bother to do anything id $sUnprefixedId is empty
			if ( $sUnprefixedId = $oPqElem->attr( 'id' ) ) {
				
				$sUnprefixedName = $oPqElem->attr( 'name' );
				
				// don't bother to do anything if $sPrefixedId is empty
				if ( $sPrefixedId = trim( $sPrefix . $sUnprefixedId ) ) {
					$oPqElem->attr( 'id', $sPrefixedId );
				}
				
				if ( '' == $sUnprefixedName ) $sUnprefixedName = $sUnprefixedId;
				
				// don't bother to do anything if $sPrefixedName is empty
				if ( $sPrefixedName = trim( $sPrefix . $sUnprefixedName ) ) {
					
					// name the element group
					if ( 'select:multiple' == $sType ) {
						$oPqElem->attr( 'name', $sPrefixedName . '[]' );
					} elseif ( 'input:checkbox' == $sType ) {
						if ( $sUnprefixedId != $sUnprefixedName ) {
							$oPqElem->attr( 'name', $sPrefixedName . '[]' );		// group of checkboxes
						} else {
							$oPqElem->attr( 'name', $sPrefixedName );				// singleton checkbox
						}
					} else {
						$oPqElem->attr( 'name', $sPrefixedName );
					}
					
					// group together
					$aElemsGroup[ $sPrefixedName ][ 'type' ] = $sType;
					$aElemsGroup[ $sPrefixedName ][ 'nodename' ] = $sNodeName;
					$aElemsGroup[ $sPrefixedName ][ 'subtype' ] = $sSubType;
					$aElemsGroup[ $sPrefixedName ][ 'name' ] = $sUnprefixedName;
					$aElemsGroup[ $sPrefixedName ][ 'prefixed_name' ] = $sPrefixedName;
					
					if ( ( 'input:radio' == $sType ) || ( 'input:checkbox' == $sType ) ) {
						$aElemsGroup[ $sPrefixedName ][ 'elem' ][] = $oPqElem;
					} else {
						$aElemsGroup[ $sPrefixedName ][ 'elem' ] = $oPqElem;
					}
					
					// printf('%s - %s - %s<br />', $sType, $sPrefixedId, $sPrefixedName);
				}
			
			}
			
		}
		
		//// invoke plugins
		
		// iterate each $aElemsGroup item
		foreach ( $aElemsGroup as $sPrefixedGroupName => $aElem ) {
			foreach ( self::$aPlugins as $sClassName => $iUnused ) {
				$aElem = call_user_func(
					array( $sClassName, 'modifyGroupedFormElem' ), $aElem, $sPrefixedGroupName
				);
			}
			$aElemsGroup[ $sPrefixedGroupName ] = $aElem;
		}
		
		// modify entire $aElemsGroup
		foreach ( self::$aPlugins as $sClassName => $iUnused ) {
			$aElemsGroup = call_user_func(
				array( $sClassName, 'modifyGroupedFormElems' ), $aElemsGroup, $oPqFormElems, $sPrefix
			);
		}
		
		return $aElemsGroup;
	}
	
	
	
	//
	public static function setElemDefaultValue( $aElem ) {
		
		// set up some vars
		$sElemType = $aElem[ 'type' ];
		$sNodeName = $aElem[ 'nodename' ];
		$sSubType = $aElem[ 'subtype' ];
		
		if ( ( 'input:radio' == $sElemType ) || ( 'input:checkbox' == $sElemType ) ) {
			
			foreach ( $aElem[ 'elem' ] as $oPqElem ) {
				$sDefaultFlag = $oPqElem->attr( '_is_default' );
				if ( '' != $sDefaultFlag ) {
					$oPqElem->attr( 'checked', 'checked' );
				}
			}
			
		} elseif ( 'select' == $sNodeName ) {
			
			$oSelOptions = $aElem[ 'elem' ]->find( 'option' );
			
			foreach ( $oSelOptions as $oElem ) {
				$oPqElem = pq( $oElem );
				$sDefaultFlag = $oPqElem->attr( '_is_default' );
				if ( '' != $sDefaultFlag ) {
					$oPqElem->attr( 'selected', 'selected' );
				}
			}	
			
		}
		
		// invoke plugins
		foreach ( self::$aPlugins as $sClassName => $iUnused ) {
			call_user_func( array( $sClassName, 'setElemDefaultValue' ), $aElem );
		}
	}
	
	
	
	//
	public static function setElemValue( $aElem, $mOptionVal ) {
		
		// set up some vars
		$sElemType = $aElem[ 'type' ];
		$sNodeName = $aElem[ 'nodename' ];
		$sSubType = $aElem[ 'subtype' ];
		
		if ( 'input:radio' == $sElemType ) {
			
			foreach ( $aElem[ 'elem' ] as $oPqElem ) {
				$sValue = $oPqElem->attr( 'value' );
				if ( $sValue == $mOptionVal ) {
					$oPqElem->attr( 'checked', 'checked' );
				}
			}
			
		} elseif ( 'input:checkbox' == $sElemType ) {

			if ( !is_array( $mOptionVal ) ) $mOptionVal = array( $mOptionVal );
			
			foreach ( $aElem[ 'elem' ] as $oPqElem ) {
				$sValue = $oPqElem->attr( 'value' );
				if ( in_array( $sValue, $mOptionVal ) ) {
					$oPqElem->attr( 'checked', 'checked' );
				}
			}
							
		} elseif ( ( 'input:text' == $sElemType ) || ( 'input:hidden' == $sElemType ) ) {
			
			$aElem[ 'elem' ]->attr( 'value', $mOptionVal );
		
		} elseif ( 'textarea' == $sElemType ) {
			
			$aElem[ 'elem' ]->html( $mOptionVal );
			
		} elseif ( 'select' == $sNodeName ) {
			
			$oSelOptions = $aElem[ 'elem' ]->find( 'option' );
			
			if ( !is_array( $mOptionVal ) ) $mOptionVal = array( $mOptionVal );
			
			foreach ( $oSelOptions as $oElem ) {
				$oPqElem = pq( $oElem );
				$sValue = $oPqElem->attr( 'value' );
				if ( in_array( $sValue, $mOptionVal ) ) {
					$oPqElem->attr( 'selected', 'selected' );
				}
			}
			
		}
		
		// invoke plugins
		foreach ( self::$aPlugins as $sClassName => $iUnused ) {
			call_user_func( array( $sClassName, 'setElemValue' ), $aElem, $mOptionVal );
		}
		
		// printf( '%s - %s - %s<br />', $sPrefixedGroupName, $mOptionVal, $sElemType );
		
	}
	
	
	
	// clean-up non-html tags
	public static function cleanUpNonHtml( $oDoc ) {
		
		$oDoc
			->find( 'input[_is_default], select option[_is_default]' )
			->removeAttr( '_is_default')
		;
		
		// invoke plugins
		foreach ( self::$aPlugins as $sClassName => $iUnused ) {
			call_user_func( array( $sClassName, 'cleanUpNonHtml' ), $oDoc );
		}
		
	}
	
	
}


