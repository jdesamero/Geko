<?php

// listing
class Geko_Wp_EmailMessage_Storage_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sEntityIdVarName = 'strg_id';
	
	protected $_sSubject = 'Email Message Storage';
	protected $_sSubjectPlural = 'Email Message Storage';
	protected $_sMenuTitle = 'Storage';
	protected $_sListingTitle = 'Label';
	protected $_sDescription = 'Facility for handling (local or remote) mailboxes.';
	protected $_sIconId = 'icon-tools';
	protected $_sType = 'strg';
	
	protected $_aJsParams = array(
		'conditional_toggle' => array(
			'strg_type_id' => array(
				'enum' => 'geko-emsg-strg-type'
			)
		)
	);
	
	protected $_iEntitiesPerPage = 10;
	
	
	
	
	
	//
	protected function __construct() {
		Geko_Wp_Enumeration_Manage::getInstance()->init();
		parent::__construct();
	}
	
	
	//// init
	
	
	//
	public function add() {
		
		parent::add();
		
		
		Geko_Wp_Enumeration_Manage::getInstance()->add();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_emsg_storage', 't' )
			->fieldBigInt( 'strg_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'label' )
			->fieldVarChar( 'slug', array( 'size' => 256, 'unq' ) )
			->fieldTinyInt( 'type_id', array( 'unsgnd' ) )
			->fieldVarChar( 'server', array( 'size' => 256 ) )
			->fieldVarChar( 'security', array( 'size' => 64 ) )					// should be "ssl", but is a MySQL reserved word
			->fieldInt( 'port', array( 'unsgnd' ) )
			->fieldVarChar( 'username', array( 'size' => 256 ) )
			->fieldVarChar( 'password', array( 'size' => 256 ) )
			->fieldLongText( 'filename' )
			->fieldLongText( 'dirname' )
			->fieldVarChar( 'folder', array( 'size' => 256 ) )
			->fieldVarChar( 'delim', array( 'size' => 16 ) )
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
			array( 'title' => 'Email Message Storage Type', 'slug' => 'geko-emsg-strg-type', 'description' => 'List of email storage types.' ),
			array(
				array( 'title' => 'Mbox', 'slug' => 'geko-emsg-strg-type-mbox', 'value' => 1, 'rank' => 0, 'description' => 'Open local Mbox storage file.' ),
				array( 'title' => 'Maildir', 'slug' => 'geko-emsg-strg-type-maildir', 'value' => 2, 'rank' => 1, 'description' => 'Open local Maildir location.' ),
				array( 'title' => 'POP3', 'slug' => 'geko-emsg-strg-type-pop3', 'value' => 3, 'rank' => 2, 'description' => 'Connect to POP3 server.' ),
				array( 'title' => 'IMAP', 'slug' => 'geko-emsg-strg-type-imap', 'value' => 4, 'rank' => 3, 'description' => 'Connect to IMAP server.' )
			)
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
		
		$aTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-strg-type' );
		
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
		
		$aTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-strg-type' );
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<style type="text/css">
			
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
			
			.short {
				width: 10em !important;
			}
			
			.long {
				width: 500px !important;
			}
			
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="strg_label">Label</label></th>
				<td>
					<input id="strg_label" name="strg_label" type="text" class="regular-text" value="" /><br />
					<span class="description">Label of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="strg_slug">Slug</label></th>
				<td>
					<input id="strg_slug" name="strg_slug" type="text" class="regular-text" value="" /><br />
					<span class="description">Slug of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="strg_type_id">Type</label></th>
				<td>
					<select name="strg_type_id" id="strg_type_id">
						<option value="">Select Type</option>
						<?php echo $aTypes->implode( '<option value="##Value##">##Title##</option>' ); ?>
					</select><br />
					<span class="description">Specify a mail storage option.</span>
				</td>			
			</tr>
			<tr class="cond imap pop3">
				<th><label for="strg_server">Server</label></th>
				<td><input id="strg_server" name="strg_server" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond imap pop3">
				<th><label for="strg_security">SSL</label></th>
				<td><input id="strg_security" name="strg_security" type="text" class="regular-text short" value="" /></td>
			</tr>
			<tr class="cond imap pop3">
				<th><label for="strg_port">Port</label></th>
				<td><input id="strg_port" name="strg_port" type="text" class="regular-text short" value="" /></td>
			</tr>
			<tr class="cond imap pop3">
				<th><label for="strg_username">Username</label></th>
				<td><input id="strg_username" name="strg_username" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond imap pop3">
				<th><label for="strg_password">Password</label></th>
				<td><input id="strg_password" name="strg_password" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond mbox">
				<th><label for="strg_filename">File Name</label></th>
				<td><input id="strg_filename" name="strg_filename" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond mbox maildir">
				<th><label for="strg_dirname">Directory Path</label></th>
				<td><input id="strg_dirname" name="strg_dirname" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond imap mbox maildir">
				<th><label for="strg_folder">Folder Name</label></th>
				<td><input id="strg_folder" name="strg_folder" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond maildir">
				<th><label for="strg_delim">Delimiter</label></th>
				<td><input id="strg_delim" name="strg_delim" type="text" class="regular-text short" value="" /></td>
			</tr>
			<tr>
				<th><label for="strg_notes">Notes</label></th>
				<td><textarea cols="30" rows="5" id="strg_notes" name="strg_notes" /></td>
			</tr>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	
	//// crud methods
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'label' ];
		$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
		);
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
		
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
		
		$sLabel = $aValues[ 'label' ];
		$iTypeId = $aValues[ 'type_id' ];
		
		// check title
		if ( $bContinue && !$sLabel ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty subject was given
		}
		
		if ( $bContinue && !$iTypeId ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm203' );										// Type must be specified !!!
		}
		
		return $bContinue;
		
	}
	
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'label' ];
		if ( $aValues[ 'slug' ] != $oEntity->getSlug() ) {
			$aValues[ 'slug' ] = Geko_Wp_Db::generateSlug(
				$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
			);
		}
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}
	
	
}

