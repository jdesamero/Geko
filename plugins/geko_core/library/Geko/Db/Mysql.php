<?php

//
class Geko_Db_Mysql
{
		
	//
	public static function getHierarchyPathQuery(
		$sFuncName, $sTableName, $sIdFieldName, $sParentFieldName, $sWhereCondition = ''
	) {
		
		//
		return sprintf(
			
			'CREATE FUNCTION %s(delimiter TEXT, node INT) RETURNS TEXT
			NOT DETERMINISTIC
			READS SQL DATA
			BEGIN
					DECLARE _path TEXT;
					DECLARE _cpath TEXT;
					DECLARE _id INT;
					DECLARE EXIT HANDLER FOR NOT FOUND RETURN _path;
					
					SET _id = COALESCE(node, @id);
					SET _path = _id;
					
					LOOP
							SELECT  %s
							INTO    _id
							FROM    %s
							WHERE   %s = _id
									%s
									AND COALESCE(%s <> @start_with, TRUE);
							
							SET _path = CONCAT(_id, delimiter, _path);
					
					END LOOP;
			END',
			
			$sFuncName,
			$sParentFieldName,
			$sTableName,
			$sIdFieldName,
			$sWhereCondition,
			$sIdFieldName
			
		);
	}
	
	
	//
	public static function getHierarchyConnectQuery(
		$sFuncName, $sTableName, $sIdFieldName, $sParentFieldName, $sWhereCondition = ''
	) {
		return sprintf(
		
			'CREATE FUNCTION %s(value INT, maxlevel INT) RETURNS INT
			NOT DETERMINISTIC
			READS SQL DATA
			BEGIN
					DECLARE _id INT;
					DECLARE _parent INT;
					DECLARE _next INT;
					DECLARE _i INT;
					DECLARE CONTINUE HANDLER FOR NOT FOUND SET @id = NULL;
			
					SET _parent = @id;
					SET _id = -1;
					SET _i = 0;
			
					IF @id IS NULL THEN
							RETURN NULL;
					END IF;
			
					LOOP
							SELECT  MIN(%s)
							INTO    @id
							FROM    %s
							WHERE   %s = _parent
									%s
									AND %s > _id
									-- Checking for @start_with in descendants
									AND %s <> @start_with
									AND COALESCE(@level < maxlevel, TRUE);
							
							IF @id IS NOT NULL OR _parent = @start_with THEN
									SET @level = @level + 1;
									RETURN @id;
							END IF;
							
							SET @level := @level - 1;
							
							SELECT  %s, %s
							INTO    _id, _parent
							FROM    %s
							WHERE   %s = _parent
									%s;
							
							SET _i = _i + 1;
					END LOOP;
					
					RETURN NULL;
			END',
			
			$sFuncName,
			$sIdFieldName,
			$sTableName,
			$sParentFieldName,
			$sWhereCondition,
			$sIdFieldName,
			$sIdFieldName,
			$sIdFieldName,
			$sParentFieldName,
			$sTableName,
			$sIdFieldName,
			$sWhereCondition
			
		);
		
	}
	
	
	//
	public static function connectDb( $aParams ) {
		
		$sDbName = $aParams[ 'db' ];
		$sDbUser = $aParams[ 'user' ];
		$sDbPwd = $aParams[ 'pwd' ];
		$sDbHost = ( $aParams[ 'host' ] ) ? $aParams[ 'host' ] : 'localhost';
		
		$rLink = mysql_connect( $sDbHost, $sDbUser, $sDbPwd );
		
		if ( !$rLink ) {
			echo sprintf( "Could not connect: %s\n", mysql_error() );
			return FALSE;
		}
		
		mysql_select_db( $sDbName );
		
		return $rLink;
	}
	
	// insert one record
	public static function insert( $sTable, $aValues ) {
		
		$sQuery = sprintf( 'INSERT INTO %s ( ', $sTable );
		
		$aFields = array_keys( $aValues );
		
		$sQuery .= implode( ', ', $aFields );
		$sQuery .= ' ) VALUES ( ';
		
		$bFirst = TRUE;
		foreach ( $aValues as $sValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ', ';
			$sQuery .= sprintf( "'%s'", addslashes( $sValue ) );
		}
		
		$sQuery .= ' )';
		
		return mysql_query( $sQuery );
	}
	
