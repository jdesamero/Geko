<?php

//
class Geko_Wp_EmailMessage_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'emsg_id';
	
	protected $_sSubject = 'Email Message';
	protected $_sMenuTitle = 'Messages';
	protected $_sListingTitle = 'Subject';
	protected $_sDescription = 'An API/UI that handles emails that are sent out for notifications and other purposes.';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'emsg';
	
	protected $_aSubOptions = array(
		'Geko_Wp_EmailMessage_Recipient_Manage',
		'Geko_Wp_EmailMessage_Header_Manage'
	);
	
	protected $_sTabGroupTitle = 'Email Messages';
	protected $_aTabGroup = array(
		'Geko_Wp_EmailMessage_Transport_Manage',
		'Geko_Wp_EmailMessage_Storage_Manage'	
	);
	
	protected $_aCustomActions = array(
		'detect_bounces' => array(
			'button' => TRUE,
			'hidden_field' => TRUE
		),
		'view_bounces' => array(
			'button' => TRUE,
			'dialog' => TRUE
		)
	);
	
	protected $_aJsParams = array(
		'conditional_toggle' => array(
			'emsg_type_id' => array(
				'enum' => 'geko-emsg-type'
			)
		)
	);
	
	protected $_iEntitiesPerPage = 10;
	protected $_bExtraForms = TRUE;
	
	protected $_bCanImport = TRUE;
	protected $_bCanExport = TRUE;
	protected $_bCanDuplicate = TRUE;
	protected $_bCanRestore = TRUE;
	
	
	
	//
	protected function __construct() {
		Geko_Wp_Enumeration_Manage::getInstance()->init();
		parent::__construct();
	}
	
	
	//// init
	
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		Geko_Wp_Enumeration_Manage::getInstance()->add();
		
		$sTableName = 'geko_email_message';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'e' )
			->fieldBigInt( 'emsg_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'subject' )
			->fieldVarChar( 'slug', array( 'size' => 255, 'unq' ) )
			->fieldLongText( 'from_name' )
			->fieldVarChar( 'from_email', array( 'size' => 255 ) )
			->fieldTinyInt( 'type_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'body_text' )
			->fieldLongText( 'body_html' )
			->fieldBool( 'body_html_is_raw' )
			->fieldBigInt( 'trpt_id', array( 'unsgnd', 'key' ) )
			->fieldLongText( 'notes' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	
	
	// create table
	public function install() {
		
		parent::install();
		
		Geko_Once::run( sprintf( '%s::enumeration', __CLASS__ ), array( $this, 'installEnumeration' ) );
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	//
	public function installEnumeration() {
		
		Geko_Wp_Enumeration_Manage::getInstance()->install();
		Geko_Wp_Enumeration_Manage::populate( array(
			array( 'title' => 'Email Message Type', 'slug' => 'geko-emsg-type', 'description' => 'List of email formatting options.' ),
			array(
				array( 'title' => 'Plain Text', 'slug' => 'geko-emsg-type-text', 'value' => 1, 'rank' => 0, 'description' => 'Send email as plain text, with MIME type "text/plain".' ),
				array( 'title' => 'HTML', 'slug' => 'geko-emsg-type-html', 'value' => 2, 'rank' => 1, 'description' => 'Send email as HTML, with MIME type "text/html".' ),
				array( 'title' => 'Both', 'slug' => 'geko-emsg-type-both', 'value' => 3, 'rank' => 2, 'description' => 'Send email as both text and HTML, with MIME type "multipart/alternative".' )
			)
		) );
		
	}

	
	
		
	//
	public function enqueueAdmin() {
		
		parent::enqueueAdmin();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {			
			wp_enqueue_script( 'geko_wp_emailmessage_manage' );
		}
		
		return $this;
	}
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {


			$oUrl = new Geko_Uri();
			// $sThisUrl = strval( $oUrl );

			
			// view bounces link
			$sTheAction = 'edit' . $this->_sType;
			$oUrl
				->setVar( 'action', $sTheAction )
				->setVar( 'view_bounces', 1 )
			;
			
			$sViewBouncesLink = strval( $oUrl );
			$sViewBouncesLink = htmlspecialchars_decode( wp_nonce_url( $sViewBouncesLink, $this->_sInstanceClass . $sTheAction ) );
			
			$aJsonParams = array(
				'prefix' => $this->_sType . '_',
				'bounces_link' => $sViewBouncesLink
			);
			
			?><script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
					
					$.gekoWpEmailMessageManage( oParams );
					
				} );
				
			</script><?php
		}
		
		return $this;
	}
	
	
	
	
	
	
	//// error message handling
	
	//
	protected function getNotificationMsgs() {
		return array_merge( parent::getNotificationMsgs(), array(
			'm301' => 'Bounced messages (%d of %d) were processed successfully.'
		) );
	}

	//
	protected function getErrorMsgs() {
		return array_merge( parent::getErrorMsgs(), array(
			'm401' => 'Bad email address given.',
			'm402' => 'There is no associated storage with the selected transport. Please make sure the transport is properly configured.'
		) );
	}
	
	
	
	//// front-end display methods
	
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-slug">Slug</th>
		<th scope="col" class="manage-column column-type">Type</th>
		<th scope="col" class="manage-column column-notes">Notes</th>
		<th scope="col" class="manage-column column-date-created">Date Created</th>
		<th scope="col" class="manage-column column-date-modified">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		
		$aTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-type' );
		
		?>
		<td class="column-slug"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-type"><?php echo $aTypes->getTitleFromValue( $oEntity->getTypeId() ); ?></td>
		<td class="column-notes"><?php $oEntity->echoNotes(); ?></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-modified"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	
		
	
	//
	public function formFields() {
		
		$oEntity = $this->_oCurrentEntity;
		
		$aTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-type' );
		
		$aTransports = new Geko_Wp_EmailMessage_Transport_Query( array(
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		// from name/email address help text
		
		$sFromNameHelp = 'Defaults to "' . get_bloginfo( 'name' ) . '" if not specified.';
		$sFromEmailHelp = 'Defaults to "' . get_bloginfo( 'admin_email' ) . '" if not specified.';
		
		if ( $oEntity ) {
			if (
				( $iTrptId = $oEntity->getTrptId() ) && 
				( $oCurTrpt = $aTransports->subsetId( $iTrptId )->getOne() )
			) {
				$sFromNameHelp = 'Handled by "' . $oCurTrpt->getTitle() . '" transport if not specified.';
				$sFromEmailHelp = $sFromNameHelp;
			}
		}
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<style type="text/css">
			
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}

			#view_bounces .inner {
				overflow: auto;
				width: 100%;
				height: 100%;
			}
			
			#view_bounces .inner table {
				margin: 12px;
			}
			
			#view_bounces .inner p.text {
				margin: 15px;
			}
			
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="emsg_subject">Subject</label></th>
				<td>
					<input id="emsg_subject" name="emsg_subject" type="text" class="regular-text" value="" /><br />
					<span class="description">Subject of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="emsg_slug">Slug</label></th>
				<td>
					<input id="emsg_slug" name="emsg_slug" type="text" class="regular-text" value="" /><br />
					<span class="description">Slug of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<?php $this->customFieldsPre(); ?>
			<tr>
				<th><label for="emsg_from_name">"From" Name</label></th>
				<td>
					<input id="emsg_from_name" name="emsg_from_name" type="text" class="regular-text" value="" /><br />
					<span class="description"><?php echo $sFromNameHelp; ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="emsg_from_email">"From" Email Address</label></th>
				<td>
					<input id="emsg_from_email" name="emsg_from_email" type="text" class="regular-text" value="" /><br />
					<span class="description"><?php echo $sFromEmailHelp; ?></span>
				</td>
			</tr>
			<tr>
				<th><label for="emsg_type_id">Type</label></th>
				<td>
					<select name="emsg_type_id" id="emsg_type_id">
						<option value="">Select Type</option>
						<?php echo $aTypes->implode( '<option value="##Value##">##Title##</option>' ); ?>
					</select><br />
					<span class="description">Specify how the email message should be delivered.</span>
				</td>			
			</tr>
			<tr class="cond text both">
				<th><label for="emsg_body_text">Body Text</label></th>
				<td>
					<textarea cols="30" rows="5" id="emsg_body_text" name="emsg_body_text" /><br />
					<span class="description">Body text of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr class="cond html both">
				<th><label for="emsg_body_html">Body HTML</label></th>
				<td>
					<textarea cols="30" rows="5" id="emsg_body_html" name="emsg_body_html" /><br />
					<span class="description">Body HTML of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr class="cond html both">
				<th><label for="emsg_body_html_is_raw">Body HTML Is Raw</label></th>
				<td>
					<input id="emsg_body_html_is_raw" name="emsg_body_html_is_raw" type="checkbox" value="1" /><br />
					<span class="description">If checked, auto &lt;br /&gt; and &lt;p&gt;&lt;/p&gt; tags are not applied.</span>
				</td>
			</tr>
			<?php if ( count( $aTransports ) > 0 ): ?>
				<tr>
					<th><label for="emsg_trpt_id">Transport</label></th>
					<td>
						<select name="emsg_trpt_id" id="emsg_trpt_id">
							<option value="">Use Default</option>
							<?php echo $aTransports->implode( '<option value="##Id##">##Title##</option>' ); ?>
						</select><br />
						<span class="description">Specify a custom transport for mail delivery.</span>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th><label for="emsg_notes">Notes</label></th>
				<td>
					<textarea cols="30" rows="5" id="emsg_notes" name="emsg_notes" /><br />
					<span class="description">Specify any useful info pertaining to the message, typically, details about when it is triggered.</span>
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	
	
	
	
	//// crud methods
	
		
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
				
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'subject' ];
		$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
		);
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		if ( !isset( $aValues[ 'body_html_is_raw' ] ) ) $aValues[ 'body_html_is_raw' ] = 0;
		
		return $aValues;
		
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
		
		$sSubject = $aValues[ 'subject' ];
		$iTypeId = $aValues[ 'type_id' ];
		$sFromEmail = $aValues[ 'from_email' ];
		
		// check title
		if ( $bContinue && !$sSubject ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty subject was given
		}
		
		if ( $bContinue && !$iTypeId ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm203' );										// Type must be specified !!!
		}

		if ( $bContinue && $sFromEmail && !is_email( $sFromEmail ) ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm401' );										// bad email address given !!!
		}
		
		return $bContinue;
		
	}
	
	
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
				
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'subject' ];
		if ( $aValues[ 'slug' ] != $oEntity->getSlug() ) {
			$aValues[ 'slug' ] = Geko_Wp_Db::generateSlug(
				$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
			);
		}
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		if ( !isset( $aValues[ 'body_html_is_raw' ] ) ) $aValues[ 'body_html_is_raw' ] = 0;
		
		return $aValues;
	}
	
	
	
	// delete overrides
	
	
	
	//// import/export/duplicate serialization methods
	
	//
	public function exportSerialize( $aParams = array() ) {
		
		$iEmsgId = $aParams[ 'entity_id' ];
		if ( !$oEmsg = $aParams[ 'entity' ] ) {
			$oEmsg = new Geko_Wp_EmailMessage( $iEmsgId );
		}
		
		$sTitleOverride = $aParams[ 'title' ];
		$aSerialized = array(
			'subject' => ( $sTitleOverride ) ? $sTitleOverride : $oEmsg->getSubject(),
			'slug' => $oEmsg->getSlug(),
			'from_name' => $oEmsg->getFromName(),
			'from_email' => $oEmsg->getFromEmail(),
			'type_id' => $oEmsg->getTypeId(),
			'body_text' => $oEmsg->getBodyText(),
			'body_html' => $oEmsg->getBodyHtml(),
			'body_html_is_raw' => $oEmsg->getBodyHtmlIsRaw(),
			'notes' => $oEmsg->getNotes()
		);
		
		if ( $iTrptId = $oEmsg->getTrptId() ) {
			$oTrpt = new Geko_Wp_EmailMessage_Transport( $iTrptId );
			if ( $oTrpt->isValid() ) {
				$aSerialized[ 'trpt_slug' ] = $oTrpt->getSlug();
			}
		}
		
		
		
		//// geko_emsg_recipients
		
		$aEmsgRcptFmt = array();
		$aEmsgRcpt = new Geko_Wp_EmailMessage_Recipient_Query( array(
			'emsg_id' => $iEmsgId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aEmsgRcpt as $oEmsgRcpt ) {
			$aEmsgRcptFmt[] = array(
				'name' => $oEmsgRcpt->getName(),
				'email' => $oEmsgRcpt->getEmail(),
				'active' => $oEmsgRcpt->getActive()
			);
		}
		
		$aSerialized[ 'recipients' ] = $aEmsgRcptFmt;
		
		
		
		//// geko_emsg_header
		
		$aEmsgHdrFmt = array();
		$aEmsgHdr = new Geko_Wp_EmailMessage_Header_Query( array(
			'emsg_id' => $iEmsgId,
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		foreach ( $aEmsgHdr as $oEmsgHdr ) {
			$aEmsgHdrFmt[] = array(
				'name' => $oEmsgHdr->getName(),
				'val' => $oEmsgHdr->getVal(),
				'multi' => $oEmsgHdr->getMulti()
			);
		}
		
		$aSerialized[ 'headers' ] = $aEmsgHdrFmt;
		
		
		return $aSerialized;
	}
	
	
	
	//
	public function importSerialized( $aSerialized ) {
		
		global $wpdb;
		
		//// do checks
		
		// start transaction
		// NOTE: This only works on InnoDB tables!!!
		
		$bRes = FALSE;
		
		$wpdb->query( 'START TRANSACTION' );
		
		
		// setup values
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		
		$aMainValues = array(
			'subject:%s' => $aSerialized[ 'subject' ],
			'from_name:%s' => $aSerialized[ 'from_name' ],
			'from_email:%s' => $aSerialized[ 'from_email' ],
			'type_id:%d' => $aSerialized[ 'type_id' ],
			'body_text:%s' => $aSerialized[ 'body_text' ],
			'body_html:%s' => $aSerialized[ 'body_html' ],
			'body_html_is_raw:%d' => $aSerialized[ 'body_html_is_raw' ],
			'notes:%s' => $aSerialized[ 'notes' ],
			'date_created:%s' => $sDateTime,
			'date_modified:%s' => $sDateTime
		);
		
		if ( $sTrptSlug = $aSerialized[ 'trpt_slug' ] ) {
			$oTrpt = new Geko_Wp_EmailMessage_Transport( $sTrptSlug );
			if ( $oTrpt->isValid() ) {
				$aMainValues[ 'trpt_id:%d' ] = $oTrpt->getId();
			}
		}
		
		if ( $iRestoreEmsgId = $aSerialized[ 'entity_id' ] ) {
			
			// maintain email message values
			$aMainValues[ 'emsg_id:%d' ] = $iRestoreEmsgId;
			$aMainValues[ 'slug:%s' ] = $aSerialized[ 'slug' ];
			
			// clean up old values
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->geko_emsg_recipients WHERE emsg_id = %d", $iRestoreEmsgId ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->geko_emsg_header WHERE emsg_id = %d", $iRestoreEmsgId ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->geko_email_message WHERE emsg_id = %d", $iRestoreEmsgId ) );
			
		} else {
			
			// generate a new slug, if needed
			$aMainValues[ 'slug:%s' ] = Geko_Wp_Db::generateSlug(
				$aSerialized[ 'slug' ], $wpdb->geko_email_message, 'slug'
			);
			
		}
		
		
		//// geko_email_message
		
		$bRes = Geko_Wp_Db::insert( 'geko_email_message', $aMainValues );
		
		
		
		//// geko_emsg_recipients
		
		if ( $bRes ) {
			
			$iDupEmsgId = ( $iRestoreEmsgId ) ? $iRestoreEmsgId : $wpdb->insert_id;
			
			$aEmsgRcpt = $aSerialized[ 'recipients' ];
			
			foreach ( $aEmsgRcpt as $aRecipient ) {
				
				$bRes = Geko_Wp_Db::insert(
					'geko_emsg_recipients',
					array(
						'emsg_id:%d' => $iDupEmsgId,
						'name:%s' => $aRecipient[ 'name' ],
						'email:%s' => $aRecipient[ 'email' ],
						'active:%d' => $aRecipient[ 'active' ]
					)
				);
				
				if ( !$bRes ) break;
			}		
		}



		//// geko_emsg_header
		
		if ( $bRes ) {
			
			$aEmsgHdr = $aSerialized[ 'headers' ];
			
			foreach ( $aEmsgHdr as $aHeader ) {
				
				$bRes = Geko_Wp_Db::insert(
					'geko_emsg_header',
					array(
						'emsg_id:%d' => $iDupEmsgId,
						'name:%s' => $aHeader[ 'name' ],
						'val:%s' => $aHeader[ 'val' ],
						'multi:%d' => $aHeader[ 'multi' ]
					)
				);
				
				if ( !$bRes ) break;
			}		
		}		
		
		
		
		// commit if no errors
		if ( $bRes ) {
			$wpdb->query( 'COMMIT' );
			return array(
				'dup_entity_id' => $iDupEmsgId
			);
		}
		
		// rollback if there are errors
		$wpdb->query( 'ROLLBACK' );		
		return FALSE;
	}
	
	
	
	
	// detect bounces
	
	//
	public function doDetectBouncesAction( $aParams ) {
		
		$bContinue = TRUE;
		
		$iEmsgId = $_POST[ 'emsg_id' ];
		$oEmsg = new Geko_Wp_EmailMessage( $iEmsgId );
		$iStrgId = $oEmsg->getStrgId();
		
		if ( $bContinue && !$oEmsg->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );
		}
		
		if ( $bContinue && !$iStrgId ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm402' );		
		}
		
		if ( $bContinue ) {

			$oSt = new Geko_Wp_EmailMessage_Storage( $iStrgId );
			$aRes = $oSt->saveMessagesToDb();
			
			$this->triggerNotifyMsg( 'm301', $aRes[ 'deleted' ], $aRes[ 'count' ] );
		}
		
		return $aParams;
	}
	
	
	
	//
	public function doViewBouncesAction( $aParams ) {
		
		if ( !$aMeta = $aParams[ 'meta' ] ) {
			$aMeta = array( 'geko_emsg_id' => array(
				'type' => 'int',
				'val' => $_REQUEST[ 'emsg_id' ]
			) );
		}
		
		$aLogs = new Geko_Wp_EmailMessage_Storage_Log_Query( array(
			'showposts' => -1,
			'posts_per_page' => -1,
			'meta' => $aMeta
		), FALSE );
		
		if ( $aLogs->count() > 0 ):
			?>
			<style type="text/css">
			
				table.bnc th,
				table.bnc td {
					padding: 3px 9px;
				}
				
				table.bnc th,
				table.bnc td.exp {
					text-align: center;
				}
				
				table.bnc tr.row1 td.exp a {
					font-weight: bold;
					text-decoration: none;
				}
				
				table.bnc tr.row2 {
					display: none;
				}
				
				table.bnc tr.row2 td {
					border: solid 1px #dfdfdf;					
				}
				
				table.bnc tr.row2 td pre {
					white-space: pre-wrap;
					white-space: -moz-pre-wrap;
					white-space: -pre-wrap;
					white-space: -o-pre-wrap;
					word-wrap: break-word;
				}
				
			</style>
			<table class="bnc">
				<tr>
					<th>Details</th>
					<th>Failed Recipient</th>
					<th>Bounce Reason</th>
					<th>Date</th>
				</tr>
				<?php foreach ( $aLogs as $i => $oLog ): ?>
					<tr class="row1" id="r1_<?php echo $i; ?>">
						<td class="exp"><a href="#">Show</a></td>
						<td><?php $oLog->echoFailedRecipient(); ?></td>
						<td><?php $oLog->echoDeliveryStatusDetails(); ?></td>
						<td><?php $oLog->echoDateParsedFmt(); ?></td>
					</tr>
					<tr class="row2" id="r2_<?php echo $i; ?>">
						<td colspan="4"><pre><?php echo htmlspecialchars( $oLog->getMessageBody() ); ?></pre></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php
		else:
			?>
			<p class="text">No matching bounced messages were found.</p>
			<?php
		endif;
		
		die();
	}
	
	
	
	
}



