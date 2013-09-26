<?php

//
class Geko_Wp_EmailMessage_Delivery
{	
	
	protected $oEmsg;
	protected $aRecipients = array();
	protected $aCc = array();
	protected $aMergeParams = array();
	protected $aAttachments = array();
	
	
	//
	public function __construct( $mEntity = NULL, $aRecipients = array(), $aMergeParams = array() ) {
		
		$this
			->setEntity( $mEntity )
			->addRecipients( $aRecipients )
			->setMergeParams( $aMergeParams )
			->setMode( 'normal' )
			->setMergeParams( Geko_Wp::getStandardPlaceholders() )
		;		
	}
	
	//
	public function setEntity( $mEntity ) {
		
		if ( is_object( $mEntity ) ) {
			$this->oEmsg = $mEntity;
		} elseif ( is_string( $mEntity ) ) {
			$oEmsg = new Geko_Wp_EmailMessage( $mEntity );
			if ( $oEmsg->isValid() ) {
				$this->oEmsg = $oEmsg;
			}
		}
		
		return $this;
	}
	
	//
	public function isValid() {
		return ( $this->oEmsg ) ? TRUE : FALSE;
	}
	
	
	
	//// recipients
	
	//
	public function addRecipient( $sEmail, $sName = '' ) {
		
		if ( $sName ) {
			$this->aRecipients[] = array( $sEmail, $sName );
		} else {
			$this->aRecipients[] = $sEmail;
		}
		
		return $this;
	}
	
	//
	public function addRecipients( $aRecipients = NULL ) {
		
		if ( is_array( $aRecipients ) ) {
			$this->aRecipients = array_merge(
				$this->aRecipients,
				$aRecipients
			);
		} elseif ( $this->oEmsg ) {
			$aParams = array(
				'emsg_id' => $this->oEmsg->getId(),
				'is_active' => 1
			);
			$aRecipients = new Geko_Wp_EmailMessage_Recipient_Query( $aParams );
			foreach ( $aRecipients as $oRecipient ) {
				if ( $sEmail = $oRecipient->getEmail() ) {
					if ( $sName = $oRecipient->getName() ) {
						$this->aRecipients[] = array( $sEmail, $sName );
					} else {
						$this->aRecipients[] = $sEmail;
					}
				}
			}
		}
		
		return $this;
	}
	
	//
	public function hasRecipients() {
		return ( count( $this->aRecipients ) > 0 );
	}
	
	//
	public function addCc( $sEmail, $sName = '' ) {
		
		if ( $sName ) {
			$this->aCc[] = array( $sEmail, $sName );
		} else {
			$this->aCc[] = $sEmail;
		}
		
		return $this;
	}
	
	//
	public function addCcs( $aCcs ) {
		
		if ( is_array( $aCcs ) ) {
			$this->aCc = array_merge( $this->aCc, $aCcs );
		}
		
		return $this;
	}
	
	
	
	
	
	//// sender
	
	//
	public function setSender( $sEmail, $sName = '' ) {
		
		$this->aMergeParams[ '__sender_email' ] = $sEmail;
		if ( $sName ) $this->aMergeParams[ '__sender_name' ] = $sName;
		
		return $this;
	}
	
	//
	public function getSenderEmail( $oTransport = NULL ) {
		
		return Geko_String::coalesce(
			$this->aMergeParams[ '__sender_email' ],
			$this->oEmsg->getTheFromEmail()
		);
	}
	
	//
	public function getSenderName() {
		return Geko_String::coalesce(
			$this->aMergeParams[ '__sender_name' ],
			$this->oEmsg->getTheFromName()
		);
	}
	
	
	//// attachments
	
	// $aAttachment = array(
	// 	'contents' => <result of file_get_contents() >,
	// 	'path' => <path to file, passed to file_get_contents() >,
	// 	'type' => <mime type>,
	// 	'disposition' => Zend_Mime::DISPOSITION_INLINE,
	// 	'encoding' => Zend_Mime::ENCODING_BASE64,
	// 	'name' => <name of file>
	// );
	
	public function addAttachment( $aAttachment ) {
		$this->aAttachments[] = $aAttachment;
		return $this;
	}
	
	
	//// merge params
	
	//
	public function setMode( $sMode ) {
		$this->setMergeParam( '__mode', $sMode );
		return $this;
	}
	
	//
	public function setMergeParam( $sKey, $mValue ) {

		$this->aMergeParams[ $sKey ] = $mValue;
		return $this;
	}
	
	//
	public function setMergeParams( $aMergeParams ) {
		$this->aMergeParams = array_merge(
			$this->aMergeParams,
			$aMergeParams
		);
		
		return $this;
	}
	
	//
	public function mergeParams( $sContent ) {
		return Geko_String::replacePlaceholders(
			$this->aMergeParams, $sContent
		);
	}
	
	//
	public function getSubjectMerged() {
	
		if ( $this->oEmsg ) {
			return $this->mergeParams( $this->oEmsg->getSubject() );
		}
		
		return '';
	}
	
	//
	public function getBodyTextMerged() {
		
		if ( $this->oEmsg ) {
			return $this->mergeParams( $this->oEmsg->getBodyText() );
		}
		
		return '';
	}
	
