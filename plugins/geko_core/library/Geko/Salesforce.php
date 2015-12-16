<?php
/*
 * "geko_core/library/Geko/Salesforce.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */


define( 'GEKO_SALESFORCE_LIB_DIR', sprintf(
	'%s/external/libs/salesforce-soapclient',
	dirname( dirname( dirname( __FILE__ ) ) )
) );

require_once( sprintf( '%s/SforcePartnerClient.php', GEKO_SALESFORCE_LIB_DIR ) );


//
class Geko_Salesforce
{
	
	const SESS_KEY = 'GEKO_SALESFORCE';
	
	const CONN_NEW = 1;
	const CONN_EXISTS = 2;
	
	protected $_iConnType = NULL;
	protected $_oSfConn = NULL;
	protected $_aSfSess = array();
	
	
	
	//
	public function __construct( $sUsername, $sPassword, $sToken, $sSoapClientPath = '', $sEndPoint = NULL ) {
		
		try {
			
			// use default salesforce lib path, if not specified
			if ( !$sSoapClientPath ) $sSoapClientPath = GEKO_SALESFORCE_LIB_DIR;
			
			
			$aSfSess = $_SESSION[ self::SESS_KEY ];
			
			if (
				( !$aSfSess[ 'ts' ] ) || 
				(
					( $iSessTs = $aSfSess[ 'ts' ] ) && 
					( ( time() - $iSessTs ) > ( 60 * 30 )  )			// 30 mins
				)
			) {
				$aSfSess = array();					// expire the session
			}
			
			
			$oSfConn = new SforcePartnerClient();
			$oSfConn->createConnection( sprintf( '%s/partner.wsdl.xml', $sSoapClientPath ) );
			
			if ( $aSfSess[ 'session_id' ] ) {
			
				$oSfConn->setSessionHeader( $aSfSess[ 'session_id' ] );
				$oSfConn->setEndpoint( $aSfSess[ 'location' ] );
				
				// Using existing session
				$this->_iConnType = self::CONN_EXISTS;
				
			} else {
				
				$sPwdTok = sprintf( '%s%s', $sPassword, $sToken );
				
				if ( $sEndPoint ) {
					$oSfConn->setEndpoint( $sEndPoint );
				}
				
				$oSfConn->login( $sUsername, $sPwdTok );
				
				$aSfSess = array(
					'session_id' => $oSfConn->getSessionId(),
					'location' => $oSfConn->getLocation(),
					'ts' => time()
				);
				
				// Using new session...
				$this->_iConnType = self::CONN_NEW;
				
			}
			
			
			// assign
			$this->_oSfConn = $oSfConn;
			$this->_aSfSess = $aSfSess;
			
			
		} catch ( Exception $e ) {
			
			echo strval( $e );
			
		}
		
	}
	
	
	//
	public function getSfConn() {
		return $this->_oSfConn;
	}
	
	
	