	// insert multiple record
	public static function insertMulti( $sTable, $aValues ) {
		
		$aRes = array();
		foreach ( $aValues as $i => $aRow ) {
			if ( self::insert( $sTable, $aRow ) ) {
				$aRes[ $i ] = array( 'insert_id' => self::insert_id() );
			} else {
				$aRes[ $i ] = FALSE;
			}
		}
		
		return $aRes;
	}
	
	// update one record
	public static function update( $sTable, $aValues, $aKeys ) {
	
		$sQuery = sprintf( 'UPDATE %s SET ', $sTable );
		
		$bFirst = TRUE;
		foreach ( $aValues as $sField => $sValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ', ';	
			$sQuery .= sprintf( " %s = '%s'", $sField, addslashes( $sValue ) );
		}
		
		$sQuery .= ' WHERE ';
		
		$bFirst = TRUE;
		foreach ( $aKeys as $sField => $sValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ' AND ';	
			$sQuery .= sprintf( " ( %s = '%s' ) ", $sField, addslashes( $sValue ) );
		}
		
		return mysql_query( $sQuery );
	}
	
	// update multiple record
	public static function updateMulti( $sTable, $aValues ) {
		
		$aRes = array();
		foreach ( $aValues as $i => $aRow ) {
			if ( self::update( $sTable, $aRow[ 0 ], $aRow[ 1 ] ) ) {
				$aRes[ $i ] = array(
					'keys' => $aRow[ 1 ],
					'affected_rows' => mysql_affected_rows()
				);
			} else {
				$aRes[ $i ] = FALSE;
			}
		}
		
		return $aRes;
	}
	
	// delete one record
	public static function delete( $sTable, $aKeys ) {
		
		$sQuery = sprintf( 'DELETE FROM %s WHERE ', $sTable );
		
		$bFirst = TRUE;
		foreach ( $aKeys as $sFldName => $mValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ' AND ';
			$sQuery .= sprintf( " ( %s = '%s' ) ", $sFldName, addslashes( $mValue ) );
		}
		
		return mysql_query( $sQuery );
	}
	
	// delete multiple records
	public static function deleteMulti( $sTable, $aKeys ) {
		
		$aRes = array();
		foreach ( $aKeys as $i => $aRow ) {
			if ( self::delete( $sTable, $aRow ) ) {
				$aRes[ $i ] = array(
					'keys' => $aRow,
					'affected_rows' => mysql_affected_rows()
				);
			} else {
				$aRes[ $i ] = FALSE;
			}
		}
		
		return $aRes;
	}
	
	// helper, format array of ids for use in self::deleteMulti
	public static function formatDeleteKeys( $sField, $aIds ) {
		$aIds = is_array( $aIds ) ? $aIds : array( $aIds );
		$aRes = array();
		foreach ( $aIds as $iId ) {
			$aRes[] = array( $sField => $iId );
		}
		return $aRes;
	}
	
	// check if table exists
	public static function tableExists( $sTableName ) {
		$rRes = mysql_query( sprintf( "SHOW TABLES LIKE '%s'", $sTableName ) );
		return mysql_num_rows( $rRes ) ? TRUE : FALSE;	
	}
	
	// get the first table that exists from the argument list
	public static function tableCoalesce() {
		$aArgs = func_get_args();
		foreach ( $aArgs as $sTable ) {
			if ( self::tableExists( $sTable ) ) {
				return $sTable;
			}
		}
		return NULL;
	}
	
	// check if the specified fields are present in the table
	public static function tableHasFields( $sDbName, $sTableName, $aCheckFields ) {
		$aFields = self::tableGetFieldNames( $sDbName, $sTableName );
		foreach ( $aCheckFields as $sField ) {
			if ( !in_array( $sField, $aFields ) ) return FALSE;
		}
		return TRUE;
	}
	
	// get the number of fields in a table
	public static function tableNumFields( $sDbName, $sTableName ) {
		
		$sQuery = sprintf(
			"SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '%s' AND table_name = '%s'",
			$sDbName,
			$sTableName
		);
		
		$rRes = mysql_query( $sQuery );
		return self::fetchValue( $rRes );
	}
	
