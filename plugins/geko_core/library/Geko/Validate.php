<?php
/*
 * "geko_core/library/Geko/Validate.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * a collection of basic input validation methods
 */

//
class Geko_Validate
{

	//
	public function email( $sEmail ) {
		return preg_match( '/^[a-z0-9\._-]+@([a-z0-9_-]+\.)+[a-z]{2,6}$/i', $sEmail );
	}
	
	//
	public function emptyMd5( $sMd5Hash ) {
		return ( 'd41d8cd98f00b204e9800998ecf8427e' == $sMd5Hash ) ? TRUE : FALSE ;
	}
	
	//
	public function md5( $sMd5Hash ) {
		return preg_match( '/^[a-f0-9]{32}$/i', $sMd5Hash );
	}
	
	//
	public function duplicateDbValues( $mTable, $mField, $mValue ) {
		
		$oDb = Geko::get( 'db' );
		
		if ( $mTable instanceof Geko_Sql_Table ) {
			$sTable = $mTable->getTableName();
		} else {
			$sTable = $mTable;
		}
		
		$aFields = Geko_Array::wrap( $mField );
		
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'COUNT(*)', 'num_matches' )
			->from( $sTable )
		;
		
		$aWhere = array();
		foreach ( $aFields as $sField ) {
			$aWhere[] = sprintf( '( %s = ? )', $sField );
		}
		
		$oQuery->where( implode( ' OR ', $aWhere ), $mValue );
		
		if ( $aRes = $oDb->fetchRowAssoc( strval( $oQuery ) ) ) {
			return intval( $aRes[ 'num_matches' ] );
		}
		
		return FALSE;
	}
	
}
