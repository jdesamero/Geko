<?php

//
class Geko_Db_Sqlite
{
	
	//
	public static function getTimestamp( $iTimestamp = NULL ) {
		if ( NULL == $iTimestamp ) $iTimestamp = time();
		return @date( 'Y-m-d H:i:s', $iTimestamp );
	}
	
	
}