	// - - - - - - generic methods - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	
	//
	public function getRecords( $sQuery, $sType = '' ) {
		
		if ( $oSfConn = $this->_oSfConn ) {
			
			$aRes = array();
			
			$oResponse = $oSfConn->query( $sQuery );
			$oQueryResult = new QueryResult( $oResponse );
			
			for (
				$oQueryResult->rewind();
				$oQueryResult->pointer < $oQueryResult->size;
				$oQueryResult->next()
			) {
				
				$oRecord = $oQueryResult->current();
				
				// normalize record if type was provided
				if ( $sType ) {
					
					$oFields = $oRecord->fields;
					
					if ( $oRecord->Id ) {
						$oFields->Id = $oRecord->Id;
					}
					
					$oRecord = $oFields;
				
				}
				
				$aRes[] = $oRecord;
			}
			
			if ( count( $aRes ) > 0 ) {
				return $aRes;
			}
		}
		
		return NULL;
	}
		
	
	//
	public function newObject( $sType, $mFields ) {
		
		if ( is_object( $mFields ) ) {
			$aFields = ( array ) $mFields;
		} else {
			$aFields = $mFields;
		}
		
		$oSfObj = new sObject();
		$oSfObj->type = $sType;
		
		if ( $sId = $aFields[ 'Id' ] ) {
			$oSfObj->Id = $sId;
			unset( $aFields[ 'Id' ] );
		}
		
		
		// sanitize html entities
		foreach ( $aFields as $sKey => $mValue ) {
			if ( is_string( $mValue ) ) {
				$aFields[ $sKey ] = htmlspecialchars( $mValue );
			}
		}
		
		$oSfObj->fields = $aFields;
		
		
		return $oSfObj;
	}
	
	
	// remotely create salesforce object/s
	public function createSfObject( $mSfObj ) {
		
		if ( $oSfConn = $this->_oSfConn ) {
			
			$bWrapped = FALSE;
			
			if ( is_object( $mSfObj ) ) {
				$aSfObj = array( $mSfObj );
				$bWrapped = TRUE;
			} else {
				$aSfObj = $mSfObj;
			}
			
			$aResult = $oSfConn->create( $aSfObj );
			
			foreach ( $aResult as $i => $oRes ) {
				
				if ( $oRes->success && $oRes->id ) {
					$aSfObj[ $i ]->Id = $oRes->id;
				}
			}
			
			return $bWrapped ? $aSfObj[ 0 ] : $aSfObj ;
		}
		
		return NULL;
	}
	
	
	//
	public function createSingleResAssign( $oSfObj, $aResult ) {
		
		if (
			( $oMyRes = $aResult[ 0 ] ) &&
			( $oMyRes->success )
		) {
			$oSfObj->Id = $oMyRes->id;
		}
		
		return $oSfObj;
	}
	
	
	// format to unit timestamp
	public function formatToTs( $mTime ) {
		
		if ( NULL === $mTime ) {
			$iTs = time();
		} else {
			if ( is_int( $mTime ) ) {
				$iTs = $mTime;
			} else {
				$iTs = strtotime( $mTime );
			}
		}
		
		return $iTs;
	}
	
	//
	public function formatSfId( $mSfId ) {

		if ( is_object( $mSfId ) ) {
			$sSfId = $mSfId->Id;
		} else {
			$sSfId = $mSfId;
		}
		
		return $sSfId;
	}
	
	
	// - - - - - - object specific methods - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	
	
	//// leads
	
	//
	public function getLeadByEmail( $mEmail, $aFields = NULL ) {
		
		$bWrapped = FALSE;
		
		if ( is_array( $mEmail ) ) {
			$aEmails = $mEmail;		
		} else {
			$bWrapped = TRUE;
			$aEmails = array( $mEmail );
		}
		
		
		// protect against injections
		$aEmails = array_map( 'addslashes', $aEmails );
		
		// default fields
		if ( NULL === $aFields ) {
			$aFields = array( 'FirstName', 'LastName', 'Phone', 'Email' );
		}
		
		$aFields[] = 'Id';		// required
		
		$sQuery = sprintf( "
			
			SELECT			%s
			
			FROM			Lead
			
			WHERE			Email IN ( '%s' )
			
			",
			implode( ', ', $aFields ),
			implode( "', '", $aEmails )
		);
		
		if ( $aRes = $this->getRecords( $sQuery, 'Lead' ) ) {
			return $bWrapped ? $aRes[ 0 ] : $aRes ;
		}
		
		return NULL;
	}
	
	
	
