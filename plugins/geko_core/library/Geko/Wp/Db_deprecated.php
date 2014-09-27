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
	
	
}



