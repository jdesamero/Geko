<?php

// decorator class for Zend_Mail_Message
class Geko_Wp_EmailMessage_Storage_Message
{
	
	const HEADER_DELIM = ' ## ';
	
	const BOUNCE_REGEX_SUBJECT = "/(mail delivery failed|failure notice|warning: message|delivery status notif|delivery failure|delivery problem|spam eater|returned mail|undeliverable|returned mail|delivery errors|mail status report|mail system error|failure delivery|delivery notification|delivery has failed|undelivered mail|returned email|returning message to sender|returned to sender|message delayed|mdaemon notification|mailserver notification|mail delivery system|nondeliverable mail|mail transaction failed)|auto.{0,20}reply|vacation|(out|away|on holiday).*office/i";
	const BOUNCE_REGEX_AUTOREPLY = '/auto_reply/';
	const BOUNCE_REGEX_FROM = "/^(postmaster|mailer-daemon)\@?/i";
	
	
	
	protected $_oZendMail = NULL;
	protected $_aHeaderNames = array();
	
	protected static $_aBouncePatterns = array(
		'4.2.0' => array(
			'not yet been delivered',
			'Delivery attempts will continue to be made for'
		),
		'4.2.2' => array(
			'mailbox is full',
			'Mailbox quota usage exceeded',
			'User mailbox exceeds allowed size'
		),
		'4.4.1' => array(
			'Status: 4.4.1',
			'delivery temporarily suspended'
		),
		'4.4.7' => array(
			'retry timeout exceeded'
		),
		'5.5.0' => array(
			'550 OU-002',
			'Mail rejected by Windows Live Hotmail for policy reasons'
		),
		'5.1.1' => array(
			'no such address',
			'Recipient address rejected',
			'User unknown in virtual alias table',
			'550-5.1.1',
			'550 5.1.1',
			'This Gmail user does not exist.',
			'Status: 5.1.1'
		),
		'5.1.2' => array(
			'unrouteable mail domain',
			'Esta casilla ha expirado por falta de uso',
			'DNS Error: Domain name not found'
		),
		'5.2.0' => array(
			'mailbox unavailable',
			'The account or domain may not exist, they may be blacklisted, or missing the proper dns entries.'
		),
		'5.4.4' => array(
			'Unrouteable address',
			'554 TRANSACTION FAILED',
			'554 delivery error:'
		)
	);
	
	//
	protected static $_aCodeClasses = array(
		'2' => array(
			'title' => 'Success',
			'descr' => 'Success specifies that the DSN is reporting a positive delivery action.  Detail sub-codes may provide notification of transformations required for delivery.'
		),
		'4' => array(
			'title' => 'Persistent Transient Failure',
			'descr' => 'A persistent transient failure is one in which the message as sent is valid, but some temporary event prevents the successful sending of the message.  Sending in the future may be successful.'
		),
		'5' => array(
			'title' => 'Permanent Failure',
			'descr' => 'A permanent failure is one which is not likely to be resolved by resending the message in the current form.  Some change to the message or the destination must be made for successful delivery.'
		),
	);
	
