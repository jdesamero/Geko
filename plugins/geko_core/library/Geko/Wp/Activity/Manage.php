<?php

//
class Geko_Wp_Activity_Manage extends Geko_Wp_Log_Manage
{
	
	protected $_sTableSuffix = 'activity';
	protected $_bUseMetaTable = TRUE;
	
	
	
	// implement hook method
	public function modifyPrimaryTable( $oSqlTable ) {
		
		// add a session_id field to the main table
		$oSqlTable
			->fieldVarChar( 'session_id', array( 'size' => 32 ) )
		;
		
		return $oSqlTable;
	}
	
	// implement hook method
	public function modifyMetaTable( $oSqlTable ) {
		
		// add a type_id field to the meta table
		$oSqlTable
			->fieldSmallInt( 'type_id', array( 'unsgnd' ) )
		;
		
		return $oSqlTable;
	}
	
	// implement modify params
	// NOTE!!! Possible collision!!!
	public function modifyParams( $aParams ) {
		
		// capture the current session_id
		$aParams[ 'session_id' ] = $_COOKIE[ 'PHPSESSID' ];
		
		// load meta values from $_GET, $_POST, and $_COOKIE
		$aMeta = array();
		$aMeta = $this->loadRequestData( $aMeta, $_GET, 'get' );
		$aMeta = $this->loadRequestData( $aMeta, $_POST, 'post' );
		$aMeta = $this->loadRequestData( $aMeta, $_COOKIE, 'cookie' );
		
		$aParams[ 'meta' ] = $aMeta;
		
		return parent::modifyParams( $aParams );
		
	}
	
	// implement hook method
	public function getInsertMetaData( $aParams ) {
		
		// separate meta value into type and value
		$aValue = $aParams[ 'meta_value' ];
		$aParams[ 'type_id' ] = $aValue[ 0 ];
		$aParams[ 'meta_value' ] = $aValue[ 1 ];
		
		return parent::getInsertMetaData( $aParams );
	}
	
	
	
	//// helpers
	
	//
	private function loadRequestData( $aMeta, $aData, $sType ) {
		if ( is_array( $aData ) ) {
			
			$aCookieFilter = array( 'PHPSESSID', '__un', '__ut', 'wordpress_', 'wp-settings-', 'wp-user-' );
			$aPassFilter = array( 'pass', 'pwd' );
			
			$iTypeId = Geko_Wp_Options_MetaKey::getId( $sType );
			foreach ( $aData as $sKey => $mValue ) {
				if ( ( 'cookie' != $sType ) || (
					( 'cookie' == $sType ) && 
					( !Geko_Array::beginsWith( $sKey, $aCookieFilter ) )
				) ) {
					$sKeyLcase = strtolower( $sKey );
					if ( Geko_Array::contains( $sKeyLcase, $aPassFilter ) ) {
						// do not capture user passwords
						$mValue = '********';
					} elseif ( !is_scalar( $mValue ) ) {
						$mValue = Zend_Json::encode( $mValue );
					}
					// return meta value with type and value
					$aMeta[ $sKey ] = array( $iTypeId, $mValue );
				}
			}
		}
		return $aMeta;
	}
	
	
	
}


