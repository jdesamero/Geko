<?php

//
class Geko_Db_Mysql
{
	
	//
	public static function getRoutineExistsQuery( $sRoutineName ) {
		return "
			SELECT			*
			FROM 			information_schema.routines
			WHERE			( SPECIFIC_NAME = '" . $sRoutineName . "' ) AND 
							( ROUTINE_SCHEMA = '" . DB_NAME . "' )
		";
	}
	
	//
	public static function getHierarchyPathQuery(
		$sFuncName, $sTableName, $sIdFieldName, $sParentFieldName, $sWhereCondition = ''
	) {
		return '
			CREATE FUNCTION ' . $sFuncName . '(delimiter TEXT, node INT) RETURNS TEXT
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
							SELECT  ' . $sParentFieldName . '
							INTO    _id
							FROM    ' . $sTableName . '
							WHERE   ' . $sIdFieldName . ' = _id
									' . $sWhereCondition . '
									AND COALESCE(' . $sIdFieldName . ' <> @start_with, TRUE);
							
							SET _path = CONCAT(_id, delimiter, _path);
					
					END LOOP;
			END
		';
	}
	
	//
	public static function getHierarchyConnectQuery(
		$sFuncName, $sTableName, $sIdFieldName, $sParentFieldName, $sWhereCondition = ''
	) {
		return '
			CREATE FUNCTION ' . $sFuncName . '(value INT, maxlevel INT) RETURNS INT
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
							SELECT  MIN(' . $sIdFieldName . ')
							INTO    @id
							FROM    ' . $sTableName . '
							WHERE   ' . $sParentFieldName . ' = _parent
									' . $sWhereCondition . '
									AND ' . $sIdFieldName . ' > _id
									-- Checking for @start_with in descendants
									AND ' . $sIdFieldName . ' <> @start_with
									AND COALESCE(@level < maxlevel, TRUE);
							
							IF @id IS NOT NULL OR _parent = @start_with THEN
									SET @level = @level + 1;
									RETURN @id;
							END IF;
							
							SET @level := @level - 1;
							
							SELECT  ' . $sIdFieldName . ', ' . $sParentFieldName . '
							INTO    _id, _parent
							FROM    ' . $sTableName . '
							WHERE   ' . $sIdFieldName . ' = _parent
									' . $sWhereCondition . ';
							
							SET _i = _i + 1;
					END LOOP;
					
					RETURN NULL;
			END
		';
		
	}
	
	//
	public static function getTimestamp( $iTimestamp = NULL ) {
		if ( NULL == $iTimestamp ) $iTimestamp = time();
		return @date( 'Y-m-d H:i:s', $iTimestamp );
	}
	
	
	//
	public static function connectDb( $aParams ) {
		
		$sDbName = $aParams[ 'db' ];
		$sDbUser = $aParams[ 'user' ];
		$sDbPwd = $aParams[ 'pwd' ];
		$sDbHost = ( $aParams[ 'host' ] ) ? $aParams[ 'host' ] : 'localhost';
		
		$rLink = mysql_connect( $sDbHost, $sDbUser, $sDbPwd );
		
		if ( !$rLink ) {
			echo 'Could not connect: ' . mysql_error() . "\n";
			return FALSE;
		}
		
		mysql_select_db( $sDbName );
		
		return $rLink;
	}
	
	// insert one record
	public static function insert( $sTable, $aValues ) {
		
		$sQuery = 'INSERT INTO ' . $sTable . ' ( ';
		
		$aFields = array_keys( $aValues );
		
		$sQuery .= implode( ', ', $aFields );
		$sQuery .= ' ) VALUES ( ';
		
		$bFirst = TRUE;
		foreach ( $aValues as $sValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ', ';	
			$sQuery .= "'" . addslashes( $sValue ) . "'";
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
	
		$sQuery = 'UPDATE ' . $sTable . ' SET ';
		
		$bFirst = TRUE;
		foreach ( $aValues as $sField => $sValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ', ';	
			$sQuery .= ' ' . $sField . " = '" . addslashes( $sValue ) . "'";
		}
		
		$sQuery .= ' WHERE ';
		
		$bFirst = TRUE;
		foreach ( $aKeys as $sField => $sValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ' AND ';	
			$sQuery .= ' ( ' . $sField . " = '" . addslashes( $sValue ) . "' ) ";
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
		
		$sQuery = 'DELETE FROM ' . $sTable . ' WHERE ';
		
		$bFirst = TRUE;
		foreach ( $aKeys as $sFldName => $mValue ) {
			if ( $bFirst ) $bFirst = FALSE;
			else $sQuery .= ' AND ';
			$sQuery .= ' ( ' . $sFldName . " = '" . addslashes( $mValue ) . "' ) ";
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
		$rRes = mysql_query( "SHOW TABLES LIKE '" . $sTableName . "'" );
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
		$sQuery = "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = '" . $sDbName . "' AND table_name = '" . $sTableName . "'";
		$rRes = mysql_query( $sQuery );
		return self::fetchValue( $rRes );
	}
	
	// get the field names in a table
	public static function tableGetFieldNames( $sDbName, $sTableName ) {
		$aFields = array();
		
		$sQuery = "SHOW FIELDS FROM " . $sDbName . "." . $sTableName;
		$rRes = mysql_query( $sQuery );
		
		while( $aRow = mysql_fetch_array( $rRes ) ) {
			$aFields[] = $aRow[ 'Field' ];
		}
		
		return $aFields;
	}
	
	// get the number of rows of a table
	public static function tableNumRows( $sTableName ) {
		$rRes = mysql_query( "SELECT COUNT(*) FROM " . $sTableName );
		$aRes = mysql_fetch_row( $rRes );
		return $aRes[ 0 ];
	}
	
	// check if database exists
	public static function dbExists( $sDbName ) {
		$rRes = mysql_query( "SHOW DATABASES LIKE '" . $sDbName . "'" );
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

	
	
}


