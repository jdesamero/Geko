<?php

//
class Geko_PhpQuery_FormTransform_Plugin_RowTemplate extends Geko_PhpQuery_FormTransform_Plugin_Abstract
{
	protected static $mValues;
		
	//
	public static function modifyDoc( $oDoc, $aOptions ) {
		
		$aRowTemplates = $oDoc->find( '*[_row_template]' );
		
		foreach ( $aRowTemplates as $oElem ) {
			
			$oPq = pq( $oElem );
			$sKey = $oPq->attr( '_row_template' );
			
			$aRowVals = ( is_array( $aOptions[ $sKey ] ) ) ? $aOptions[ $sKey ] : array();
						
			// $aRow is unused
			foreach ( $aRowVals as $i => $aRow ) {
				$oPqCopy = $oPq->clone();
				
				$aIdElems = $oPqCopy->find( 'input[id], select[id], textarea[id]' );
				foreach ( $aIdElems as $oElem2 ) {
					
					$oPq2 = pq( $oElem2 );
					
					$sId = $oPq2->attr( 'id' );
					$sNewId = str_replace( $sKey . '[]', $sKey . '[' . $i . ']', $sId );

					$sName = $oPq2->attr( 'name' );
					$sNewName = str_replace( $sKey . '[]', $sKey . '[' . $i . ']', $sName );
					
					$oPq2
						->attr( 'id', $sNewId )
						->attr( 'name', $sNewName )
					;
				}
				
				$oPq->before( $oPqCopy );
			}
			
			$oPq->addClass( '_row_template' );
		}
		
		return array( $oDoc, self::flatten( $aOptions ) );
	}
	
	// clean-up non-html tags
	public static function cleanUpNonHtml( $oDoc ) {
		$oDoc->find( '*[_row_template]' )->removeAttr( '_row_template' );
	}
	
	// helpers
	public static function flatten( $aOptions ) {
		$aOptionsFlat = Geko_Array::flatten( $aOptions );
		$aUnflat = array();
		foreach ( $aOptionsFlat as $sKey => $sValue ) {
			$aRegs = array();
			if ( preg_match( '/(.+)\[([0-9]+)\]$/', $sKey, $aRegs ) ) {
				$aUnflat[ $aRegs[1] ][ $aRegs[2] ] = $sValue;
				unset( $aOptionsFlat[ $sKey ] );
			}
		}
		
		return array_merge( $aOptionsFlat, $aUnflat );
	}
	
}


