<?php

// listing
class Geko_Wp_EmailMessage_Transport_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'trpt_id';
	
	protected $_sSubject = 'Email Message Transport';
	protected $_sMenuTitle = 'Transports';
	protected $_sListingTitle = 'Label';
	protected $_sDescription = 'Alternate email transports for sending email.';
	protected $_sIconId = 'icon-plugins';
	protected $_sType = 'trpt';
	
	protected $_aJsParams = array(
		'conditional_toggle' => array(
			'trpt_type_id' => array(
				'enum' => 'geko-emsg-trpt-type'
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
			->create( '##pfx##geko_emsg_transport', 't' )
			->fieldBigInt( 'trpt_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'label' )
			->fieldVarChar( 'slug', array( 'size' => 255, 'unq' ) )
			->fieldTinyInt( 'type_id', array( 'unsgnd' ) )
			->fieldBigInt( 'strg_id', array( 'unsgnd' ) )
			->fieldVarChar( 'server', array( 'size' => 256 ) )
			->fieldVarChar( 'security', array( 'size' => 64 ) )					// should be "ssl", but is a MySQL reserved word
			->fieldInt( 'port', array( 'unsgnd' ) )
			->fieldVarChar( 'auth', array( 'size' => 256 ) )
			->fieldVarChar( 'username', array( 'size' => 256 ) )
			->fieldVarChar( 'password', array( 'size' => 256 ) )
			->fieldLongText( 'params' )
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
			array( 'title' => 'Email Message Transport Type', 'slug' => 'geko-emsg-trpt-type', 'description' => 'List of email transport types.' ),
			array(
				array( 'title' => 'Sendmail', 'slug' => 'geko-emsg-trpt-type-sendmail', 'value' => 1, 'rank' => 0, 'description' => 'Send email via "sendmail".' ),
				array( 'title' => 'SMTP', 'slug' => 'geko-emsg-trpt-type-smtp', 'value' => 2, 'rank' => 1, 'description' => 'Send email via SMTP.' )
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
		
		$aTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-trpt-type' );
		
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
		
		$aTypes = Geko_Wp_Enumeration_Query::getSet( 'geko-emsg-trpt-type' );
		
		$aParams = array(
			'posts_per_page' => -1,
			'showposts' => -1
		);
		$aEmsgStrg = new Geko_Wp_EmailMessage_Storage_Query( $aParams, FALSE );
		
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
			
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="trpt_label">Label</label></th>
				<td>
					<input id="trpt_label" name="trpt_label" type="text" class="regular-text" value="" /><br />
					<span class="description">Label of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="trpt_slug">Slug</label></th>
				<td>
					<input id="trpt_slug" name="trpt_slug" type="text" class="regular-text" value="" /><br />
					<span class="description">Slug of the <?php echo $this->_sSubject; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="trpt_type_id">Type</label></th>
				<td>
					<select name="trpt_type_id" id="trpt_type_id">
						<option value="">- Select -</option>
						<?php echo $aTypes->implode( '<option value="##Value##">##Title##</option>' ); ?>
					</select><br />
					<span class="description">Specify a mail transport option.</span>
				</td>			
			</tr>
			<?php if ( $aEmsgStrg->count() > 0 ): ?>
				<tr>
					<th><label for="trpt_strg_id">Storage</label></th>
					<td>
						<select id="trpt_strg_id" name="trpt_strg_id">
							<option value="">- Select -</option>
							<?php echo $aEmsgStrg->implode( array( '<option value="##Id##">##Title##</option>', '' ) ); ?>
						</select><br />
						<span class="description">Corresponding storage account for transport.</span>
					</td>
				</tr>
			<?php endif; ?>			
			<tr class="cond smtp">
				<th><label for="trpt_server">Server</label></th>
				<td><input id="trpt_server" name="trpt_server" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond smtp">
				<th><label for="trpt_security">SSL</label></th>
				<td><input id="trpt_security" name="trpt_security" type="text" class="regular-text short" value="" /></td>
			</tr>
			<tr class="cond smtp">
				<th><label for="trpt_port">Port</label></th>
				<td><input id="trpt_port" name="trpt_port" type="text" class="regular-text short" value="" /></td>
			</tr>
			<tr class="cond smtp">
				<th><label for="trpt_auth">Auth</label></th>
				<td><input id="trpt_auth" name="trpt_auth" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond smtp">
				<th><label for="trpt_username">Username</label></th>
				<td><input id="trpt_username" name="trpt_username" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond smtp">
				<th><label for="trpt_password">Password</label></th>
				<td><input id="trpt_password" name="trpt_password" type="text" class="regular-text" value="" /></td>
			</tr>
			<tr class="cond sendmail">
				<th><label for="trpt_params">Params</label></th>
				<td><textarea cols="30" rows="5" id="trpt_params" name="trpt_params" /></td>
			</tr>
			<tr>
				<th><label for="trpt_notes">Notes</label></th>
				<td><textarea cols="30" rows="5" id="trpt_notes" name="trpt_notes" /></td>
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


