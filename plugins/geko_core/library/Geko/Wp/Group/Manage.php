<?php

//
class Geko_Wp_Group_Manage extends Geko_Wp_Options_Manage
{
	private static $aInstances = array();

	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'group_id';
	
	protected $_sSubject = 'Group';
	protected $_sDescription = 'Groups allow you to associate related items together. Items could be users, posts, or even other groups.';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'group';
	
	
	
	//
	protected function __construct() {
		parent::__construct();
		self::$aInstances[ $this->_sInstanceClass ] = $this;		
	}
	
	
	//// init
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		Geko_Wp_Options_MetaKey::init();
		
		$sTable = 'geko_group';
		Geko_Wp_Db::addPrefix( $sTable );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTable, 'g' )
			->fieldBigInt( 'group_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'title', array( 'size' => 256 ) )
			->fieldVarChar( 'slug', array( 'size' => 256, 'unq' ) )
			->fieldLongText( 'description' )
			->fieldSmallInt( 'role_id', array( 'unsgnd' ) )
			->fieldSmallInt( 'grptype_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'parent_id', array( 'unsgnd', 'key' ) )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
	}
	
	
	// create table
	public function install() {
		
		parent::install();
		
		Geko_Wp_Options_MetaKey::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
	
	
	//// accessors
	
	//
	public function getGroupType() {
		return $this->_sSubjectPlural;
	}
	
	//
	public function getGroupTypeSingular() {
		return $this->_sSubject;
	}
	
	//
	public function getGroupTypeSlug() {
		return $this->_sSlug;
	}
	
	//
	public function getStoredOptions() {
		
		$aRet = parent::getStoredOptions();
		
		$sTypeKey = sprintf( '%s_type', $this->_sType );
		
		if ( !$aRet[ $sTypeKey ] ) {
			$aRet[ $sTypeKey ] = $this->_sSlug;
		}
		
		return $aRet;
	}
	
	
	//// front-end display methods
	
	// hook method
	public function modifyListingParams( $aParams ) {
		
		if ( $iParentId = $this->_iCurrentParentEntityId ) {
			$aParams[ 'parent_id' ] = $iParentId;
		}
		
		if ( $iRoleId = intval( $_GET[ 'role_id' ] ) ) {
			$aParams[ 'geko_role_id' ] = $iRoleId;
		}
		
		return $aParams;
	}
	
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-type">Type</th>
		<th scope="col" class="manage-column column-role">Role</th>
		<th scope="col" class="manage-column column-date-created">Date Created</th>
		<th scope="col" class="manage-column column-date-modified">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-title"><?php $oEntity->echoType(); ?></td>
		<td class="column-title"><?php $oEntity->echoRoleTitle(); ?></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-modified"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	//
	public function outputListingFilterSection() {
		
		if ( $this->isDisplayMode( 'list' ) ) {
			
			$iRoleId = intval( $_GET[ 'role_id' ] );
			
			$sRoleType = sprintf( '%s role', ucfirst( strtolower( ( $this->_sSubject ) ) ) );
			$aRoles = new Geko_Wp_Role_Query( array( 'role_type' => $sRoleType ) );
			
			if ( count( $aRoles ) > 0 ):
				?><div class="filter">
					<ul class="subsubsub">
						<?php foreach ( $aRoles as $i => $oRole ):
							$sDelim = ( $i ) ? '| ' : '' ;
							$sCurrentCssClass = ( $iRoleId == $oRole->getId() ) ? 'class="current"' : '' ;
							?><li><?php echo $sDelim; ?><a <?php echo $sCurrentCssClass; ?> href="<?php $oRole->echoAssignedCountUrl(); ?>"><?php $oRole->echoTitle(); ?> <span class="count">(<?php $oRole->echoAssignedCount(); ?>)</span></a></li>
						<?php endforeach; ?>
					</ul>
				</div><?php
			endif;
		}
	}
	
	
	
	
	//
	public function formFields() {
		
		$sEndings = '';
		
		?>
		<h3><?php echo $this->_sListingTitle; ?> Options</h3>
		<style type="text/css">
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="group_title"><?php echo $this->_sListingTitle; ?> Name</label></th>
				<td>
					<input id="group_title" name="group_title" type="text" class="regular-text" value="" /><br />
					<span class="description">Name of the <?php echo $this->_sListingTitle; ?>.</span>
				</td>
			</tr>
			<tr>
				<th><label for="group_slug"><?php echo $this->_sListingTitle; ?> Slug</label></th>
				<td>
					<input id="group_slug" name="group_slug" type="text" class="regular-text" value="" /><br />
					<span class="description">Slug of the <?php echo $this->_sListingTitle; ?>.</span>
				</td>
			</tr><?php
				
				if ( 'group' == $this->_sSlug ):
					?><tr>
						<th><label for="group_type"><?php echo $this->_sListingTitle; ?> Type</label></th>
						<td>
							<select id="group_type" name="group_type">
								<option value="">Select Type</option>
								<?php foreach ( self::$aInstances as $oGroupType ):
									$sSlug = $oGroupType->getGroupTypeSlug();
									$sTitle = $oGroupType->getGroupTypeSingular();
									if ( 'group' != $sSlug ): ?><option value="<?php echo $sSlug; ?>"><?php echo $sTitle; ?></option><?php endif;
								endforeach; ?>
							</select><br />
							<span class="description">Type of the <?php echo $this->_sListingTitle; ?>.</span>
						</td>
					</tr><?php
				else:
					$sEndings .= '<input type="hidden" name="group_type" id="group_type" value="" />';
				endif;
				
				$sRoleType = ucfirst( strtolower( ( $this->_sSubject ) ) ) . ' role';
				$aRoles = new Geko_Wp_Role_Query( array( 'role_type' => $sRoleType ) );
				
				if ( count( $aRoles ) > 0 ):
					?><tr>
						<th><label for="group_role_id">Role</label></th>
						<td>
							<select id="group_role_id" name="group_role_id">
								<option value="">Select Role</option>
								<?php foreach ( $aRoles as $oRole ): ?>
									<option value="<?php $oRole->echoId(); ?>"><?php $oRole->echoTitle(); ?></option>
								<?php endforeach; ?>
							</select><br />
							<span class="description">The role of the <?php echo $this->_sSubject; ?>.</span>
						</td>
					</tr><?php
				endif;
				
			?><tr>
				<th><label for="group_description"><?php echo $this->_sListingTitle; ?> Description</label></th>
				<td>
					<textarea cols="30" rows="5" id="group_description" name="group_description" /><br />
					<span class="description">Description of the <?php echo $this->_sListingTitle; ?>.</span>
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
		echo $sEndings;
	}
	
	
	//// crud methods
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
		);
		
		$aValues[ 'grptype_id' ] = Geko_Wp_Options_MetaKey::getId( $aValues[ 'type' ] );
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
	
		$sTitle = $aValues[ 'title' ];
		$iGrpTypeId = $aValues[ 'grptype_id' ];
		
		// check title
		if ( $bContinue && !$sTitle ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty title was given
		}
		
		if ( $bContinue && !$iGrpTypeId ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm203' );										// Type must be specified !!!
		}
		
		return $bContinue;
		
	}
	
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
		
		if ( !$aValues[ 'slug' ] ) $aValues[ 'slug' ] = $aValues[ 'title' ];
		if ( $aValues[ 'slug' ] != $oEntity->getSlug() ) {
			$aValues[ 'slug' ] =  Geko_Wp_Db::generateSlug(
				$aValues[ 'slug' ], $this->getPrimaryTable(), 'slug'
			);
		}
		
		$aValues[ 'grptype_id' ] = Geko_Wp_Options_MetaKey::getId( $aValues[ 'type' ] );
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}

	
}