	//
	protected static $_aCodeSubclasses = array(
		'0.0' => array(
			'title' => 'Other undefined Status',
			'descr' => 'Other undefined status is the only undefined error code. It should be used for all errors for which only the class of the error is known.'
		),
		'1.0' => array(
			'title' => 'Other address status',
			'descr' => 'Something about the address specified in the message caused this DSN.'
		),
		'1.1' => array(
			'title' => 'Bad destination mailbox address',
			'descr' => 'The mailbox specified in the address does not exist.  For Internet mail names, this means the address portion to the left of the @ sign is invalid.  This code is only useful for permanent failures.'
		),
		'1.2' => array(
			'title' => 'Bad destination system address',
			'descr' => 'The destination system specified in the address does not exist or is incapable of accepting mail.  For Internet mail names, this means the address portion to the right of the @ is invalid for mail.  This codes is only useful for permanent failures.'
		),
		'1.3' => array(
			'title' => 'Bad destination mailbox address syntax',
			'descr' => 'The destination address was syntactically invalid.  This can apply to any field in the address.  This code is only useful for permanent failures.'
		),
		'1.4' => array(
			'title' => 'Destination mailbox address ambiguous',
			'descr' => 'The mailbox address as specified matches one or more recipients on the destination system.  This may result if a heuristic address mapping algorithm is used to map the specified address to a local mailbox name.'
		),
		'1.5' => array(
			'title' => 'Destination address valid',
			'descr' => 'This mailbox address as specified was valid.  This status code should be used for positive delivery reports.'
		),
		'1.6' => array(
			'title' => 'Destination mailbox has moved, No forwarding address',
			'descr' => 'The mailbox address provided was at one time valid, but mail is no longer being accepted for that address.  This code is only useful for permanent failures.'
		),
		'1.7' => array(
			'title' => "Bad sender's mailbox address syntax",
			'descr' => "The sender's address was syntactically invalid.  This can apply to any field in the address."
		),
		'1.8' => array(
			'title' => "Bad sender's system address",
			'descr' => "The sender's system specified in the address does not exist or is incapable of accepting return mail.  For domain names, this means the address portion to the right of the @ is invalid for mail."
		),
		'2.0' => array(
			'title' => 'Other or undefined mailbox status',
			'descr' => 'The mailbox exists, but something about the destination mailbox has caused the sending of this DSN.'
		),
		'2.1' => array(
			'title' => 'Mailbox disabled, not accepting messages',
			'descr' => 'The mailbox exists, but is not accepting messages.  This may be a permanent error if the mailbox will never be re-enabled or a transient error if the mailbox is only temporarily disabled.'
		),
		'2.2' => array(
			'title' => 'Mailbox full',
			'descr' => 'The mailbox is full because the user has exceeded a per-mailbox administrative quota or physical capacity.  The general semantics implies that the recipient can delete messages to make more space available.  This code should be used as a persistent transient failure.'
		),
		'2.3' => array(
			'title' => 'Message length exceeds administrative limit',
			'descr' => 'A per-mailbox administrative message length limit has been exceeded.  This status code should be used when the per-mailbox message length limit is less than the general system limit.  This code should be used as a permanent failure.'
		),
		'2.4' => array(
			'title' => 'Mailing list expansion problem',
			'descr' => 'The mailbox is a mailing list address and the mailing list was unable to be expanded.  This code may represent a permanent failure or a persistent transient failure.'
		),
		'3.0' => array(
			'title' => 'Other or undefined mail system status',
			'descr' => 'The destination system exists and normally accepts mail, but something about the system has caused the generation of this DSN.'
		),
		'3.1' => array(
			'title' => 'Mail system full',
			'descr' => 'Mail system storage has been exceeded.  The general semantics imply that the individual recipient may not be able to delete material to make room for additional messages.  This is useful only as a persistent transient error.'
		),
		'3.2' => array(
			'title' => 'System not accepting network messages',
			'descr' => 'The host on which the mailbox is resident is not accepting messages.  Examples of such conditions include an immanent shutdown, excessive load, or system maintenance.  This is useful for both permanent and permanent transient errors.'
		),
		'3.3' => array(
			'title' => 'System not capable of selected features',
			'descr' => 'Selected features specified for the message are not supported by the destination system.  This can occur in gateways when features from one domain cannot be mapped onto the supported feature in another.'
		),
		'3.4' => array(
			'title' => 'Message too big for system',
			'descr' => 'The message is larger than per-message size limit.  This limit may either be for physical or administrative reasons. This is useful only as a permanent error.'
		),
		'3.5' => array(
			'title' => 'System incorrectly configured',
			'descr' => 'The system is not configured in a manner which will permit it to accept this message.'
		),
		'4.0' => array(
			'title' => 'Other or undefined network or routing status',
			'descr' => 'Something went wrong with the networking, but it is not clear what the problem is, or the problem cannot be well expressed with any of the other provided detail codes.'
		),
		'4.1' => array(
			'title' => 'No answer from host',
			'descr' => 'The outbound connection attempt was not answered, either because the remote system was busy, or otherwise unable to take a call.  This is useful only as a persistent transient error.'
		),
		'4.2' => array(
			'title' => 'Bad connection',
			'descr' => 'The outbound connection was established, but was otherwise unable to complete the message transaction, either because of time-out, or inadequate connection quality. This is useful only as a persistent transient error.'
		),
		'4.3' => array(
			'title' => 'Directory server failure',
			'descr' => 'The network system was unable to forward the message, because a directory server was unavailable.  This is useful only as a persistent transient error. The inability to connect to an Internet DNS server is one example of the directory server failure error.'
		),
		'4.4' => array(
			'title' => 'Unable to route',
			'descr' => 'The mail system was unable to determine the next hop for the message because the necessary routing information was unavailable from the directory server. This is useful for both permanent and persistent transient errors.  A DNS lookup returning only an SOA (Start of Administration) record for a domain name is one example of the unable to route error.'
		),
		'4.5' => array(
			'title' => 'Mail system congestion',
			'descr' => 'The mail system was unable to deliver the message because the mail system was congested. This is useful only as a persistent transient error.'
		),
		'4.6' => array(
			'title' => 'Routing loop detected',
			'descr' => 'A routing loop caused the message to be forwarded too many times, either because of incorrect routing tables or a user forwarding loop. This is useful only as a persistent transient error.'
		),
		'4.7' => array(
			'title' => 'Delivery time expired',
			'descr' => 'The message was considered too old by the rejecting system, either because it remained on that host too long or because the time-to-live value specified by the sender of the message was exceeded. If possible, the code for the actual problem found when delivery was attempted should be returned rather than this code.  This is useful only as a persistent transient error.'
		),
		'5.0' => array(
			'title' => 'Other or undefined protocol status',
			'descr' => 'Something was wrong with the protocol necessary to deliver the message to the next hop and the problem cannot be well expressed with any of the other provided detail codes.'
		),
		'5.1' => array(
			'title' => 'Invalid command',
			'descr' => 'A mail transaction protocol command was issued which was either out of sequence or unsupported.  This is useful only as a permanent error.'
		),
		'5.2' => array(
			'title' => 'Syntax error',
			'descr' => 'A mail transaction protocol command was issued which could not be interpreted, either because the syntax was wrong or the command is unrecognized. This is useful only as a permanent error.'
		),
		'5.3' => array(
			'title' => 'Too many recipients',
			'descr' => 'More recipients were specified for the message than could have been delivered by the protocol.  This error should normally result in the segmentation of the message into two, the remainder of the recipients to be delivered on a subsequent delivery attempt.  It is included in this list in the event that such segmentation is not possible.'
		),
		'5.4' => array(
			'title' => 'Invalid command arguments',
			'descr' => 'A valid mail transaction protocol command was issued with invalid arguments, either because the arguments were out of range or represented unrecognized features. This is useful only as a permanent error.'
		),
		'5.5' => array(
			'title' => 'Wrong protocol version',
			'descr' => 'A protocol version mis-match existed which could not be automatically resolved by the communicating parties.'
		),
		'6.0' => array(
			'title' => 'Other or undefined media error',
			'descr' => 'Something about the content of a message caused it to be considered undeliverable and the problem cannot be well expressed with any of the other provided detail codes.'
		),
		'6.1' => array(
			'title' => 'Media not supported',
			'descr' => 'The media of the message is not supported by either the delivery protocol or the next system in the forwarding path. This is useful only as a permanent error.'
		),
		'6.2' => array(
			'title' => 'Conversion required and prohibited',
			'descr' => 'The content of the message must be converted before it can be delivered and such conversion is not permitted.  Such prohibitions may be the expression of the sender in the message itself or the policy of the sending host.'
		),
		'6.3' => array(
			'title' => 'Conversion required but not supported',
			'descr' => 'The message content must be converted to be forwarded but such conversion is not possible or is not practical by a host in the forwarding path.  This condition may result when an ESMTP gateway supports 8bit transport but is not able to downgrade the message to 7 bit as required for the next hop.'
		),
		'6.4' => array(
			'title' => 'Conversion with loss performed',
			'descr' => 'This is a warning sent to the sender when message delivery was successfully but when the delivery required a conversion in which some data was lost.  This may also be a permanant error if the sender has indicated that conversion with loss is prohibited for the message.'
		),
		'6.5' => array(
			'title' => 'Conversion Failed',
			'descr' => 'A conversion was required but was unsuccessful.  This may be useful as a permanent or persistent temporary notification.'
		),
		'7.0' => array(
			'title' => 'Other or undefined security status',
			'descr' => 'Something related to security caused the message to be returned, and the problem cannot be well expressed with any of the other provided detail codes.  This status code may also be used when the condition cannot be further described because of security policies in force.'
		),
		'7.1' => array(
			'title' => 'Delivery not authorized, message refused',
			'descr' => 'The sender is not authorized to send to the destination. This can be the result of per-host or per-recipient filtering.  This memo does not discuss the merits of any such filtering, but provides a mechanism to report such. This is useful only as a permanent error.'
		),
		'7.2' => array(
			'title' => 'Mailing list expansion prohibited',
			'descr' => 'The sender is not authorized to send a message to the intended mailing list. This is useful only as a permanent error.'
		),
		'7.3' => array(
			'title' => 'Security conversion required but not possible',
			'descr' => 'A conversion from one secure messaging protocol to another was required for delivery and such conversion was not possible. This is useful only as a permanent error.'
		),
		'7.4' => array(
			'title' => 'Security features not supported',
			'descr' => 'A message contained security features such as secure authentication which could not be supported on the delivery protocol. This is useful only as a permanent error.'
		),
		'7.5' => array(
			'title' => 'Cryptographic failure',
			'descr' => 'A transport system otherwise authorized to validate or decrypt a message in transport was unable to do so because necessary information such as key was not available or such information was invalid.'
		),
		'7.6' => array(
			'title' => 'Cryptographic algorithm not supported',
			'descr' => 'A transport system otherwise authorized to validate or decrypt a message was unable to do so because the necessary algorithm was not supported.'
		),
		'7.7' => array(
			'title' => 'Message integrity failure',
			'descr' => 'A transport system otherwise authorized to validate a message was unable to do so because the message was corrupted or altered.  This may be useful as a permanent, transient persistent, or successful delivery code.'
		)
	);
	
	
	
	
	//// static methods
	
