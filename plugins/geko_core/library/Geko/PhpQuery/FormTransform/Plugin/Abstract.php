<?php

// add functionality to Geko_PhpQuery_FormTransform
class Geko_PhpQuery_FormTransform_Plugin_Abstract
{
	//
	public static function modifyDoc( $oDoc, $aOptions ) {
		return array( $oDoc, $aOptions );
	}
	
	//
	public static function modifyGroupedFormElem( $aElem, $sPrefixedGroupName ) {
		return $aElem;
	}
	
	//
	public static function modifyGroupedFormElems( $aElemsGroup, $oPqFormElems, $sPrefix = '' ) {
		return $aElemsGroup;
	}
	
	//
	public static function setElemDefaultValue( $aElem ) { }
	
	//
	public static function setElemValue( $aElem, $mOptionVal ) { }
	
	//
	public static function cleanUpNonHtml( $oDoc ) { }
	
}