	//
	public function getTheBodyHtmlMerged() {
		
		if ( $this->oEmsg ) {
			return $this->mergeParams( $this->oEmsg->getTheBodyHtml() );
		}
		
		return '';
	}
		
	
	
	// TO DO: add return values for failure
	public function send() {
		
		if ( $this->oEmsg ) {
			
			// call the addRecipients() method to include emails specified in the admin interface
			$this->addRecipients();
			
			if ( $this->hasRecipients() ) {
				
				// prep for sending
				$oEmsg = $this->oEmsg;
				
				// check for a custom transport
				$oTransport = $oEmsg->getTransport();
				
				// check for custom headers
				$aHeaders = new Geko_Wp_EmailMessage_Header_Query( array(
					'emsg_id' => $oEmsg->getId(),
					'showposts' => -1,
					'posts_per_page' => -1
				), FALSE );
				
				// send a separate email for each recipient
				foreach ( $this->aRecipients as $mRecipient ) {
					
					if ( is_array( $mRecipient ) ) {
						$sRecipientEmail = $mRecipient[ 0 ];
						$sRecipientName = $mRecipient[ 1 ];
					} else {
						$sRecipientEmail = $mRecipient;
						$sRecipientName = $mRecipient;
					}
					
					if ( is_string( $sRecipientEmail ) && is_email( $sRecipientEmail ) ) {
						
						$this
							->setMergeParam( '__recipient_email', $sRecipientEmail )
							->setMergeParam( '__recipient_name', $sRecipientName )
						;

						$oMail = new Zend_Mail();
						
						$oMail->setFrom( $this->getSenderEmail(), $this->getSenderName() );
						
						// to
						if ( $sRecipientEmail != $sRecipientName ) {
							$oMail->addTo( $sRecipientEmail, $sRecipientName );
						} else {
							$oMail->addTo( $sRecipientEmail );
						}
						
						// cc
						foreach ( $this->aCc as $mCcItem ) {
							if ( is_array( $mCcItem ) ) {
								if ( is_email( $mCcItem[ 0 ] ) ) {
									$oMail->addCc( $mCcItem[ 0 ], $mCcItem[ 1 ] );							
								} elseif ( is_string( $mCcItem ) && is_email( $mCcItem ) ) {
									$oMail->addCc( $mCcItem );							
								}
							}
						}
						
						// default headers
						$oMail
							->addHeader( 'X-WpBloginfoUrl', get_bloginfo( 'url' ) )
							->addHeader( 'X-WpBloginfoUrlHash', md5( get_bloginfo( 'url' ) ) )
							->addHeader( 'X-GekoEmsgId', $oEmsg->getId() )
							->addHeader( 'X-GekoEmsgRecipient', $sRecipientEmail )
						;
						
						// additional custom headers
						if ( $aHeaders->count() > 0 ) {
							foreach ( $aHeaders as $oHeader ) {
								$oMail->addHeader(
									$this->mergeParams( $oHeader->getKey() ),
									$this->mergeParams( $oHeader->getContent() ),
									intval( $oHeader->getMulti() ) ? TRUE : FALSE
								);
							}
						}
						
						// subject/body
						
						$oMail->setSubject( $this->getSubjectMerged() );
						
						$aTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-type' );
						
						if ( $aTypes->valueMatchesSlug(
							$oEmsg->getTypeId(),
							array( 'geko-emsg-type-text', 'geko-emsg-type-both' )
						) ) {
							$oMail->setBodyText( $this->getBodyTextMerged() );
						}
						
						if ( $aTypes->valueMatchesSlug(
							$oEmsg->getTypeId(),
							array( 'geko-emsg-type-html', 'geko-emsg-type-both' )
						) ) {
							$oMail->setBodyHtml( $this->getTheBodyHtmlMerged() );			
						}
						
						// handle attachments
						foreach ( $this->aAttachments as $aAttachment ) {
							
							if ( $sPath = $aAttachment[ 'path' ] ) {
								$sContents = file_get_contents( $sPath );
							} else {
								$sContents = $aAttachment[ 'contents' ];							
							}
							
							if ( $sContents ) {
							
								$oPart = new Zend_Mime_Part( $sContents );
								
								$oPart->type = ( $aAttachment[ 'type' ] ) ?
									$aAttachment[ 'type' ] : 'application/octet-stream';
								
								$oPart->disposition = ( $aAttachment[ 'disposition' ] ) ?
									$aAttachment[ 'disposition' ] : Zend_Mime::DISPOSITION_INLINE;
								
								$oPart->encoding = ( $aAttachment[ 'encoding' ] ) ?
									$aAttachment[ 'encoding' ] : Zend_Mime::ENCODING_BASE64;
								
								if ( $aAttachment[ 'name' ] ) $oPart->filename = $aAttachment[ 'name' ];
								
								$oMail->addAttachment( $oPart );
							}
							
						}
						
						// send using custom transport, if applicable
						if ( $oTransport ) {
							$oMail->send( $oTransport );
						} else {
							$oMail->send();						
						}
						
						do_action( 'geko_wp_emsg_delivery_send', $oEmsg, $this->aMergeParams );
					
					} else {
	
						do_action( 'geko_wp_emsg_delivery_fail', $oEmsg, $this->aMergeParams );
						
					}
					
				}
			}
			
		}
		
	}
	
}