	//
	public static function _getDeliveryStatusCode( $sCode ) {
		
		$aCode = explode( '.', $sCode );
		
		$sDetails = self::$_aCodeClasses[ $aCode[ 0 ] ][ 'title' ];
		if ( !$sDetails ) $sDetails = 'Unknown Status'; 
		
		if ( $sSubclass = self::$_aCodeSubclasses[ $aCode[ 1 ] . '.' . $aCode[ 2 ] ][ 'title' ] ) {
			$sDetails .= ': ' . $sSubclass;
		}
		
		return $sDetails;	
	}
	
	
	
	//// methods
	
	//
	public function __construct( $oZendMail ) {
		
		$this->_oZendMail = $oZendMail;
		
		// get header names
		$aHeaders = $oZendMail->getHeaders();
		
		// $mValue is not used
		foreach ( $aHeaders as $sName => $mValue ) {
			$this->_aHeaderNames[] = strtolower( $sName );
		}
	}
	
	
	
	//// accessors
	
	// the native Zend_Mail_Message getHeader function throws an exception for a non-existent
	// header, use this so we don't have to worry about catching exceptions
	public function getHeaderSafe( $sHeader ) {
		
		$oZendMail = $this->_oZendMail;
		
		$sHeader = strtolower( $sHeader );		// normalize
		if ( in_array( $sHeader, $this->_aHeaderNames ) ) {
			
			$mValue = $oZendMail->getHeader( $sHeader );
			
			// always return results as a string
			return ( is_array( $mValue ) ) ? implode( self::HEADER_DELIM, $mValue ) : $mValue ;
		}
		return FALSE;
	}
	