	/* Overloading:
	 * addLead( $sEmail, $sFirstName, $sLastName, $sCompany, $aOther = array() )
	 * addLead( $aFields )
	 */
	public function addLead() {
		
		if ( $oSfConn = $this->_oSfConn ) {
			
			$aArgs = func_get_args();
			
			if (
				( 1 == count( $aArgs ) ) && 
				( is_array( $aArgs[ 0 ] ) )
			) {
				
				$aFields = $aArgs[ 0 ];
			
			} else {

				$aFields = array(
					'Email' => $aArgs[ 0 ],
					'FirstName' => $aArgs[ 1 ],
					'LastName' => $aArgs[ 2 ],
					'Company' => $aArgs[ 3 ]
				);
				
				if ( is_array( $aArgs[ 4 ] ) ) {
					$aFields = array_merge( $aFields, $aArgs[ 4 ] );
				}
				
			}
			
			
			$oLead = $this->newObject( 'Lead', $aFields );

			$aResult = $oSfConn->create( array( $oLead ) );
			
			return $this->createSingleResAssign( $oLead, $aResult );
		}
		
		return NULL;
	}
	
	
	
	
	/* Overloading:
	 * addOrEditLead( $oLead, $sEmail, $sFirstName, $sLastName, $sCompany, $aOther = array() )
	 * addOrEditLead( $sEmail, $sFirstName, $sLastName, $sCompany, $aOther = array() )
	 * addOrEditLead( $aFields )
	 */
	public function addOrEditLead() {
		
		if ( $oSfConn = $this->_oSfConn ) {
			
			$aArgs = func_get_args();
			
			if (
				( 1 == count( $aArgs ) ) && 
				( is_array( $aArgs[ 0 ] ) )
			) {
				
				$aFields = $aArgs[ 0 ];
			
			} else {
				
				if ( is_object( $aArgs[ 0 ] ) ) {
					// we're expecting this to be a fully formed lead, with Id
					$oLead = array_shift( $aArgs );
				}
								
				// these are required fields
				$aFields = array(
					'Email' => $aArgs[ 0 ],
					'FirstName' => $aArgs[ 1 ],
					'LastName' => $aArgs[ 2 ],
					'Company' => $aArgs[ 3 ]
				);
				
				if ( is_array( $aArgs[ 4 ] ) ) {
					$aFields = array_merge( $aFields, $aArgs[ 4 ] );
				}
				
			}
			
			
			// $oLead may be NULL
			$aCurFields = $this->getLeadByEmail( $aArgs[ 0 ], array_keys( $aFields ) );
			if ( $aCurFields ) {
				$oLead = $this->newObject( 'Lead', $aCurFields );
			}
			
			
			
			$aChanged = NULL;		// use this to track changes
			
			
			//
			if ( $oLead ) {
				
				// edit existing lead
				
				// track which values were changed
				$aOldFields = $oLead->fields;
				$aChanged = array();
				
				foreach ( $aOldFields as $sKey => $sOldValue ) {
					if (
						( 'Id' != $sKey ) && 
						( $sOldValue != $aFields[ $sKey ] )
					) {
						$aChanged[ $sKey ] = $sOldValue;
					}
				}
				
				// don't set the LeadSource field if updating an existing lead
				if ( $aFields[ 'LeadSource' ] ) {
					unset( $aFields[ 'LeadSource' ] );
				}
				
				$oLead->fields = $aFields;
				
				$oSfConn->update( array( $oLead ) );
				
				
			} else {
				
				// add new lead
				
				$oLead = $this->newObject( 'Lead', $aFields );
	
				$aResult = $oSfConn->create( array( $oLead ) );
				
				$oLead = $this->createSingleResAssign( $oLead, $aResult );				
			}
			
			
			return array( $oLead, $aChanged );
		}
		
		return array();
	}
	
	
	//
	public function addLeadToCampaign( $oLead, $sCampaignId, $sStatus = NULL ) {
		
		if ( $oSfConn = $this->_oSfConn ) {

			$sLeadId = $oLead->Id;
			
			// check if lead is already a member
			$sCheckQuery = sprintf(
				"SELECT Id FROM CampaignMember WHERE CampaignId = '%s' AND LeadId = '%s'",
				$sCampaignId,
				$sLeadId
			);
			
			if ( !$this->getRecords( $sCheckQuery, 'CampaignMember' ) ) {
				
				$aValues = array(
					'CampaignId' => $sCampaignId,
					'LeadId' => $sLeadId
				);
				
				if ( $sStatus ) $aValues[ 'Status' ] = $sStatus;
				
				$oCampaignMember = $this->newObject( 'CampaignMember', $aValues );
				
				$aResult = $oSfConn->create( array( $oCampaignMember ) );
				
				return $this->createSingleResAssign( $oCampaignMember, $aResult );
			}
			
		}
		
		return NULL;		
	}
	
	
	
	
	
	//// contacts
	
