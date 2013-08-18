<?php

//
class Geko_Wp_EmailMessage_Storage extends Geko_Wp_Entity
{

	protected $_sEntityIdVarName = 'geko_emsg_strg_id';
	protected $_sEntitySlugVarName = 'geko_emsg_strg_slug';
	
	protected $_sEditEntityIdVarName = 'strg_id';
	
	protected static $_aStorage = array();
	
	
	
	//// static methods

	// factory method
	public static function factory( $mArg ) {
		
		$oStrg = NULL;
		$oStorage = NULL;
		
		if ( is_object( $mArg ) ) {
			$oStrg = $mArg;
			$mStrgIdOrSlug = $oStrg->getId();
		} else {
			$mStrgIdOrSlug = $mArg;
		}
				
		if ( $mStrgIdOrSlug = trim( $mStrgIdOrSlug ) ) {
			
			if ( !$oStorage = self::$_aStorage[ $mStrgIdOrSlug ] ) {
				
				if ( NULL == $oStrg ) {
					$oStrg = new Geko_Wp_EmailMessage_Storage( $mStrgIdOrSlug );
				}
				
				if (
					( $oStrg->isValid() ) && 
					( $iTypeId = $oStrg->getTypeId() )
				) {
					
					$aStrgTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-strg-type' );
					
					$sType = $aStrgTypes->getSlugFromValue( $iTypeId );
					
					// prepare parameters
					
					$aParams = array();
					
					if ( $sSsl = $oStrg->getSecurity() ) {
						if ( 'TRUE' == trim( strtoupper( $sSsl ) ) ) $sSsl = TRUE;
						$aParams[ 'ssl' ] = $sSsl;
					}
					
					if ( $sServer = $oStrg->getServer() ) $aParams[ 'host' ] = $sServer;
					if ( $iPort = $oStrg->getPort() ) $aParams[ 'port' ] = intval( $iPort );
					if ( $sUsername = $oStrg->getUsername() ) $aParams[ 'user' ] = $sUsername;
					if ( $sPassword = $oStrg->getPassword() ) $aParams[ 'password' ] = $sPassword;
					if ( $sFilename = $oStrg->getFilename() ) $aParams[ 'filename' ] = $sFilename;
					if ( $sDirname = $oStrg->getDirname() ) $aParams[ 'dirname' ] = $sDirname;
					if ( $sFolder = $oStrg->getFolder() ) $aParams[ 'folder' ] = $sFolder;
					if ( $sDelim = $oStrg->getDelim() ) $aParams[ 'delim' ] = $sDelim;
					
					
					// create storage instance
					if ( 'geko-emsg-strg-type-mbox' == $sType ) {
						$oStorage = new Zend_Mail_Storage_Mbox(
							Geko_Array::sanitize( $aParams, array( 'filename', 'dirname', 'folder' ) )
						);
					} elseif ( 'geko-emsg-strg-type-maildir' == $sType ) {
						$oStorage = new Zend_Mail_Storage_Maildir(
							Geko_Array::sanitize( $aParams, array( 'dirname', 'folder', 'delim' ) )
						);
					} elseif ( 'geko-emsg-strg-type-pop3' == $sType ) {
						$oStorage = new Zend_Mail_Storage_Pop3(
							Geko_Array::sanitize( $aParams, array( 'host', 'ssl', 'port', 'user', 'password' ) )
						);
					} elseif ( 'geko-emsg-strg-type-imap' == $sType ) {
						$oStorage = new Zend_Mail_Storage_Imap(
							Geko_Array::sanitize( $aParams, array( 'host', 'ssl', 'port', 'user', 'password', 'folder' ) )
						);
					}
					
					if ( $oStorage ) {
						self::$_aStorage[ $oStrg->getId() ] = $oStorage;
						self::$_aStorage[ $oStrg->getSlug() ] = $oStorage;
					}
				}
			
			}
			
		}
		
		return $oStorage;
	}
	
	
	// look at the message body and parse "X-SomeHeader: <some value>"
	// if found, add value to $aCustom with key "some_header"
	public static function parseHeaderValue( $aCustom, $sContent, $sPattern, $iRegsIdx = 1, $sKey = '' ) {
		
		$aRegs = array();
		if ( preg_match( $sPattern, $sContent, $aRegs ) ) {
			
			if ( !$sKey ) {
				$aRegs2 = array();
				if ( preg_match( '/X-([a-zA-Z0-9]+)/', $sPattern, $aRegs2 ) ) {
					$sKey = Geko_Inflector::underscore( $aRegs2[ 1 ] );
				}
			}
			
			if ( $sKey ) $aCustom[ $sKey ] = trim( $aRegs[ $iRegsIdx ] );
		}
		
		return $aCustom;
	}
	
