<?php

//
class Geko_Wp_Db
{
	
	//
	public static function generateSlug( $sTitle, $mTable, $sSlugField ) {
		
		global $wpdb;
		
		if ( is_a( $mTable, 'Geko_Sql_Table' ) ) {
			$sTable = $mTable->getTableName();
		} elseif ( is_string( $mTable ) ) {
			$sTable = ( $wpdb->$mTable ) ? $wpdb->$mTable : $mTable ;
		}
		
		if ( !$sTable ) return '';
		
		$sName = sanitize_title_with_dashes( $sTitle );
		$sName = preg_replace( '/-[0-9]+$/', '', $sName );	// strip trailing digits, if any
		
		$aMatches = $wpdb->get_col( sprintf(
			
			"SELECT				%s
			FROM				%s
			WHERE				( %s RLIKE '%s-[0-9]+' ) OR
								( %s = '%s' )",
			
			$sSlugField,
			$sTable,
			$sSlugField,
			$wpdb->escape( $sName ),
			$sSlugField,
			$wpdb->escape( $sName )
		
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
	public static function addPrefix( $sTableName ) {
		
		global $wpdb;
		
		if ( !$wpdb->$sTableName ) {
			$wpdb->$sTableName = sprintf( '%s%s', $wpdb->prefix, $sTableName );
		}
	}
	
	
	// NOTE: To be deprecated
	// Use $oDb = Geko_Wp::get( 'db' ); $oDb->tableCreateIfNotExists() instead
	
	/* /
	public static function createTable() {
		
		global $wpdb;
		
		$aArgs = func_get_args();
		
		if ( count( $aArgs ) == 1 ) {
			
			$oSqlTable = $aArgs[ 0 ];
			$sTableName = $oSqlTable->getTableName();
			$sSql = strval( $oSqlTable );
		
		} elseif ( count( $aArgs ) == 2 ) {
			
			list( $sTableName, $sSql ) = $aArgs;
		}
		
		$sTableName = ( $wpdb->$sTableName ) ? $wpdb->$sTableName : $sTableName ;
		
		if ( $sTableName && $sSql && !self::tableExists( $sTableName ) ) {
			
			require_once( sprintf( '%swp-admin/includes/upgrade.php', ABSPATH ) );
			
			return dbDelta( $sSql );
		}
		
		return FALSE;
	}
	/* */
	
	
	//
	public static function createHierarchyPathFunction(
		$sTable, $sIdField, $sParentIdField, $sWhereCondition = ''
	) {
		global $wpdb;
		
		$sTablePrefixed = sprintf( '%s%s', $wpdb->prefix, $sTable );
		$sFuncName = sprintf( '%s_path', $sTablePrefixed );
		$sSql = Geko_Db_Mysql::getRoutineExistsQuery( $sFuncName, DB_NAME );
		
		if ( !$wpdb->get_var( $sSql ) ) {
			
			$sQuery = Geko_Db_Mysql::getHierarchyPathQuery(
				$sFuncName, $sTablePrefixed, $sIdField, $sParentIdField, $sWhereCondition
			);
			
			$bRes = $wpdb->query( $sQuery );
			return $bRes;
			
		} else {
			return TRUE;
		}
		
	}
	
	
	//
	public static function createHierarchyConnectFunction(
		$sTable, $sIdField, $sParentIdField, $sWhereCondition = ''
	) {
		global $wpdb;
		
		$sTablePrefixed = sprintf( '%s%s', $wpdb->prefix, $sTable );
		$sFuncName = sprintf( '%s_connect', $sTablePrefixed );
		$sSql = Geko_Db_Mysql::getRoutineExistsQuery( $sFuncName, DB_NAME );
		
		if ( !$wpdb->get_var( $sSql ) ) {
			
			$sQuery = Geko_Db_Mysql::getHierarchyConnectQuery(
				$sFuncName, $sTablePrefixed, $sIdField, $sParentIdField, $sWhereCondition
			);
			
			$bRes = $wpdb->query( $sQuery );
			return $bRes;
			
		} else {
			return TRUE;
		}
		
	}
	
	
	//
	public static function getResultsHash( $sQuery, $sHashKey, $sOutputType = 'OBJECT' ) {
		
		global $wpdb;
		
		$aRet = array();
		$aRes = $wpdb->get_results( $sQuery, $sOutputType );
		
		foreach ( $aRes as $mItem ) {
			if ( is_array( $mItem ) ) {
				$aRet[ $mItem[ $sHashKey ] ] = $mItem;
			} else {
				$aRet[ $mItem->$sHashKey ] = $mItem;			
			}
		}
		
		return $aRet;
	}
	
	
	// first column is array key, second column is the value
	public static function getPair( $sQuery ) {
		
		global $wpdb;
		
		$aRet = array();
		$aRes = $wpdb->get_results( $sQuery, 'ARRAY_N' );
		
		foreach ( $aRes as $aRow ) {
			$aRet[ $aRow[ 0 ] ] = $aRow[ 1 ];
		}
		
		return $aRet;
		
	}
	
	
	// takes ##d## and ##s## arguments similar to %d and %s
	public static function prepare() {
		
		global $wpdb;
		
		$aArgs = func_get_args();
		$sExpression = array_shift( $aArgs );
		
		$aRegs = array();
		if ( preg_match_all( '/##([ds])##/', $sExpression, $aRegs ) ) {
			
			$aPatterns = $aRegs[0];
			$aTypes = $aRegs[1];
			
			foreach ( $aPatterns as $i => $sPattern ) {
				
				$mValue = $aArgs[ $i ];
				$sType = $aTypes[ $i ];
				$sReplace = '';
				
				if (
					( is_scalar( $mValue ) ) && 
					( FALSE !== strpos( $mValue, ',' ) )
				) {
					// format as array
					$mValue = explode( ',', $mValue );
				}
				
				if ( is_array( $mValue ) ) {
					$mValue = array_map( 'trim', $mValue );
					
					if ( 'd' == $sType ) {
						$mValue = array_map( 'intval', $mValue );
						$sReplace = implode( ', ', $mValue );
					} else {
						$mValue = array_map( array( $wpdb, 'escape' ), $mValue );
						$sReplace = sprintf( "'%s'", implode( "', '", $mValue ) );					
					}
					
					$sReplace = sprintf( ' IN (%s) ', $sReplace );
					
				} else {

					if ( 'd' == $sType ) {
						$mValue = intval( $mValue );
					} else {
						$mValue = sprintf( "'%s'", $wpdb->escape( $mValue ) );					
					}
					
					$sReplace = sprintf( ' = %s ', $mValue );
				}
				
				$sExpression = substr_replace( $sExpression, $sReplace, strpos( $sExpression, $sPattern ), strlen( $sPattern ) );
				
			}
		}
		
		return $sExpression;
	}
	
	
	//
	public static function keywordSearch( $sKeywords, $aFields ) {
		
		global $wpdb;
		
		$aKeywords = Geko_Array::explodeTrim(
			' ', $sKeywords, array( 'remove_empty' => TRUE )
		);
		
		$aMain = array();
		foreach ( $aKeywords as $sKeyword ) {
			$aExp = array();
			foreach ( $aFields as $sField ) {
				$aExp[] = sprintf( " ( %s LIKE '%%%s%%' ) ", $sField, $wpdb->escape( $sKeyword ) );	
			}
			$aMain[] = sprintf( ' ( %s ) ', implode( ' OR ', $aExp ) );
		}
		
		return sprintf( ' ( %s ) ', implode( ' AND ', $aMain ) );
	}
	
	
	
	//// wrappers for $wpdb
	
	//
	public static function formatValues( $aValues ) {
		
		$aVal = array();
		$aValFmt = array();
		
		foreach ( $aValues as $sKey => $mValue ) {
			$aKeyFmt = explode( ':', $sKey );
			$aVal[ $aKeyFmt[ 0 ] ] = $mValue;
			$aValFmt[] = ( $aKeyFmt[ 1 ] ) ? $aKeyFmt[ 1 ] : '%s' ;
		}
		
		return array( $aVal, $aValFmt );
	}
	
	//
	public static function insert( $sTable, $aValues ) {
		
		global $wpdb;
		
		list( $aVal, $aValFmt ) = self::formatValues( $aValues );
		
		return $wpdb->insert( $wpdb->$sTable, $aVal, $aValFmt );
	}
	
	//
	public static function insertMulti( $sTable, $aMultiValues ) {
		
		$aRetVals = array();
		
		foreach ( $aMultiValues as $aValues ) {
			$aRetVals[] = self::insert( $sTable, $aValues );
		}
		
		return $aRetVals;
	}
	
	//
	public static function update( $sTable, $aValues, $aKeys ) {
		
		global $wpdb;
		
		list( $aVal, $aValFmt ) = self::formatValues( $aValues );
		list( $aKey, $aKeyFmt ) = self::formatValues( $aKeys );
		
		return $wpdb->update( $wpdb->$sTable, $aVal, $aKey, $aValFmt, $aKeyFmt );	
	}
	
	// TO DO: implement later
	public static function delete( $sTable, $aValues, $aKeys ) {
		// stub
	}
	
	
	
	//// misc utility functions
	
	//
	public static function tableExists( $sTable ) {
		
		global $wpdb;
		
		$sQuery = sprintf( "SHOW TABLES LIKE '%s'", $sTable );
		return ( $wpdb->get_var( $sQuery ) == $sTable ) ? TRUE : FALSE ;
	}
	
	//
	public static function getTableNumRows( $sTable ) {
		
		global $wpdb;
		
		$sTableName = $wpdb->$sTable;
		
		$sQuery = sprintf( 'SELECT COUNT(*) AS num_rows FROM %s', $sTableName );
		
		if ( self::tableExists( $sTableName ) ) {
			return intval( $wpdb->get_var( $sQuery ) );
		}
		
		return FALSE;
	}
	
	
}