	//
	public function getContactByEmail( $mEmail, $aFields = NULL ) {
		
		$bWrapped = FALSE;
		
		if ( is_array( $mEmail ) ) {
			$aEmails = $mEmail;		
		} else {
			$bWrapped = TRUE;
			$aEmails = array( $mEmail );
		}
		
		
		// protect against injections
		$aEmails = array_map( 'addslashes', $aEmails );
		
		// default fields
		if ( NULL === $aFields ) {
			$aFields = array( 'FirstName', 'LastName', 'Phone', 'Email' );
		}
		
		$aFields[] = 'Id';		// required
		
		$sQuery = sprintf( "
			
			SELECT			%s
			
			FROM			Contact
			
			WHERE			Email IN ( '%s' )
			
			",
			implode( ', ', $aFields ),
			implode( "', '", $aEmails )
		);
		
		if ( $aRes = $this->getRecords( $sQuery, 'Contact' ) ) {
			return $bWrapped ? $aRes[ 0 ] : $aRes ;
		}
		
		return NULL;
	}
	
	
	
	/* Overloading:
	 * addLead( $sEmail, $sFirstName, $sLastName, $sCompany, $aOther = array() )
	 * addLead( $aFields )
	 */
	public function addContact() {
		
		if ( $oSfConn = $this->_oSfConn ) {
			
			$aArgs = func_get_args();
			
			if (
				( 1 == count( $aArgs ) ) && 
				( is_array( $aArgs[ 0 ] ) )
			) {
				
				$aFields = $aArgs[ 0 ];
			
			} else {

				$aFields = array(
					'Email' => $aArgs[ 0 ],
					'FirstName' => $aArgs[ 1 ],
					'LastName' => $aArgs[ 2 ],
					'Description' => $aArgs[ 3 ]
				);
				
				if ( is_array( $aArgs[ 4 ] ) ) {
					$aFields = array_merge( $aFields, $aArgs[ 4 ] );
				}
				
			}
			
			
			$oLead = $this->newObject( 'Contact', $aFields );

			$aResult = $oSfConn->create( array( $oLead ) );
			
			return $this->createSingleResAssign( $oLead, $aResult );
		}
		
		return NULL;
	}
	
	
	
	//// tasks
	
	//
	public function addTask( $mWhoId, $Subject, $sDescription = '', $mTime = NULL, $sStatus = 'Completed' ) {
		
		if ( $oSfConn = $this->_oSfConn ) {
						
			$aFields = array(
				'Subject' => $Subject,
				'Description' => $sDescription,
				'WhoId' => $this->formatSfId( $mWhoId ),
				'ActivityDate' => date( 'c', $this->formatToTs( $mTime ) ),
				'Status' => $sStatus
			);
			
			$oTask = $this->newObject( 'Task', $aFields );

			$aResult = $oSfConn->create( array( $oTask ) );
			
			return $this->createSingleResAssign( $oTask, $aResult );
		}
		
		return NULL;
	}
	
	
	
	
	//// cases
	
	//
	public function addCase( $mSfId, $Subject, $sDescription = '', $sStatus = 'New' ) {
		
		if ( $oSfConn = $this->_oSfConn ) {
						
			$aFields = array(
				'Subject' => $Subject,
				'Description' => $sDescription,
				'ContactId' => $this->formatSfId( $mSfId ),
				'Status' => $sStatus
			);
			
			$oCase = $this->newObject( 'Case', $aFields );

			$aResult = $oSfConn->create( array( $oCase ) );
			
			return $this->createSingleResAssign( $oCase, $aResult );
		}
		
		return NULL;
	}
	
	
	
	// - - - - - - assignment rules - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	
	
	// covenience method for $oSfConn->setAssignmentRuleHeader( ... )
	public function setAssignmentRule( $sId ) {
		
		if ( $oSfConn = $this->_oSfConn ) {
		
			$oHeader = new AssignmentRuleHeader( $sId );
			
			$oSfConn->setAssignmentRuleHeader( $oHeader );
		}
		
		return NULL;
	}
	
	
	// TO DO: ??? is this necessary ???
	public function unsetAssignmentRule() {
	
	}
	
	
	
	// - - - - - - clean-up methods - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
	
	//
	public function __destruct() {
		
		// track session data, if any
		$_SESSION[ self::SESS_KEY ] = $this->_aSfSess;
		
	}
	
	
	
}