	//
	public static function _saveMessagesToDb( $aParams ) {
		
		$mStrgId = $aParams[ 'strg_id' ];
		$bRemoveMessage = isset( $aParams[ 'remove_message' ] ) ? $aParams[ 'remove_message' ] : TRUE ;
		$bMatchBloginfoUrlHash = isset( $aParams[ 'match_bloginfo_url_hash' ] ) ? $aParams[ 'match_bloginfo_url_hash' ] : TRUE ;

		$iDeleted = 0;
		$iCount = 0;
		
		$aStrg = new Geko_Wp_EmailMessage_Storage_Query( array(
			'showposts' => -1,
			'posts_per_page' => -1,
			'geko_emsg_strg_id' => $mStrgId
		), FALSE );

		foreach ( $aStrg as $oSt ) {
			
			$aRes = $oSt->saveMessagesToDb( $bRemoveMessage, $bMatchBloginfoUrlHash );
			
			$iDeleted += intval( $aRes[ 'deleted' ] );
			$iCount += intval( $aRes[ 'count' ] );
		}
		
		return array(
			'deleted' => $iDeleted,
			'count' => $iCount
		);
	}
	
	
	
	//// object oriented functions
		
	//
	public function init() {
		
		parent::init();
		
		$this
			->setEntityMapping( 'id', 'strg_id' )
			->setEntityMapping( 'title', 'label' )
			->setEntityMapping( 'content', 'notes' )
		;
		
		return $this;
	}
	
	
	//
	public function saveMessagesToDb( $bRemoveMessage = TRUE, $bMatchBloginfoUrlHash = TRUE ) {
		
		$oMng = Geko_Wp_EmailMessage_Storage_Log_Manage::getInstance();
		
		$oStorage = self::factory( $this );
		$iStrgId = $this->getId();
		
		// foreach ( $oStorage as $i => $oMsg ) ... has problems
		// http://framework.zend.com/issues/browse/ZF-5655
		// better to read messages in reverse, then delete
		
		$iMsgCount = $oStorage->countMessages();
		
		$aRes = array(
			'count' => $iMsgCount,
			'inserted' => 0,
			'deleted' => 0,
			'remove_message' => $bRemoveMessage
		);
		
		//
		for ( $i = $iMsgCount; $i; --$i ) {
			
			$oMsg = $oStorage->getMessage( $i );
			$iUniqueId = $oStorage->getUniqueId( $i );
			
			$oMsg = new Geko_Wp_EmailMessage_Storage_Message( $oMsg );
			$sContent = $oMsg->getContent();
			
			$aMeta = array();
			
			//// headers
			
			$aHdrFmt = array();
			$aHeaders = $oMsg->getHeaders();

			foreach ( $aHeaders as $sName => $mValue ) {
				$aHdrFmt[ $sName ] = $oMsg->getHeaderSafe( $sName );
			}
			
			$aMeta[ 'header' ] = $aHdrFmt;
			
			//// custom
			
			$aCustom = array(
				'is_multipart' => $oMsg->isMultipart(),
				'is_bounce' => $oMsg->isBounce(),
				'delivery_status_code' => $oMsg->getDeliveryStatusCode()
			);
			
			//// parse other header values
			
			$aCustom = self::parseHeaderValue( $aCustom, $sContent, '/X-WpBloginfoUrl: (.+)/' );
			$aCustom = self::parseHeaderValue( $aCustom, $sContent, '/X-WpBloginfoUrlHash: (.+)/' );
			$aCustom = self::parseHeaderValue( $aCustom, $sContent, '/X-GekoEmsgId: ([0-9]+)/' );
			$aCustom = self::parseHeaderValue( $aCustom, $sContent, '/X-GekoEmsgRecipient: (.+)/' );
			
			// match parsed value of "X-WpBloginfoUrlHash" to the actual value of this deployment
			// allows the use of the same bounce handler account for multiple sites
			if (
				( $bMatchBloginfoUrlHash ) && 
				( md5( get_bloginfo( 'url' ) ) != $aCustom[ 'wp_bloginfo_url_hash' ] )
			) {
				// no match, so don't insert
				continue;
			}
			
			$aCustom = apply_filters( __METHOD__ . '::custom', $aCustom, $oMsg, $this );
			
			$aMeta[ 'custom' ] = $aCustom;
			
			//// main
			
			$aParams = array(
				'message_body' => $sContent,
				'strg_id' => $iStrgId,
				'unique_id' => $iUniqueId,
				'meta' => $aMeta
			);
			
			if ( $oMng->insert( $aParams ) ) {
				
				$aRes[ 'inserted' ]++;
				
				if ( $bRemoveMessage ) {
					// remove now that we have stored the message to the db
					try {
						$oStorage->removeMessage( $i );
						$aRes[ 'deleted' ]++;
					} catch ( Exception $e ) {
						// remove failed, do something ???
					}
				}
				
			} else {
				// insert failed, do something ???
			}
			
		}
		
		return $aRes;
	}
	
	
	
}



