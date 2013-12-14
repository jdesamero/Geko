<?php

//
class Geko_Wp_EmailMessage_Transport extends Geko_Wp_Entity
{

	protected $_sEntityIdVarName = 'geko_emsg_trpt_id';
	protected $_sEntitySlugVarName = 'geko_emsg_trpt_slug';
	
	protected $_sEditEntityIdVarName = 'trpt_id';
	
	protected static $_aTransports = array();
	
	
	
	//// static methods

	// factory method
	public static function factory( $mTrptIdOrSlug ) {
		
		$oTransport = NULL;
		
		if ( $mTrptIdOrSlug = trim( $mTrptIdOrSlug ) ) {
			
			if ( !$oTransport = self::$_aTransports[ $mTrptIdOrSlug ] ) {
				
				$oTrpt = new Geko_Wp_EmailMessage_Transport( $mTrptIdOrSlug );
				
				if (
					( $oTrpt->isValid() ) && 
					( $iTypeId = $oTrpt->getTypeId() )
				) {
					
					$aTrptTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-trpt-type' );
					
					$sType = $aTrptTypes->getSlugFromValue( $iTypeId );
					
					if ( 'geko-emsg-trpt-type-sendmail' == $sType ) {
						
						$sParams = $oTrpt->getParams();
						
						if ( $sParams ) {
							$oTransport = new Zend_Mail_Transport_Sendmail( $sParams );
						}
						
					} elseif ( 'geko-emsg-trpt-type-smtp' == $sType ) {
						
						$sServer = $oTrpt->getServer();
						
						if ( $sServer ) {
							
							$aParams = array();
							
							if ( $sSsl = $oTrpt->getSecurity() ) {
								if ( 'TRUE' == trim( strtoupper( $sSsl ) ) ) $sSsl = TRUE;
								$aParams[ 'ssl' ] = $sSsl;
							}
							
							if ( $iPort = $oTrpt->getPort() ) $aParams[ 'port' ] = intval( $iPort );
							if ( $sAuth = $oTrpt->getAuth() ) $aParams[ 'auth' ] = $sAuth;
							if ( $sUsername = $oTrpt->getUsername() ) $aParams[ 'username' ] = $sUsername;
							if ( $sPassword = $oTrpt->getPassword() ) $aParams[ 'password' ] = $sPassword;
							
							$oTransport = new Zend_Mail_Transport_Smtp( $sServer, $aParams );
						}
					
					} elseif ( 'geko-emsg-trpt-type-file' == $sType ) {
						
						// TO DO:
						// $oTransport = new Zend_Mail_Transport_File();
						
					}
					
					if ( $oTransport ) {
						
						// HACKISH!!!
						$oTransport->geko_trpt = $oTrpt;
						
						self::$_aTransports[ $oTrpt->getId() ] = $oTransport;
						self::$_aTransports[ $oTrpt->getSlug() ] = $oTransport;
					}
				}
			
			}
			
		}
		
		return $oTransport;
	}
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'trpt_id' )
			->setEntityMapping( 'title', 'label' )
			->setEntityMapping( 'content', 'notes' )
		;
		
		return $this;
	}
	
	
	
	
}