	//
	public function isBounce() {
		if ( preg_match( self::BOUNCE_REGEX_SUBJECT, $this->getHeaderSafe( 'subject' ) ) ) return TRUE;
		if ( preg_match( self::BOUNCE_REGEX_AUTOREPLY, $this->getHeaderSafe( 'precedence' ) ) ) return TRUE;
		if ( preg_match( self::BOUNCE_REGEX_FROM, $this->getHeaderSafe( 'from' ) ) ) return TRUE;
		return FALSE;
	}
	
	//
	public function getDeliveryStatusCode() {
		
		$oZendMail = $this->_oZendMail;
		
		if ( $this->isBounce() ) {
			
			$sContent = $oZendMail->getContent();
			
			foreach ( self::$_aBouncePatterns as $sCode => $aPattern ) {
				foreach ( $aPattern as $sPattern ) {
					if ( FALSE !== stristr( $sContent, $sPattern ) ) {
						return $sCode;
					}
				}
			}
			
			return '5.0.0';
		}
		
		return '2.0.0';
	}
	
	//
	public function getDeliveryStatusDetails() {
		return self::_getDeliveryStatusCode( $this->getDeliveryStatusCode() );
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		//// delegate calls to Zend_Mail_Message
		if ( $oZendMail = $this->_oZendMail ) {
			if ( method_exists( $oZendMail, $sMethod ) ) {
				return call_user_func_array(
					array( $oZendMail, $sMethod ),
					$aArgs
				);
			}
		}
		
		throw new Exception( 'Invalid method ' . __CLASS__ . '::' . $sMethod . '() called.' );
	}
	
	
}
