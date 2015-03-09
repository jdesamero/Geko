<?php

// should be final
class Geko_Wp_Role_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;
	
	protected $_sEntityIdVarName = 'role_id';
	
	protected $_sSubject = 'Role';
	protected $_sIconId = 'icon-users';
	protected $_sType = 'role';
	protected $_sSubAction = 'user';
	
	protected $_iEntitiesPerPage = 20;
	protected $_bShowTotalItems = FALSE;
	
	private $bSyncWithDb = TRUE;
	private $iLastInsertId = NULL;
	
	
	
	//// init
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_roles', 'r' )
			->fieldSmallInt( 'role_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'title', array( 'size' => 256 ) )
			->fieldVarChar( 'slug', array( 'size' => 256, 'unq' ) )
			->fieldVarChar( 'type', array( 'size' => 64 ) )
			->fieldLongText( 'description' )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		$oDb = Geko_Wp::get( 'db' );
		
		add_action( $oDb->replacePrefixPlaceholder( 'update_option_##pfx##user_roles' ), array( $this, 'updateRole' ), 10, 2 );
		
		
		return $this;
	}
	
	//
	public function afterCalledInit() {
		
		parent::afterCalledInit();
		
		// IMPORTANT!!!: This is called here to prevent a nasty infinite loop
		// reconcile assigned for the types that need it
		Geko_Wp_Role_Types::getInstance()->reconcileAssigned();
		
	}
	
	
	// create table
	public function install() {
		
		parent::install();
		
		if ( $this->createTableOnce() ) {
			
			// initial database sync with roles array
			global $wp_roles;
			
			$aRoleNames = $wp_roles->get_names();
			$this->insertRolesIntoDb( $aRoleNames );
		}
		
		return $this;
	}
	
	//
	public function attachPage() {
		$this->initEntities();
		add_submenu_page( 'users.php', $this->_sSubjectPlural, $this->_sSubjectPlural, $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'displayPage' ) );
	}
	
	
	
	
	
	
	//// error message handling
	
	//
	protected function getErrorMsgs() {
		return array_merge(
			parent::getErrorMsgs(),
			array(
				'm301' => 'An admin account can not be deleted.',
				'm302' => 'The slug for the Administrator role cannot be changed.',
				'm303' => 'There are still items assigned to the role. Operation cannot be completed.'
			)
		);
	}
	
	
	
	
	
	//// front-end display methods
	
	//
	public function outputStyles() {
		?>
		<style type="text/css">
			
			.form-field input.checkbox,
			.form-field input.radio {
				width: 20px;
			}
			
			label.side {
				display: inline;
			}
			
			.inherit-toggle label {
				font-style: italic;
			}
			
			#wpcontent select.multi {
				height: 6em;
			}
			
		</style>
		<?php
	}
	
	//
	public function listingPage() {
		
		$this->runIntegrityCheck();		// TO DO: costly operation, see if there is better way to invoke
		
		$aParams = array(
			'paged' => $this->getPageNum(),
			'posts_per_page' => $this->_iEntitiesPerPage
		);
				
		$oUrl = new Geko_Uri();
		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
			->unsetVar( 'pagenum' )
		;
		
		$sThisUrl = strval( $oUrl );
		
		$sAction = $this->_sAddAction;
		
		$sQueryClass = $this->_sQueryClass;
		$aEntities = new $sQueryClass( $aParams );
		
		$iTotalRows = $aEntities->getTotalRows();
		$sPaginateLinks = $this->getPaginationLinks( $iTotalRows );
		
		?>
		<div class="wrap nosubsub">
			
			<?php $this->outputHeading(); ?>
			
			<div id="col-container">
				
				<div id="col-right">
					<div class="col-wrap">
						
						<?php do_action( 'admin_geko_roles_pre_listing_form' ); ?>
						
						<form id="posts-filter" action="" method="get">
							
							<input type="hidden" name="taxonomy" value="post_tag" />
							
							<div class="tablenav">
								<?php echo Geko_String::sw( '<div class="tablenav-pages">%s</div>', $sPaginateLinks ); ?>
								<br class="clear"/>
							</div>
							
							<div class="clear"></div>
							
							<table class="widefat tag fixed" cellspacing="0">
								<thead>
									<tr>
										<th scope="col" class="manage-column column-name">Name</th>
										<th scope="col" class="manage-column column-slug">Slug</th>
										<th scope="col" class="manage-column column-type">Type</th>
										<th scope="col" class="manage-column column-description">Description</th>
										<th scope="col" class="manage-column column-assigned num">Assigned</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th scope="col" class="manage-column column-name" style="">Name</th>
										<th scope="col" class="manage-column column-slug" style="">Slug</th>
										<th scope="col" class="manage-column column-type" style="">Type</th>
										<th scope="col" class="manage-column column-description" style="">Description</th>
										<th scope="col" class="manage-column column-assigned num">Assigned</th>
									</tr>
								</tfoot>
								<tbody id="the-list" class="list:tag">
									<?php
									
									foreach ( $aEntities as $i => $oEntity ):
										
										$sOddEvenClass = ( $i % 2 ) ? '' : ' alternate';
										
										$oUrl
											->setVar( $this->_sEntityIdVarName, $oEntity->getId() )
											->unsetVar( 'action' )
										;
										$sEditLink = strval( $oUrl );
										
										$oUrl->setVar( 'action', $this->_sDelAction );
										$sDeleteLink = strval( $oUrl );
										
										if ( function_exists( 'wp_nonce_url' ) ) {
											$sDeleteLink = wp_nonce_url( $sDeleteLink, sprintf( '%s%s', $this->_sInstanceClass, $this->_sDelAction ) );
											$sDeleteLink .= sprintf( '&_wp_http_referer=%s', urlencode( $sThisUrl ) );
										}
										
										?>
										<tr id="role-<?php $oEntity->echoId(); ?>" class="iedit<?php echo $sOddEvenClass; ?>">
											<td class="name column-name">
												<strong><a class="row-title" href="<?php echo $sEditLink; ?>"><?php echo stripslashes( $oEntity->getTitle() ); ?></a></strong><br />
												<div class="row-actions">
													<span class="edit"><a href="<?php echo $sEditLink; ?>">Edit</a></span>
													<?php if ( !$oEntity->getAssignedCount() && ( FALSE === strpos( $oEntity->getSlug(), 'admin' ) ) ): ?>
														<span class="delete"> | <a class="delete:the-list:role-<?php $oEntity->echoId(); ?> submitdelete" href="<?php echo $sDeleteLink; ?>">Delete</a></span>
													<?php endif; ?>
												</div>
											</td>
											<td class="slug column-slug"><?php $oEntity->echoSlug(); ?></td>
											<td class="type column-type"><?php $oEntity->echoType(); ?></td>
											<td class="description column-description"><?php echo stripslashes( $oEntity->getDescription() ); ?></td>
											<td class="assigned column-assigned num"><?php $oEntity->echoAssignedCountLink(); ?></td>
										</tr><?php
									endforeach; ?>
								</tbody>
							</table>
							
							<div class="tablenav">
								<?php echo Geko_String::sw( '<div class="tablenav-pages">%s</div>', $sPaginateLinks ); ?>
								<br class="clear"/>
							</div>
							
							<br class="clear" />
						
						</form>
					</div>
				</div><!-- /col-right -->
				
				<div id="col-left">
					<div class="col-wrap">
						<div class="form-wrap">
							
							<h3>Add a New Role</h3>
							<div id="ajax-response"></div>
							
							<?php do_action( 'admin_geko_roles_pre_form_add_fields' ); ?>
							
							<form name="<?php echo $sAction; ?>" id="<?php echo $sAction; ?>" method="post" action="<?php echo $sThisUrl; ?>" class="add:the-list: validate">
								
								<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( sprintf( '%s%s', $this->_sInstanceClass, $sAction ) ); ?>
								
								<input type="hidden" name="action" value="<?php echo $sAction; ?>" />
								
								<?php
									echo $this->setupFields( 'add' );
									do_action( 'admin_geko_roles_add_fields' );
								?>
								
								<p class="submit"><input type="submit" class="button" name="submit" value="Add Role" /></p>
							
							</form>
						
						</div>
					</div>
				</div><!-- /col-left -->
			</div><!-- /col-container -->
		</div><!-- /wrap -->		
		<?php

		/* /
		global $wp_roles, $wpdb;
		echo '<pre>';
		
		// print_r( $wp_roles->get_names() );
		// print '<br /><br />';
		// $aEntities = get_option( $wpdb->prefix . 'user_roles' );
		// print_r( $aEntities );
		
		print_r( $wp_roles->get_role('king-furdi') );
		
		echo '</pre>';
		/* */
		
	}
	
	
	//
	public function detailsPage( $oEntity ) {
		
		$oUrl = new Geko_Uri();

		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		$sAction = $this->_sEditAction;
		$iEntityId = $oEntity->getId();
		
		?>
		<div class="wrap">
			
			<?php $this->outputHeading(); ?>
			
			<?php do_action( 'admin_geko_roles_pre_form_edit_fields' ); ?>
			
			<form name="<?php echo $sAction; ?>" id="<?php echo $sAction; ?>" method="post" action="<?php echo $oUrl; ?>" class="validate">
				
				<?php if ( function_exists( 'wp_nonce_field' ) ) wp_nonce_field( sprintf( '%s%s', $this->_sInstanceClass, $sAction ) ); ?>
				
				<input type="hidden" name="action" value="<?php echo $sAction; ?>">
				<?php echo Geko_String::sw( '<input type="hidden" name="%s$1" value="%d$0">', $iEntityId, $this->_sEntityIdVarName ); ?>
				
				<table class="form-table">
					<?php
						echo $this->setupFields( 'edit' );
						do_action( 'admin_geko_roles_edit_fields', $oEntity );
					?>
				</table>
				
				<p class="submit"><input type="submit" class="button-primary" name="submit" value="Update Role"></p>
			
			</form>
		</div>
		<?php

		/* /
		global $wp_roles, $wpdb;
		
		// $wp_roles->add_cap( 'administrator', 'blow_away' );
		
		echo '<pre>';
		//print_r( $wp_roles->get_names() );
		//print '<br /><br />';
		
		//$aEntities = get_option( $wpdb->prefix . 'user_roles' );
		//print_r( $aEntities );
		
		print_r( $wp_roles->get_role( $oEntity->getSlug() ) );
		
		echo '</pre>';
		/* */
		
	}
	
	
		
	//
	public function formFields() {
		?>
		<p>
			<label class="main">Role name</label> 
			<input name="role_title" id="role_title" type="text" value="" size="40" aria-required="true" />
			<label class="description">The name is how the role appears on your site.</label>
		</p>
		<p>
			<label class="main">Role slug</label> 
			<input name="role_slug" id="role_slug" type="text" value="" size="40" />
			<label class="description">The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</label>
		</p>
		<p>
			<label class="main">Role type</label> 
			<select name="role_type" id="role_type">
				<?php $this->roleTypeOptions(); ?>
			</select>
			<label class="description">The type of role to be created.</label>
		</p>			
		<p>
			<label class="main">Description</label> 
			<textarea name="role_description" id="role_description" rows="5" cols="40"></textarea>
			<label class="description">The description is not prominent by default, however some themes may show it.</label>
		</p>
		<?php
	}
	
	
	
	//// form processing/injection methods

	// plug into the add category form
	public function setupFields( $sMode = 'add' ) {
		
		$aParts = $this->extractParts();
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			
			$sLabel = Geko_String::sw( '<label for="%s$1">%s$0</label>', $aPart[ 'label' ], $aPart[ 'name' ] );
			$sFieldGroup = Geko_String::sw( '%s<br />', $aPart[ 'field_group' ] );
			
			if ( 'edit' == $sMode ) {
				
				$sFields .= sprintf(
					'<tr class="form-field">
						<th scope="row" valign="top">%s</th>
						<td>%s%s</td>
					</tr>',
					$sLabel,
					$sFieldGroup,
					Geko_String::sw( '<span class="description">%s</span>', $aPart[ 'description' ] )
				);
				
			} else {
				
				$sFields .= sprintf(
					'<div class="form-field">%s%s%s</div>',
					$sLabel,
					$sFieldGroup,
					Geko_String::sw( '<p>%s</p>', $aPart[ 'description' ] )
				);
			}
		}
		
		return $sFields;
	}
	
	//
	public function extractPart( $aPart, $oPq ) {
		
		if (
			( $oEntity = $this->_oCurrentEntity ) &&
			( 'role_type' == $aPart[ 'name' ] )
		) {
			if ( $oEntity->getAssignedCount() ) {
				
				// disallow changing types if role has stuff assigned to it
				$aPart[ 'field_group' ] = str_replace(
					'id="role_type"',
					'id="role_type" disabled="disabled"',
					$aPart['field_group']
				);
				
				$aPart[ 'field_group' ] .= sprintf( '<input type="hidden" name="role_type" value="%s" />', $oEntity->getType() );
			}
		}
		
		return $aPart;
	}
	
	
	
	//// crud methods	
	
	//
	public function doAddAction( $aParams ) {
		
		global $wp_roles;
		
		$bContinue = TRUE;
		$sName = stripslashes( $_POST[ 'role_title' ] );
		$sSlug = ( $_POST[ 'role_slug' ] ) ? $_POST[ 'role_slug' ] : $sName;
		$sType = $_POST[ 'role_type' ];
		
		//// do checks
		
		// check title
		if ( $bContinue && !$sName ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );							// empty title was given
		}
		
		//// do operation !!!
		
		if ( $bContinue ) {
			$sSlug = Geko_Wp_Db::generateSlug( $sSlug, 'geko_roles', 'slug' );
			$wp_roles->add_role( $sSlug, $this->roleJoin( $sName, $sType ) );
			
			$sEntityClass = $this->_sEntityClass;
			$oInsertedRole = new $sEntityClass( $this->iLastInsertId );
			do_action( 'admin_geko_roles_add', $oInsertedRole );
			
			$this->triggerNotifyMsg( 'm101' );							// success!!!
		}
		
		return $aParams;
	}
	
	//
	public function doEditAction( $aParams ) {
		
		global $wp_roles;
		
		$oDb = Geko_Wp::get( 'db' );
		
		$bContinue = TRUE;
		$sName = stripslashes( $_POST[ 'role_title' ] );
		$sSlug = ( $_POST[ 'role_slug' ] ) ? $_POST[ 'role_slug' ] : $sName;
		$sType = $_POST[ 'role_type' ];		// allow updates if none are assigned to it
		$sDesc = stripslashes( $_POST[ 'role_description' ] );
		
		//// do checks
		
		// check title
		if ( $bContinue && !$sName ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );							// empty title was given
		}
		
		// check the role id given
		$iEntityId = intval( $aParams[ 'entity_id' ] );
		
		$sEntityClass = $this->_sEntityClass;			
		$oEntity = new $sEntityClass( $iEntityId );
		
		if ( $bContinue && !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );							// bad role id given
		}

		if ( $bContinue && ( $oEntity->getSlug() == 'administrator' ) && ( $sSlug != 'administrator' ) ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm302' );							// attempting to change the admin slug
		}
		
		// if role type is not empty, ensure a role type is defined
		if ( $bContinue && $oEntity->getType() && !$oEntity->hasRoleTypeObject() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm203' );							// there's no role type, so we can't confirm that there's nothing assigned to the role	
		}
		
		
		//// do operation !!!
		
		if ( $bContinue ) {
			
			$sCurSlug = $oEntity->getSlug();
			$sCurType = $oEntity->getType();
			
			$bUpdateType = FALSE;
			$bTypeChanged = FALSE;
			
			//// see what to do with type
			
			// see if attempting to change type
			if ( ( $sCurType ) && ( $sCurType != $sType ) && ( !$oEntity->getAssignedCount() ) ) {
				$bUpdateType = TRUE;		// allow type update
			}
			
			//
			if ( $sCurSlug != $sSlug ) {
				// slug was changed, ensure it's unique
				$sSlug = Geko_Wp_Db::generateSlug( $sSlug, 'geko_roles', 'slug' );
			}

			$aUpdateValues = array( 'title' => $sName, 'slug' => $sSlug, 'description' => $sDesc );
			
			// if $bUpdateType, type change is allowed
			// if !$sCurType, might as well assign it
			if ( $bUpdateType || !$sCurType ) {
				$aUpdateValues[ 'type' ] = $sType;
				$bTypeChanged = TRUE;
			}
			
			// update the database first
			$oDb->update(
				'##pfx##geko_roles',
				$aUpdateValues,
				array( 'role_id = ?' => $iEntityId )
			);
			
			//// simulate $wp_roles->update_role( ... )
			
			$this->bSyncWithDb = FALSE;
			$oWpRole = $wp_roles->get_role( $sCurSlug );
			$wp_roles->remove_role( $sCurSlug );
			$wp_roles->add_role( $sSlug, $this->roleJoin( $sName, $sType ), $oWpRole->capabilities );
			$this->bSyncWithDb = TRUE;

			// only trigger reconcialition if the type was not changed, and
			// if there's something assigned to the role
			
			$oUpdatedRole = new $sEntityClass( $iEntityId );
			
			if ( !$bUpdateType && $oEntity->getAssignedCount() ) {
				$oEntity->getRoleTypeObject()->reconcileRoleOnUpdate(
					$oEntity,					// old role
					$oUpdatedRole			// new role
				);
			}
			
			do_action( 'admin_geko_roles_edit', $oEntity, $oUpdatedRole );
			$this->triggerNotifyMsg( 'm102' );							// success!!!
			
		}
	
		return $aParams;
	}
	
	//
	public function doDelAction( $aParams ) {
		
		global $wp_roles;
		
		$bContinue = TRUE;
		
		$iEntityId = intval( $aParams[ 'entity_id' ] );
		
		$sEntityClass = $this->_sEntityClass;
		$oEntity = new $sEntityClass( $iEntityId );
		
		if ( !$oEntity->isValid() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm202' );							// bad role id given
		}

		if ( $bContinue && ( FALSE !== strpos( $oEntity->getSlug(), 'admin' ) ) ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm301' );							// attempting to delete admin
		}
		
		// if role type is not empty, ensure a role type is defined
		if ( $bContinue && $oEntity->getType() && !$oEntity->hasRoleTypeObject() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm203' );							// there's no role type, so we can't confirm that there's nothing assigned to the role
		}
		
		// check if there's anything assigned to the role
		if ( $bContinue && $oEntity->getAssignedCount() ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm303' );							// there are items assigned to the role, these should be re-assigned first
		}
		
		
		//// do operation !!!
		
		if ( $bContinue ) {
			$sSlug = $oEntity->getSlug();
			$wp_roles->remove_role( $sSlug );
			do_action( 'admin_geko_roles_delete', $oEntity );
			$this->triggerNotifyMsg( 'm103' );							// success!!!
		}
		
		return $aParams;
	}
	
	
	
	
	
	
	
	
	//
	public function updateRole( $mOldValue, $mNewValue ) {
		
		if ( $this->bSyncWithDb ) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$aBacktrace = debug_backtrace();
			$sFunc = $aBacktrace[ 4 ][ 'function' ];	// add_role or remove_role
			$aArgs = $aBacktrace[ 4 ][ 'args' ];
			
			$sSlug = $aArgs[ 0 ];
			
			// sync with database
			if ( 'add_role' == $sFunc ) {
				
				list( $sName, $sType ) = $this->roleSplit( $aArgs[ 1 ] );
				
				$aInsertValues = array( 'title' => $sName, 'slug' => $sSlug, 'type' => $sType );
				
				if (
					( $this->_sAddAction == $_POST[ 'action' ] ) &&
					( check_admin_referer( sprintf( '%s%s', $this->_sInstanceClass, $this->_sAddAction ) ) )
				) {
					// insert description as well
					$aInsertValues[ 'description' ] = stripslashes( $_POST[ 'role_description' ] );
				}
				
				$oDb->insert( '##pfx##geko_roles', $aInsertValues );
				
				$this->iLastInsertId = $oDb->lastInsertId();
				
			} elseif ( 'remove_role' == $sFunc ) {
				
				$oDb->delete( '##pfx##geko_roles', array(
					'slug = ?' => $sSlug
				) );
			}
		
		}
		
	}
	
	//
	protected function insertRolesIntoDb( $aRoleNames ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		foreach ( $aRoleNames as $sSlug => $sRole ) {
			
			if ( $sSlug ) {
				
				list( $sName, $sType ) = $this->roleSplit( $sRole );
				
				$oDb->insert( '##pfx##geko_roles', array(
					'title' => $sName,
					'slug' => $sSlug,
					'type' => $sType
				) );
			}
		}
	
	}
	
	// perform an integrity check on what's in $wp_roles and what we store in the DB
	protected function runIntegrityCheck() {
		
		global $wp_roles;
		
		$oDb = Geko_Wp::get( 'db' );
		
		$sQueryClass = $this->_sQueryClass;
		$aEntities = new $sQueryClass();
		
		$aDbRoles = array();
		foreach ( $aEntities as $oEntity ) {
			$aDbRoles[ $oEntity->getSlug() ] = $this->roleJoin( $oEntity->getTitle(), $oEntity->getType() );
		}
		
		$aWpRoles = $wp_roles->get_names();
		
		//// when doing the update, $wp_roles always takes precedence
		
		foreach ( $aWpRoles as $sSlug => $sName ) {
			if ( isset( $aDbRoles[ $sSlug ] ) ) {
				
				// compare the names
				if ( $sName != $aDbRoles[ $sSlug ] ) {
					
					list( $sUpdateTitle, $sUpdateType ) = $this->roleSplit( $sName );
					
					$oDb->update(
						'##pfx##geko_roles',
						array( 'title' => $sUpdateTitle, 'type' => $sUpdateType ),
						array( 'slug = ?' => $sSlug )
					);
				}
				
				unset( $aDbRoles[ $sSlug ] );
				unset( $aWpRoles[ $sSlug ] );
			}
		}
		
		// insert missing roles into the database
		$this->insertRolesIntoDb( $aWpRoles );
		
		// remove "phantom" roles from the database
		foreach ( $aDbRoles as $sSlug => $sRole ) {
			
			$oDb->delete( '##pfx##geko_roles', array(
				'slug = ?' => $sSlug
			) );
		}
		
	}
	
	
	
	
	//// helpers
	
	// generate option tags for the select
	protected function roleTypeOptions( $oEntity = NULL ) {
		$sType = ( $oEntity ) ? $oEntity->getType() : '';
		foreach ( Geko_Wp_Role_Types::getInstance() as $oRoleType ):
			$sSelected = ( ( $sType ) && ( $sType == $oRoleType->getName() ) ) ? ' selected="selected" ' : '' ;
			?><option value="<?php echo $oRoleType->getName(); ?>" <?php echo $sSelected; ?> ><?php echo $oRoleType->getName(); ?></option><?php
		endforeach;
	}
	
	//
	protected function roleJoin( $sName, $sType ) {
		if ( 'User role' == $sType ) {
			return $sName;
		} else {
			return sprintf( '%s|%s', $sName, $sType );
		}
	}
	
	//
	protected function roleSplit( $sRole ) {
		$aPair = explode( '|', $sRole );
		if ( !$aPair[ 1 ] ) $aPair[ 1 ] = 'User role';
		return $aPair;
	}
	
	
}


