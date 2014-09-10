<?php

//
class Geko_Wp_Group_Member_Manage extends Geko_Wp_Options_Manage
{
	const STAT_PEN = 'pending';
	const STAT_ACT = 'active';
	const STAT_INA = 'inactive';
	const STAT_BAN = 'banned';
	
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing
	
	protected $_sSubject = 'Members';
	protected $_sDescription = 'Wordpress users associated with a group.';
	protected $_sType = 'grp-mem';
	protected $_sIconId = 'icon-users';
	
	protected $_bHasDisplayMode = FALSE;
	
	protected $_mDefaultGroup = NULL;
	protected $_sUserEntityClass = 'Geko_Wp_User';
	protected $_sGroupEntityClass = 'Geko_Wp_Group';
	
	
	
	
	//
	public function add() {
		
		parent::add();
		
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_group_members', 'gm' )
			->fieldBigInt( 'group_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldBigInt( 'user_id', array( 'unsgnd', 'key' ) )
			->fieldSmallInt( 'status_id', array( 'unsgnd' ) )
			->fieldDateTime( 'date_requested' )
			->fieldDateTime( 'date_joined' )
			->indexUnq( 'group_user_id', array( 'group_id', 'user_id' ) )
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
	public function setDefaultGroup( $mGroup ) {
		$this->_mDefaultGroup = $mGroup;
		return $this;
	}
	
	
	
	//// front-end display methods
	
	//
	public function addAdmin() {
		parent::addAdmin();
		add_action( 'admin_menu', array( $this, 'attachPage' ) );
	}
	
	//
	public function attachPage() {
		
		// parent::attachPage();
		
		Geko_Debug::log( sprintf( '%s - %s', $this->_sSubOptionParentClass, $this->_sManagementCapability ), __METHOD__ );
		
		add_submenu_page( $this->_sSubOptionParentClass, 'Pending Membership Requests', 'Pending', $this->_sManagementCapability, sprintf( '%s_Pending', $this->_sInstanceClass ), array( $this, 'pendingPage' ) );
		
		$iEntityId = Geko_String::coalesce( $_GET[ 'parent_entity_id' ], $_GET[ 'group_id' ] );
		
		$sUrl = sprintf( '%s?page=%s&%s=%d', Geko_Uri::getUrl( 'wp_admin' ), $this->_sInstanceClass, $this->_sParentEntityIdVarName, $iEntityId );
		Geko_Wp_Admin_Menu::addMenu( $this->_sSubOptionParentClass, 'Members', $sUrl );
		add_submenu_page( $this->_sSubOptionParentClass, '', '', $this->_sManagementCapability, $this->_sInstanceClass, array( $this, 'listingPage' ) );
	}
	
	//
	public function pendingPage() {
		
		$aParams = array(
			'status' => Geko_Wp_Group_Member_Manage::STAT_PEN,
			'orderby' => 'date_requested',
			'order' => 'ASC'
		);
		
		$aEntities = new $this->_sQueryClass( $aParams );
		
		//// start url stuff
		
		$oUrl = new Geko_Uri();
		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		$sThisUrl = strval( $oUrl );

		$oUrl
			->setVar( 'page', $this->_sInstanceClass )
			->setVar( 'action', $this->_sEditAction )
			->setVar( $this->_sEntityIdVarName, 1 )			// HACK!!!
		;
		
		//// end url stuff
		
		?>
		<div class="wrap">
			
			<div id="<?php echo $this->_sIconId; ?>" class="icon32"><br /></div>		
			<h2>Pending Membership Requests</h2>
			
			<?php $this->notificationMessages(); ?>
			
			<form id="geko-groups-filter" method="get" action="">
				
				<div class="tablenav"><br class="clear"/></div>
				
				<table class="widefat fixed" cellspacing="0">
				
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-user">User</th>
							<th scope="col" class="manage-column column-group">Group</th>
							<th scope="col" class="manage-column column-action">Action</th>
							<th scope="col" class="manage-column column-date">Date Requested</th>
						</tr>
					</thead>
					
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-user">User</th>
							<th scope="col" class="manage-column column-group">Group</th>
							<th scope="col" class="manage-column column-action">Action</th>
							<th scope="col" class="manage-column column-date">Date Requested</th>
						</tr>
					</tfoot>
					
					<?php
					
					foreach ( $aEntities as $oEntity ):
						
						$oUrl
							->setVar( 'group_id', $oEntity->getGroupId() )
							->setVar( 'user_id', $oEntity->getUserId() )
						;
						
						if ( function_exists('wp_nonce_url') ) {
							
							$oUrl->setVar( 'status', self::STAT_ACT );
							$sApproveLink = wp_nonce_url( strval( $oUrl ),  $this->_sInstanceClass . $this->_sEditAction );
							$sApproveLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
							
							$oUrl->setVar( 'status', self::STAT_BAN );							
							$sDenyLink = wp_nonce_url( strval( $oUrl ),  $this->_sInstanceClass . $this->_sEditAction );
							$sDenyLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
						}
						
						?><tbody>
							<tr id="group-<?php $oEntity->echoId(); ?>" class='alternate author-self status-publish iedit' valign="top">
								<th scope="row" class="check-column"><input type="checkbox" name="group[]" value="<?php $oEntity->echoId(); ?>" /></th>
								<td class="group-title column-title"><strong><a class="row-title" href="<?php $oEntity->echoUserEditUrl(); ?>"><?php $oEntity->echoUserFullName(); ?></a></strong></td>
								<td class="group-title column-title"><strong><a class="row-title" href="<?php $oEntity->echoGroupEditUrl(); ?>"><?php $oEntity->echoGroupTitle(); ?></a></strong></td>
								<td class="group-title column-title">
									<a href="<?php echo $sApproveLink; ?>">Approve</a> | 
									<a href="<?php echo $sDenyLink; ?>">Deny</a>
								</td>
								<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeRequested('Y/m/d g:i A'); ?>"><?php $oEntity->echoDateTimeRequested('Y/m/d'); ?></abbr></td>
							</tr>
						</tbody><?php
					endforeach;
					
					?>
					
				</table>
				
			</form>
						
		</div>

		<?php
	}
	
	//
	public function listingPage() {
		
		$iEntityId = $_GET['parent_entity_id'];
		
		$aParams = array(
			'group_id' => $iEntityId,
			'orderby' => 'date_joined',
			'order' => 'DESC'
		);
		
		$aEntities = new $this->_sQueryClass( $aParams );
		
		//// start url stuff
		
		$oUrl = new Geko_Uri();
		$oUrl
			->unsetVar( 'action' )
			->unsetVar( $this->_sEntityIdVarName )
		;
		
		$sThisUrl = strval( $oUrl );

		$oUrl
			->setVar( 'page', $this->_sInstanceClass )
			->setVar( $this->_sEntityIdVarName, 1 )			// HACK!!!
		;
		
		//// end url stuff
		
		$aUserParams = array( 'exclude' => $aEntities->gather('##UserId##') );
		$aUsers = new Geko_Wp_User_Query( $aUserParams, FALSE );
		
		?>
		<div class="wrap">
			
			<div id="<?php echo $this->_sIconId; ?>" class="icon32"><br /></div>		
			<h2>Members</h2>
			
			<?php
				$this->notificationMessages();
				Geko_Wp_Admin_Menu::showMenu( $this->_sSubOptionParentClass );
				
				$oGroup = new $this->_sGroupEntityClass( $iEntityId );
			?>
			
			<h3><?php $oGroup->echoTitle(); ?></h3>
			
			<form id="geko-groups-filter" method="get" action="">
				
				<div class="tablenav"><br class="clear"/></div>
				
				<table class="widefat fixed" cellspacing="0">
				
					<thead>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-user">User</th>
							<th scope="col" class="manage-column column-group">Status</th>
							<th scope="col" class="manage-column column-action">Set Status</th>
							<th scope="col" class="manage-column column-date">Date Requested</th>
							<th scope="col" class="manage-column column-date">Date Joined</th>
						</tr>
					</thead>
					
					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th>
							<th scope="col" class="manage-column column-user">User</th>
							<th scope="col" class="manage-column column-group">Status</th>
							<th scope="col" class="manage-column column-action">Set Status</th>
							<th scope="col" class="manage-column column-date">Date Requested</th>
							<th scope="col" class="manage-column column-date">Date Joined</th>
						</tr>
					</tfoot>
					
					<?php
					
					foreach ( $aEntities as $oEntity ):
						
						$oUrl
							->setVar( 'group_id', $oEntity->getGroupId() )
							->setVar( 'user_id', $oEntity->getUserId() )
						;
						
						if ( function_exists('wp_nonce_url') ) {
							
							$oUrl
								->setVar( 'action', $this->_sEditAction )
								->setVar( 'status', self::STAT_PEN )
							;
							
							$sPendingLink = wp_nonce_url( strval( $oUrl ),  $this->_sInstanceClass . $this->_sEditAction );
							$sPendingLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
							
							$oUrl->setVar( 'status', self::STAT_ACT );							
							$sActiveLink = wp_nonce_url( strval( $oUrl ),  $this->_sInstanceClass . $this->_sEditAction );
							$sActiveLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
							
							$oUrl->setVar( 'status', self::STAT_INA );							
							$sInactiveLink = wp_nonce_url( strval( $oUrl ),  $this->_sInstanceClass . $this->_sEditAction );
							$sInactiveLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
							
							$oUrl->setVar( 'status', self::STAT_BAN );							
							$sBannedLink = wp_nonce_url( strval( $oUrl ),  $this->_sInstanceClass . $this->_sEditAction );
							$sBannedLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
							
							$oUrl
								->setVar( 'action', $this->_sDelAction )
								->unsetVar( 'status' )
							;
							
							$sDeleteLink = wp_nonce_url( strval( $oUrl ),  $this->_sInstanceClass . $this->_sDelAction );
							$sDeleteLink .= '&_wp_http_referer=' . urlencode( $sThisUrl );
						}
						
						?><tbody>
							<tr id="group-<?php $oEntity->echoId(); ?>" class='alternate author-self status-publish iedit' valign="top">
								<th scope="row" class="check-column"><input type="checkbox" name="group[]" value="<?php $oEntity->echoId(); ?>" /></th>
								<td class="group-title column-title">
									<strong><a class="row-title" href="<?php $oEntity->echoUserEditUrl(); ?>"><?php $oEntity->echoUserFullName(); ?></a></strong>
									<div class="row-actions">
										<span class="delete"><a class="delete:the-list:group-<?php $oEntity->echoId(); ?> submitdelete" href="<?php echo $sDeleteLink; ?>">Delete</a></span>
									</div>
								</td>
								<td class="group-title column-title"><?php $oEntity->echoStatus(); ?></td>
								<td class="group-title column-title">
									<a href="<?php echo $sPendingLink; ?>">Pending</a> | 
									<a href="<?php echo $sActiveLink; ?>">Active</a> | 
									<a href="<?php echo $sInactiveLink; ?>">Inactive</a> | 
									<a href="<?php echo $sBannedLink; ?>">Banned</a>
								</td>
								<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeRequested('Y/m/d g:i A'); ?>"><?php $oEntity->echoDateTimeRequested('Y/m/d'); ?></abbr></td>
								<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeJoined('Y/m/d g:i A'); ?>"><?php $oEntity->echoDateTimeJoined('Y/m/d'); ?></abbr></td>
							</tr>
						</tbody><?php
					endforeach;
					
					?>
					
				</table>
				
			</form>
			
			<h3>Add Member</h3>
			
			<form id="editform" method="post">
				
				<table class="form-table">
					<tr>
						<th><label for="user_id">User</label></th>
						<td>
							<select id="user_id" name="user_id">
								<?php echo $aUsers->implode( '<option value="##Id##">##FullName##</option>' ); ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="status">Status</label></th>
						<td>
							<select id="status" name="status">
								<option value="<?php echo self::STAT_PEN; ?>"><?php echo ucfirst( self::STAT_PEN ); ?></option>
								<option value="<?php echo self::STAT_ACT; ?>"><?php echo ucfirst( self::STAT_ACT ); ?></option>
								<option value="<?php echo self::STAT_INA; ?>"><?php echo ucfirst( self::STAT_INA ); ?></option>
								<option value="<?php echo self::STAT_BAN; ?>"><?php echo ucfirst( self::STAT_BAN ); ?></option>
							</select>
						</td>
					</tr>
				</table>
				
				<input type="hidden" name="group_id" value="<?php echo $iEntityId; ?>" />				
				<input type="hidden" name="action" value="<?php echo $this->_sAddAction; ?>" />
				<input type="hidden" name="page" value="<?php echo $this->_sInstanceClass; ?>" />
				<?php if ( function_exists('wp_nonce_field') ) wp_nonce_field( $this->_sInstanceClass . $this->_sAddAction ); ?>
				
				<p class="submit"><input type="submit" class="button-primary" name="submit" value="Add"></p>
				
			</form>
			
		</div>

		<?php
		
	}
	
	
	//// Join/leave group
	
	// Statuses: pending, active, inactive, banned
	
	//
	public function join( $mUser, $sStatus, $mGroup = NULL ) {
		
		if (
			( $iUserId = $this->getUserId( $mUser ) ) && 
			( $iGroupId = $this->getGroupId( $mGroup ) )
		) {
			
			global $wpdb;
			
			// check if entry exists
			$oSql = new Geko_Sql_Select();
			$oSql
				->field( 1, 'test' )
				->from( $wpdb->geko_group_members )
				->where( 'group_id = ?', $iGroupId )
				->where( 'user_id = ?', $iUserId )
			;
			
			$sDateTime = Geko_Db_Mysql::getTimestamp();
			
			$aVals = array();
			$aVals['status_id'] = Geko_Wp_Options_MetaKey::getId( $sStatus );
			if ( self::STAT_ACT == $sStatus ) $aVals['date_joined'] = $sDateTime;
			
			if ( $wpdb->get_var( strval( $oSql ) ) ) {
				
				// update
				$aKeys['group_id'] = $iGroupId;
				$aKeys['user_id'] = $iUserId;
								
				$wpdb->update( $wpdb->geko_group_members, $aVals, $aKeys );
				
			} else {
				
				// insert
				$aVals['group_id'] = $iGroupId;
				$aVals['user_id'] = $iUserId;
				$aVals['date_requested'] = $sDateTime;
				
				$wpdb->insert( $wpdb->geko_group_members, $aVals );
				
			}
			
		}
		
		return $this;
	}
	
	//
	public function joinPending( $mUser, $mGroup = NULL ) {
		return $this->join( $mUser, self::STAT_PEN, $mGroup );
	}
	
	//
	public function joinActive( $mUser, $mGroup = NULL ) {
		return $this->join( $mUser, self::STAT_ACT, $mGroup );
	}
	
	//
	public function joinInactive( $mUser, $mGroup = NULL ) {
		return $this->join( $mUser, self::STAT_INA, $mGroup );
	}

	//
	public function joinBanned( $mUser, $mGroup = NULL ) {
		return $this->join( $mUser, self::STAT_BAN, $mGroup );
	}
	
	//
	public function leave( $mUser, $mGroup = NULL ) {

		if (
			( $iUserId = $this->getUserId( $mUser ) ) && 
			( $iGroupId = $this->getGroupId( $mGroup ) )
		) {
			global $wpdb;
			
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM $wpdb->geko_group_members WHERE group_id = %d AND user_id = %d",
				$iGroupId,
				$iUserId
			) );
		}
		
		return $this;
	}
	