	// get the field names in a table
	public static function tableGetFieldNames( $sDbName, $sTableName ) {
		
		$aFields = array();
		
		$sQuery = sprintf( 'SHOW FIELDS FROM %s.%s', $sDbName, $sTableName );
		$rRes = mysql_query( $sQuery );
		
		while( $aRow = mysql_fetch_array( $rRes ) ) {
			$aFields[] = $aRow[ 'Field' ];
		}
		
		return $aFields;
	}
	
	// get the number of rows of a table
	public static function tableNumRows( $sTableName ) {
		$rRes = mysql_query( sprintf( 'SELECT COUNT(*) FROM %s', $sTableName ) );
		$aRes = mysql_fetch_row( $rRes );
		return $aRes[ 0 ];
	}
	
	// check if database exists
	public static function dbExists( $sDbName ) {
		$rRes = mysql_query( sprintf( "SHOW DATABASES LIKE '%s'", $sDbName ) );
		return mysql_num_rows( $rRes ) ? TRUE : FALSE;	
	}
	
	// fetch single value from result set
	// $mField can be offset position or field name
	public static function fetchValue( $rRes, $mField = 0 ) {
		if ( is_resource( $rRes ) ) {
			mysql_data_seek( $rRes, 0 );			// reset pointer
			$aRes = mysql_fetch_array( $rRes );
			return $aRes[ $mField ];
		}
		return NULL;
	}
	
	// fetch result set as one-dimensional array (column)
	// $mField can be offset position or field name
	public static function fetchColNum( $rRes, $mField = 0 ) {
		if ( is_resource( $rRes ) ) {
			mysql_data_seek( $rRes, 0 );			// reset pointer
			$aRet = array();
			while( $aRow = mysql_fetch_array( $rRes ) ) {
				$aRet[] = $aRow[ $mField ];
			}
			return $aRet;
		}
		return NULL;
	}
	
	// fetch result set as one-dimensional array (column)
	// $mKeyField and $mValField can be offset position or field name
	public static function fetchColAssoc( $rRes, $mKeyField = 0, $mValField = 1 ) {
		if ( is_resource( $rRes ) ) {
			mysql_data_seek( $rRes, 0 );			// reset pointer
			$aRet = array();
			while( $aRow = mysql_fetch_array( $rRes ) ) {
				$aRet[ $aRow[ $mKeyField ] ] = $aRow[ $mValField ];
			}		
			return $aRet;	
		}
		return NULL;
	}

	
	
	//// helpers for Geko_Entity_Query class
	
	// deal with vendor specific functionality
	
	//
	public static function getUserRoutines( $oDb = NULL ) {
		
		if ( !$oDb ) {
			$oDb = Geko::get( 'db' );
		}
		
		if ( $oDb ) {
			return $oDb->fetchCol( "
				SELECT			SPECIFIC_NAME
				FROM 			information_schema.routines
				WHERE			( ROUTINE_SCHEMA = '%s' )
			", $oDb->getDbName() );
		}
		
		return FALSE;
	}
	
	//
	public static function getRoutineExistsQuery( $sRoutineName, $sDbName ) {
		
		//
		return sprintf(
			
			"SELECT			1 AS routine_exists
			FROM 			information_schema.routines
			WHERE			( SPECIFIC_NAME = '%s' ) AND 
							( ROUTINE_SCHEMA = '%s' )",
			
			$sRoutineName,
			$sDbName
		);
	}
	
	//
	public static function getTimestamp( $iTimestamp = NULL ) {
		if ( NULL == $iTimestamp ) $iTimestamp = time();
		return @date( 'Y-m-d H:i:s', $iTimestamp );
	}
	
	//
	public static function gekoQueryInit( $oQuery, $aParams ) {
		
		$oQuery->option( 'SQL_CALC_FOUND_ROWS' );
		
		return $oQuery;
	}
	
	//
	public static function gekoQueryOrderRandom( $oQuery, $aParams ) {
		
		$oQuery->order( 'RAND()', '', 'random' );
		
		return $oQuery;
	}
	
	//
	public static function gekoQueryFoundRows( $oEntityQuery ) {
		return 'SELECT FOUND_ROWS()';	
	}
	
	
	
	
}


