<?php

//
class Geko_Fb_Persist extends Geko_Singleton_Abstract
{
	protected $_oDb;
	protected $_sPersistKey;
	protected $_sNamespaceKey;
	protected $_iPersistId;
		
	protected $_aVars = array();
	protected $_bChanged = FALSE;
	
	// must be defined by subclass
	protected $_sPersistTable = '';					
	protected $_sPersistVarsTable = '';
	protected $_sExpireInterval = 'INTERVAL 2 DAY';
	
	
	
	//
	public function init( $oDb, $sPersistKey, $sNamespaceKey )
	{
		$this
			->setDb( $oDb )
			->setPersistKey( $sPersistKey )
			->setNamespaceKey( $sNamespaceKey )
			->initDb()
		;
	}
	
	//
	public function setDb( $oDb )
	{
		$this->_oDb = $oDb;
		return $this;
	}
	
	//
	public function setPersistKey( $sPersistKey )
	{
		$this->_sPersistKey = $sPersistKey;
		return $this;
	}

	//
	public function setNamespaceKey( $sNamespaceKey )
	{
		$this->_sNamespaceKey = $sNamespaceKey;
		return $this;
	}
	
	//
	public function initDb()
	{
		if ( $this->_sPersistKey && $this->_sNamespaceKey )
		{
			if ( !$this->_oDb->fetchOne("SHOW TABLES LIKE '{$this->_sPersistTable}'") )
			{
				$this->_oDb->getConnection()->exec("
					CREATE TABLE {$this->_sPersistTable} (
						ps_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						ps_key VARCHAR(128),
						ns_key VARCHAR(128),
						date_created DATETIME,
						date_modified DATETIME,
						PRIMARY KEY(ps_id),
						UNIQUE KEY ps_ns(ps_key, ns_key)			
					)
				");
			}
			
			if ( !$this->_oDb->fetchOne("SHOW TABLES LIKE '{$this->_sPersistVarsTable}'") )
			{
				$this->_oDb->getConnection()->exec("
					CREATE TABLE {$this->_sPersistVarsTable} (
						ps_id BIGINT UNSIGNED,
						var_name VARCHAR(255),
						var_value LONGTEXT,
						UNIQUE KEY ps_var(ps_id, var_name)
					)
				");
			}
			
			if ( $this->_iPersistId = $this->_oDb->fetchOne(
				"SELECT ps_id FROM {$this->_sPersistTable} WHERE ps_key = '{$this->_sPersistKey}' AND ns_key = '{$this->_sNamespaceKey}'"
			) ) {
				
				$this->_aVars = $this->_oDb->fetchPairs(
					"SELECT var_name, var_value FROM {$this->_sPersistVarsTable} WHERE ps_id = {$this->_iPersistId}"
				);
								
			} else {
				
				$sDate = Geko_Db_Mysql::getTimestamp();
				$this->_oDb->insert(
					$this->_sPersistTable,
					array(
						'ps_key' => $this->_sPersistKey,
						'ns_key' => $this->_sNamespaceKey,
						'date_created' => $sDate,
						'date_modified' => $sDate
					)
				);
				
				$this->_iPersistId = $this->_oDb->fetchOne( 'SELECT LAST_INSERT_ID()' );
				
			}
		}
		
		return $this;	
	}
	
	//
	public function getVar( $sKey )
	{
		if ( $this->hasVar( $sKey ) ) return $this->_aVars[ $sKey ];
		return NULL;
	}
	
	//
	public function setVar( $sKey, $sValue )
	{
		$this->_bChanged = TRUE;
		$this->_aVars[ $sKey ] = $sValue;
		return $this;
	}

	//
	public function hasVar( $sKey )
	{
		return isset( $this->_aVars[ $sKey ] );
	}

	//
	public function unsetVar( $sKey )
	{
		$this->_bChanged = TRUE;
		if ( $sKey ) {
			unset( $this->_aVars[ $sKey ] );
		} else {
			$this->_aVars = array();		
		}
		return $this;
	}
	
	//
	public function __destruct()
	{
		$sDate = Geko_Db_Mysql::getTimestamp();
		
		if ( $this->_bChanged && $this->_iPersistId ) {
			
			$this->_oDb->delete(
				$this->_sPersistVarsTable,
				"ps_id = {$this->_iPersistId}"
			);
			
			foreach ( $this->_aVars as $sKey => $sValue ) {
				$this->_oDb->insert(
					$this->_sPersistVarsTable,
					array(
						'ps_id' => $this->_iPersistId,
						'var_name' => $sKey,
						'var_value' => $sValue
					)
				);
			}
						
			$this->_oDb->update(
				$this->_sPersistTable,
				array( 'date_modified' => $sDate ),
				"ps_id = {$this->_iPersistId}"
			);
			
		}
		
		// delete expired
		$this->_oDb->delete(
			$this->_sPersistTable,
			"DATE_ADD( date_modified, {$this->_sExpireInterval} ) < '$sDate'"
		);
		
		$this->_oDb->delete(
			$this->_sPersistVarsTable,
			"ps_id NOT IN ( SELECT ps_id FROM {$this->_sPersistTable} )"
		);
		
	}
	
}


