<?php

//
class Geko_Html
{
	
	//
	public static function getElemsGroup( $sHtml ) {
		
		$oDoc = Geko_PhpQuery_FormTransform::createDoc( $sHtml );
		
		$aElemsGroup = Geko_PhpQuery_FormTransform::getGroupedFormElems(
			$oDoc->find( 'input, textarea, select' )
		);
		
		return $aElemsGroup;
	}
	
	//
	public static function populateForm( $sHtml, $mValues, $bReturnDoc = FALSE ) {
		
		if ( is_object( $mValues ) ) {
			$aValues = array();
			foreach ( $mValues as $sKey => $mValue ) $aValues[ $sKey ] = $mValue;
		} else {
			$aValues = $mValues;
		}
		
		
		$oDoc = Geko_PhpQuery_FormTransform::createDoc( $sHtml );
		
		list( $oDoc, $aValues ) = Geko_PhpQuery_FormTransform::modifyDoc( $oDoc, $aValues );
		
		$aElemsGroup = Geko_PhpQuery_FormTransform::getGroupedFormElems(
			$oDoc->find( 'input, textarea, select' )
		);
		
		foreach ( $aElemsGroup as $sPrefixedGroupName => $aElem ) {				
			// check if option exists
			if ( isset( $aValues[ $sPrefixedGroupName ] ) ) {
				Geko_PhpQuery_FormTransform::setElemValue(
					$aElem,
					$aValues[ $sPrefixedGroupName ]
				);
			} else {
				Geko_PhpQuery_FormTransform::setElemDefaultValue( $aElem );
			}
		}
		
		Geko_PhpQuery_FormTransform::cleanUpNonHtml( $oDoc );
		
		return ( $bReturnDoc ) ? $oDoc : strval( $oDoc );
		
	}
	
	//// attribute helpers
	
	// format an associative array into an HTML attributes string
	public static function formatAsAtts( $aAtts ) {
		
		$sOut = '';
		foreach ( $aAtts as $sKey => $sValue ) {
			$sOut .= $sKey . '="' . htmlspecialchars( strval( $sValue ) ) . '" ';
		}
		
		return trim( $sOut );
	}
	
	// assign a value to an attribute array, if the value is not empty
	public static function assignAtt( $aAtts, $sKey, $sValue ) {
		if ( $sValue ) $aAtts[ $sKey ] = $sValue;
		return $aAtts;
	}
	
	//
	public static function assignAtts( $aAtts, $aMerge ) {
		
		foreach ( $aMerge as $sKey => $sValue ) {
			$aAtts = self::assignAtt( $aAtts, $sKey, $sValue );
		}
		
		return $aAtts;
	}
	
}