	// helpers
	
	//
	protected function getUserId( $mUser ) {
		
		if ( is_object( $mUser ) ) {
			
			if ( $mUser instanceof Geko_Wp_User ) {
				return $mUser->getId();			
			} else {
				return $mUser->ID;
			}
			
		} elseif ( is_scalar( $mUser ) ) {
			
			if ( preg_match( '/^[0-9]+$/', $mUser ) ) {
				return $mUser;
			} else {
				$oUser = new Geko_Wp_User( $mUser );
				if ( $oUser->isValid() ) return $oUser->getId();			
			}
			
		}
		
		return FALSE;
	}
	
	//
	protected function getGroupId( $mGroup = NULL ) {
		
		if ( ( NULL === $mGroup ) && ( $this->_mDefaultGroup ) ) {
			$mGroup = $this->_mDefaultGroup;
		}
		
		if (
			( is_object( $mGroup ) ) && 
			( $mGroup instanceof Geko_Wp_Group )
		) {
			
			return $mGroup->getId();
		
		} elseif ( is_scalar( $mGroup ) ) {
			
			if ( preg_match( '/^[0-9]+$/', $mGroup ) ) {
				return $mGroup;
			} else {
				$oGroup = new Geko_Wp_Group( $mGroup );
				if ( $oGroup->isValid() ) return $oGroup->getId();			
			}
			
		}
		
		return FALSE;
	}
	
	
	//// crud methods
	
	//
	public function doAddAction( $aParams ) {
		
		$this->join( $_REQUEST['user_id'], $_REQUEST['status'], $_REQUEST['group_id'] );
		
		$this->triggerNotifyMsg( 'm101' );										// success!!!
		
		return $aParams;
	}
	
	
	//
	public function doEditAction( $aParams ) {
		
		$this->join( $_REQUEST['user_id'], $_REQUEST['status'], $_REQUEST['group_id'] );
		
		$this->triggerNotifyMsg( 'm102' );										// success!!!
		
		return $aParams;
	}
	
	
	//
	public function doDelAction( $aParams ) {
		
		$this->leave( $_REQUEST['user_id'], $_REQUEST['group_id'] );
		
		$this->triggerNotifyMsg( 'm103' );
		
		return $aParams;
	}
	
}


