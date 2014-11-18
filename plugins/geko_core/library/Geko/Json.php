<?php

// wrapper for Zend_Json
class Geko_Json
{
	
	// 
	public static function encode() {
		
		$aArgs = func_get_args();
		
		$aArgs[ 0 ] = self::encodeFormat( $aArgs[ 0 ] );
		
		return call_user_func_array( array( 'Zend_Json', 'encode' ), $aArgs );
	}
	
	
	// format "special" values
	public static function encodeFormat( $mValue ) {
		
		if ( is_array( $mValue ) ) {
			
			foreach ( $mValue as $mKey => $mSubValue ) {
				$mValue[ $mKey ] = self::encodeFormat( $mSubValue );
			}
			
		} else if ( $mValue instanceof Geko_Entity_Query ) {
			
			// re-assign
			$oQuery = $mValue;
			
			$mValue = $oQuery->getRawEntities( TRUE );
			
		} else if ( $mValue instanceof Geko_Sql_Table ) {

			// re-assign
			$oTable = $mValue;
			
			$mValue = array();
			
			$aFields = $oTable->getFields( TRUE );
			
			foreach ( $aFields as $sKey => $oField ) {
				
				$mDefValue = $oField->getDefaultValue();
				
				$mValue[ $sKey ] = array(
					'value' => $oField->getAssertedValue( $mDefValue ),
					'format' => $oField->getAssertedType()
				);
				
				if ( $oField->hasFlag( 'uniquecheck' ) ) {
					$mValue[ $sKey ][ 'uniqueCheck' ] = TRUE;
				}
			}
			
		}
		
		return $mValue;
	}
	
	
	//
	public static function decode() {

		$aArgs = func_get_args();
		
		return call_user_func_array( array( 'Zend_Json', 'decode' ), $aArgs );
	}
	
}

