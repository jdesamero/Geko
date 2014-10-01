<?php

//
class Geko_Wp_Db
{
	
	
	// NOTE: Deprecated
	// use of $oDb->_p( 'some_table' ) should be used in favour of $wpdb->some_table 
	// for now, $wpdb->some_table is handled by $oDb->registerTable and $oDbAdapted->registerTableName
	
	/* /
	//
	public static function addPrefix( $sTableName ) {
		
		global $wpdb;
		
		if ( !$wpdb->$sTableName ) {
			$wpdb->$sTableName = sprintf( '%s%s', $wpdb->prefix, $sTableName );
		}
	}
	/* */
	
	
	
	// NOTE: Deprecated
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
	
	
	// NOTE: Deprecated
	// Use $oDb->fetchPairs( $sSql ) instead
	
	/* /
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
	/* */
	
	
	
	//// misc utility functions

	// NOTE: Deprecated
	// Use $oDb->tableExists( $sTable ) instead
		
	/* /
	public static function tableExists( $sTable ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$sQuery = sprintf( "SHOW TABLES LIKE '%s'", $sTable );
		return ( $oDb->fetchOne( $sQuery ) == $sTable ) ? TRUE : FALSE ;
	}
	/* */
	
	
	// NOTE: Deprecated
	// Use $oDb->getTableNumRows( $sTable ) instead
	
	/* /
	public static function getTableNumRows( $sTable ) {
		
		global $wpdb;
		$oDb = Geko_Wp::get( 'db' );
		
		$sTableName = $wpdb->$sTable;
		
		$sQuery = sprintf( 'SELECT COUNT(*) AS num_rows FROM %s', $sTableName );
		
		if ( self::tableExists( $sTableName ) ) {
			return intval( $oDb->fetchOne( $sQuery ) );
		}
		
		return FALSE;
	}
	/* */
	
	
	// NOTE: Deprecated
	// Use $oDb->fetchHashObj( $sQuery, $sHashKey ), or
	// Use $oDb->fetchHashAssoc( $sQuery, $sHashKey ), or
	// Use $oDb->fetchHashNum( $sQuery, $sHashKey ) instead
	
	/* /
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
	/* */
	
	

	// NOTE: Deprecated
	// Use $oDb->insert( $sTable, $aValues )
	// Use $oDb->insertMulti( $sTable, $aMultiValues )
	// Use $oDb->update( $sTable, $aValues, $aWhere )
	// Use $oDb->delete( $sTable, $aWhere )
	
	/* /
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
	
	/* */
	
	
	/* /
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
	/* */
	
	
}



