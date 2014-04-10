<?php

//
class Geko_Wp_Log_Query extends Geko_Wp_Entity_Query
{
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		global $wpdb;
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oMng = Geko_Singleton_Abstract::getInstance( $this->_sManageClass );
		
		$sTableName = $oMng->getPrefixedTableName();
		$sMetaTableName = $oMng->getPrefixedMetaTableName();
		
		$oQuery
			
			->field( 'INET_NTOA( l.remote_ip )', 'remote_ip_address' )
			->field( 'l.*' )
			->field( 'u.user_login' )
			
			->from( $sTableName, 'l' )			
			->joinLeft( $wpdb->users, 'u' )
				->on( 'u.ID = l.user_id' )
			
		;
		
		
		//// meta
		if ( $aMeta = $aParams[ 'meta' ] ) {
			
			$i = 0;
			foreach ( $aMeta as $sKey => $aParams ) {
				
				$sPfx = '_mt' . $i;
				
				$oQuery
					->field( sprintf( '%s.meta_value', $sPfx ), $sKey )
					->joinLeft( $sMetaTableName, $sPfx )
						->on( sprintf( '%s.log_id = l.log_id', $sPfx ) )
						->on( sprintf( '%s.mkey_id = ?', $sPfx ), Geko_Wp_Options_MetaKey::getId( $sKey ) )
				;
				
				$sType = $aParams[ 'type' ];
				$mVal = $aParams[ 'val' ];
				
				if ( $sType && $mVal ) {
					
					$sClause = sprintf( '%s.meta_value = ?', $sPfx );
					
					if ( 'int' == strtolower( $sType ) ) {
						$mVal = intval( $mVal );
						$sClause = sprintf( 'CAST( %s.meta_value AS UNSIGNED ) = ?', $sPfx );
					}
					
					$oQuery->having( $sClause, $mVal );
				}
				
				$i++;
			}
		}
		
		
		//// filters
		
		//
		if ( $mRemoteIp = $aParams[ 'remote_ip' ] ) {
			
			if ( filter_var( $mRemoteIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
				$iRemoteIp = ip2long( $mRemoteIp );
			} else {
				$iRemoteIp = intval( $mRemoteIp );
			}
			
			$oQuery->where( 'l.remote_ip = ?', $iRemoteIp );
		}
		
		//
		if ( $aParams[ 'min_date' ] ) {
			$oQuery->where( 'l.date_created >= ?', $aParams[ 'min_date' ] );
		}		

		if ( $aParams[ 'max_date' ] ) {
			$oQuery->where( 'l.date_created <= ?', $aParams[ 'max_date' ] );
		}
		
		
		// apply default sorting
		if ( !isset( $aParams[ 'orderby' ] ) ) {		
			$oQuery->order( 'l.date_created', 'DESC' );
		}
		
		
		if ( $iLogId = $aParams[ 'log_id' ] ) {
			$oQuery->where( 'l.log_id = ?', $iLogId );
		}
		
		return $oQuery;
	}
	
	
}


