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
					->field( $sPfx . '.meta_value', $sKey )
					->joinLeft( $sMetaTableName, $sPfx )
						->on( $sPfx . '.log_id = l.log_id' )
						->on( $sPfx . '.mkey_id = ?', Geko_Wp_Options_MetaKey::getId( $sKey ) )
				;
				
				$sType = $aParams[ 'type' ];
				$mVal = $aParams[ 'val' ];
				
				if ( $sType && $mVal ) {
					
					$sClause = $sPfx . '.meta_value = ?';
					
					if ( 'int' == strtolower( $sType ) ) {
						$mVal = intval( $mVal );
						$sClause = 'CAST( ' . $sPfx . '.meta_value AS UNSIGNED ) = ?';
					}
					
					$oQuery->having( $sClause, $mVal );
				}
				
				$i++;
			}
		}
		
		
		//// filters
		
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
		
		return $oQuery;
	}
	
	
}


