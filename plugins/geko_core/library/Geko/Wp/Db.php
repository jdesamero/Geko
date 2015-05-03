<?php

//
class Geko_Wp_Db
{
	
	//
	public static function generateSlug( $sTitle, $mTable, $sSlugField ) {
		
		global $wpdb;
		$oDb = Geko_Wp::get( 'db' );
		
		if ( is_a( $mTable, 'Geko_Sql_Table' ) ) {
			$sTable = $mTable->getTableName();
		} elseif ( is_string( $mTable ) ) {
			$sTable = ( $wpdb->$mTable ) ? $wpdb->$mTable : $mTable ;
		}
		
		if ( !$sTable ) return '';
		
		$sName = sanitize_title_with_dashes( $sTitle );
		$sName = preg_replace( '/-[0-9]+$/', '', $sName );	// strip trailing digits, if any
		
		$aMatches = $oDb->fetchCol( sprintf(
			
			"SELECT				%s
			FROM				%s
			WHERE				( %s RLIKE '%s-[0-9]+' ) OR
								( %s = '%s' )",
			
			$sSlugField,
			$sTable,
			$sSlugField,
			$oDb->quote( $sName ),
			$sSlugField,
			$oDb->quote( $sName )
		
		) );
		
		if ( 0 == count( $aMatches ) ) {
			return $sName;										// not in db
		}
		
		//// continue, list matches and find the nearest gap or increment the max
		$aFilter = array( 0 );
		$bHasIndexless = FALSE;
		foreach ( $aMatches as $sSlug ) {
			if ( $sSlug == $sName ) {
				$bHasIndexless = TRUE;
			} else {
				$iIndex = intval( str_replace( sprintf( '%s-', $sName ), '', $sSlug ) );
				$aFilter[ $iIndex ] = $iIndex;			
			}
		}
		
		if ( !$bHasIndexless ) return $sName;					// index-less slug is available
		
		sort( $aFilter );
		foreach ( $aFilter as $i => $iIndex ) {
			if ( $iIndex != $i ) return sprintf( '%s-%d', $sName, $i );		// gap found
		}
		
		return sprintf( '%s-%d', $sName, count( $aFilter ) );				// increment max
	}
	
	
	
	//
	public static function createHierarchyPathFunction(
		$sTable, $sIdField, $sParentIdField, $sWhereCondition = ''
	) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$sTablePrefixed = $oDb->_p( $sTable );
		$sFuncName = sprintf( '%s_path', $sTablePrefixed );
		
		if ( !$oDb->routineExists( $sFuncName ) ) {
			
			$sQuery = Geko_Db_Mysql::getHierarchyPathQuery(
				$sFuncName, $sTablePrefixed, $sIdField, $sParentIdField, $sWhereCondition
			);
			
			return $oDb->routineCreateIfNotExists( $sFuncName, $sQuery );
			
		} else {
			return TRUE;
		}
		
	}
	
	
	//
	public static function createHierarchyConnectFunction(
		$sTable, $sIdField, $sParentIdField, $sWhereCondition = ''
	) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$sTablePrefixed = $oDb->_p( $sTable );
		$sFuncName = sprintf( '%s_connect', $sTablePrefixed );
		
		if ( !$oDb->routineExists( $sFuncName ) ) {
			
			$sQuery = Geko_Db_Mysql::getHierarchyConnectQuery(
				$sFuncName, $sTablePrefixed, $sIdField, $sParentIdField, $sWhereCondition
			);
			
			return $oDb->routineCreateIfNotExists( $sFuncName, $sQuery );
			
		} else {
			return TRUE;
		}
		
	}
	
	
	//
	public static function keywordSearch( $sKeywords, $aFields ) {
		
		global $wpdb;
		$oDb = Geko_Wp::get( 'db' );
		
		$aKeywords = Geko_Array::explodeTrim(
			' ', $sKeywords, array( 'remove_empty' => TRUE )
		);
		
		$aMain = array();
		foreach ( $aKeywords as $sKeyword ) {
			$aExp = array();
			foreach ( $aFields as $sField ) {
				$aExp[] = sprintf( " ( %s LIKE '%%%s%%' ) ", $sField, $oDb->quote( $sKeyword ) );	
			}
			$aMain[] = sprintf( ' ( %s ) ', implode( ' OR ', $aExp ) );
		}
		
		return sprintf( ' ( %s ) ', implode( ' AND ', $aMain ) );
	}
	
	
	
}



