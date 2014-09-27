<?php

// listing
class Geko_Wp_User_RoleType extends Geko_Wp_Role_Type_Abstract
{
	
	protected $_sTypeName = 'User role';	
	
	
	//
	public function getRoleAssignedCountUrl( Geko_Wp_Role $oRole ) {
		return sprintf( '%s/wp-admin/users.php?role=%s', Geko_Wp::getUrl(), $oRole->getSlug() );
	}
	
	//
	public function getRoleCapabilities( Geko_Wp_Role $oRole ) {
		
		global $wp_roles;
		
		$oWpRole = $wp_roles->get_role( $oRole->getSlug() );
		
		return $oWpRole->capabilities;	
	}
		
	//
	public function getRoleLevel( Geko_Wp_Role $oRole ) {
		
		$iMaxLevel = FALSE;
		$aCaps = $this->getRoleCapabilities( $oRole );
		
		foreach ( $aCaps as $sCap => $bGrant ) {
			
			if ( 0 === strpos( $sCap, 'level_' ) ) {
				
				$iLevel = intval( str_replace( 'level_', '', $sCap ) );
				
				if ( ( FALSE === $iMaxLevel ) || ( $iLevel > $iMaxLevel ) ) {
					$iMaxLevel = $iLevel;
				}
			}
		}
		
		return $iMaxLevel;
	}
	
	
	
	//// concrete implementations
	
	
	//
	public function addAdmin() {
		
		add_filter( 'admin_user_role_select_pq', array( $this, 'removeInvalidRoles' ) );
		add_filter( 'admin_user_fields_pq', array( $this, 'removeWpAdditionalCaps' ) );
		
		add_action( 'profile_update', array( $this, 'updateUser' ) );
		add_action( 'user_register', array( $this, 'updateUser' ) );
		
		add_action( 'admin_geko_roles_edit_fields', array( $this, 'editFields' ) );
		
		add_action( 'admin_geko_roles_edit', array( $this, 'updateUserCaps' ), 10, 3 );
		
		add_action( 'edit_user_profile', array( $this, 'editUserProfile'), 9 );
		add_action( 'edit_user_profile_update', array( $this,'editUserProfileUpdate') );
	}
	
	
	
	//
	public function editUserProfile() {
		
		// get current user info
		$oWpUser = new WP_User( $_GET[ 'user_id' ] );
		
		if ( $oWpUser->data->_geko_role_id ) {
			$oRole = new Geko_Wp_Role( $oWpUser->data->_geko_role_id );
		} elseif ( $oWpUser->roles ) {
			$oRole = new Geko_Wp_Role( current( $oWpUser->roles ) );
		}
		
		//print_r( $oWpUser->roles );
		//print_r( $oWpUser->caps );
		//print_r( $oWpUser->data );
		// var_dump( $oWpUser->get_role_caps() );
		
		//$oWpUser->add_cap( 'kloo', FALSE );
		//$oWpUser->add_cap( 'kloo' );
		//$oWpUser->remove_cap( 'kloo' );
		
		$aRoleCaps = ( $oRole ) ? $oRole->getCapabilities() : array();
		$aUserCaps = $oWpUser->caps;
		
		$aCaps = self::getCapabilitiesList( $aUserCaps );
		$oCapGrid = new Geko_Grid( $aCaps, 5 );
		
		?>
		<h3>Additional Capabilities</h3>
		
		<div>
			<style>
				input.checkbox {
					width: 20px;
				}
			</style>
			<table class="form-table">
				<?php for ( $i = 0; $i < $oCapGrid->rows(); $i++ ): ?>
					<tr><?php
						for ( $j = 0; $j < $oCapGrid->cols(); $j++ ):
							$aCap = $oCapGrid->item( $i, $j );
							if ( $aCap ):
								
								$sKey = $aCap[ 'key' ];
								$sLabel = $aCap[ 'label' ];
								
								if ( $aRoleCaps[ $sKey ] ) {
									
									$sChecked = ' checked="checked" disabled="disabled" ';
									$sLabel = sprintf( '<em>%s</em>', $sLabel );
								
								} elseif ( isset( $aUserCaps[ $sKey ] ) ) {
									
									$sChecked = ( $aUserCaps[ $sKey ] ) ? ' checked="checked" ' : '' ;
									$sLabel = ( $aCap[ 'other' ] ) ? sprintf( '<strong>%s</strong>', $sLabel ) : $sLabel;
								
								} else {
									
									$sChecked = '';
								}
								
								?><td>
									<input id="user_role_caps-<?php echo $sKey; ?>" name="user_role_caps[<?php echo $sKey; ?>]" type="checkbox" class="checkbox" value="1" <?php echo $sChecked; ?> /> 
									<label class="side"><?php echo $sLabel; ?></label>
								</td><?php
							else:
								?><td>&nbsp;</td><?php
							endif;
						endfor; ?>
					</tr>
				<?php endfor; ?>
			</table>
			<span class="description">Assign extra capabilities to this user.</span>
		</div>
		
		<?php
	}
	
	//
	public function editFields( $oRole ) {
		
		if ( $this->getName() == $oRole->getType() ) {
		
			$aCaps = self::getCapabilitiesList();
			$oCapGrid = new Geko_Grid( $aCaps, 4 );
			
			$aRoleCaps = $oRole->getCapabilities();
			$iLevel = $oRole->getLevel();
			
			?>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="user_role_caps">Capabilities</label></th>
				<td>
					<table>
						<?php for ( $i = 0; $i < $oCapGrid->rows(); $i++ ): ?>
							<tr><?php
								for ( $j = 0; $j < $oCapGrid->cols(); $j++ ):
									$aCap = $oCapGrid->item( $i, $j );
									if ( $aCap ):
										$sKey = $aCap[ 'key' ];
										$sLabel = $aCap[ 'label' ];
										$sChecked = ( $aRoleCaps[ $sKey ] ) ? ' checked="checked" ' : '';
										?><td>
											<input id="user_role_caps-<?php echo $sKey; ?>" name="user_role_caps[<?php echo $sKey; ?>]" type="checkbox" class="checkbox" value="1" <?php echo $sChecked; ?> /> 
											<label class="side"><?php echo $sLabel; ?></label></td>
										<?php
									else:
										?><td>&nbsp;</td><?php
									endif;
								endfor; ?>
							</tr>
						<?php endfor; ?>
					</table>
					<span class="description">Assign capabilities associated with this role.</span>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="user_role_levels">User Levels</label></th>
				<td>
					<select name="user_role_levels" id="user_role_levels">
						<option value="none">None</option>
						<?php for ( $i = 0; $i <= 10; $i++ ):
							$sChecked = ( $iLevel === $i ) ? ' selected="selected" ' : '';
							?><option value="<?php echo $i; ?>" <?php echo $sChecked; ?> >Level <?php echo $i; ?></option>
						<?php endfor; ?>
					</select><br />
					<span class="description">Assign user levels associated with this role.</span>
				</td>
			</tr>		
			<?php
		}
	}
	
	
	//
	public function updateUser( $iUserId ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aCaps = get_usermeta( $iUserId, $oDb->replacePrefixPlaceholder( '##pfx##capabilities' ) );
		
		if ( is_array( $aCaps ) ) {
			
			update_usermeta(
				$iUserId,
				'_geko_role_id',
				self::getRoleIdFromMetaArray( $aCaps, $this->getRoleHash() )
			);		
		}
	}
	
	
	//
	public function editUserProfileUpdate() {
		
		global $user_id;

		// deal with capabilities
		$aRoleCaps = ( $_POST[ 'user_role_caps' ] ) ? $_POST[ 'user_role_caps' ] : array() ;
		
		$oWpUser = new WP_User( $user_id );
		$aUserCaps = $oWpUser->caps;
		
		foreach ( $aUserCaps as $sCap => $bGrant ) {
			
			if ( $aRoleCaps[ $sCap ] ) {
				
				$oWpUser->add_cap( $sCap );
				unset( $aRoleCaps[ $sCap ] );
			
			} else {
				
				if ( 1 == $user_id ) {
					$oWpUser->add_cap( $sCap, FALSE );					// never fully remove role if admin
				} else {
					$oWpUser->add_cap( $sCap );							// weird, can't fully remove cap if grant = TRUE, so do this first
					$oWpUser->remove_cap( $sCap );					
				}
			}
		}
		
		foreach ( $aRoleCaps as $sCap => $bGrant ) {
			$oWpUser->add_cap( $sCap );		
		}
		
	}
	
	//
	// public function updateUserCaps( $sSlug, $sOldSlug, $bTypeChanged )
	public function updateUserCaps( $oOldRole, $oNewRole ) {
		
		if ( $this->_sTypeName == $oNewRole->getType() ) {
			
			//// only make changes if there was no change in type
			
			global $wp_roles;
			$sSlug = $oNewRole->getSlug();
			
			// deal with capabilities
			$aRoleCaps = ( $_POST[ 'user_role_caps' ] ) ? $_POST[ 'user_role_caps' ] : array() ;
			$aCaps = self::getCapabilitiesList();
			
			foreach ( $aCaps as $i => $aCap ) {
				$sCap = $aCap[ 'key' ];
				if ( $aRoleCaps[ $sCap ] ) {
					$wp_roles->add_cap( $sSlug, $sCap );
				} else {
					if ( FALSE !== strpos( $sSlug, 'admin' ) ) {
						$wp_roles->add_cap( $sSlug, $sCap, FALSE );					// never fully remove role if admin
					} else {
						$wp_roles->remove_cap( $sSlug, $sCap );					
					}
				}
			}
			
			// deal with user levels
			if ( 'none' == $_POST[ 'user_role_levels' ] ) {
				$wp_roles->remove_cap( $sSlug, 'level_0' );
				$iUserLevel = 0;
			} else {
				$iUserLevel = intval( $_POST[ 'user_role_levels' ] );
				if ( 0 == $iUserLevel ) {
					$wp_roles->add_cap( $sSlug, 'level_0' );			
				}
			}
			
			for ( $i = 1; $i <= 10; $i++ ) {
				if ( $i <= $iUserLevel ) {
					$wp_roles->add_cap( $sSlug, sprintf( 'level_%d', $i ) );
				} else {
					if ( FALSE !== strpos( $sSlug, 'admin' ) ) {
						$wp_roles->add_cap( $sSlug, sprintf( 'level_%d', $i ), FALSE );			// never fully remove role if admin	
					} else {
						$wp_roles->remove_cap( $sSlug, sprintf( 'level_%d', $i ), $sCap );					
					}
				}
			}
		}
	}
	
	
	//// phpQuery
	
	//
	public function removeInvalidRoles( $oPqSel ) {
		
		$aRoleHash = $this->getRoleHash();		// get role hash
		
		foreach ( $oPqSel[ 'option' ] as $oElemOption ) {
			$oPqOption = pq( $oElemOption );
			if ( $sSlug = $oPqOption->val() ) {
				if ( !isset( $aRoleHash[ $sSlug ] ) ) {
					$oPqOption->remove();
				}
			}
		}
		
		return $oPqSel;
	}
	
	//
	public function removeWpAdditionalCaps( $oPqForm ) {
		
		foreach ( $oPqForm[ 'table.editform' ] as $oElemTable ) {
			$oPqTable = pq( $oElemTable );
			if ( 'Additional Capabilities' == $oPqTable[ 'th' ]->html() ) {
				$oPqTable->remove();
			}
		}
		
		return $oPqForm;
	}
	
	
	
	//
	public function reconcileAssigned() {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			
			->field( 'c.user_id', 'user_id' )
			->field( 'c.meta_value', 'caps' )
			
			->from( '##pfx##usermeta', 'c' )
			
			->joinLeft( '##pfx##usermeta', 'r' )
				->on( 'r.user_id = c.user_id' )
				->on( 'r.meta_key = ?', '_geko_role_id' )
				->on( 'r.meta_value IS NULL' )
			
			->where( 'c.meta_key = ?', $oDb->replacePrefixPlaceholder( '##pfx##capabilities' ) )
			->where( 'r.meta_value IS NULL' )
		;
		
		// check for user records to be reconciled
		$aRes = $oDb->fetchAllObj( strval( $oQuery ) );
		
		if ( $aRes ) {
		
			$aRoleHash = $this->getRoleHash();		// get role hash
			
			foreach ( $aRes as $oMeta ) {
				
				$aCaps = maybe_unserialize( $oMeta->caps );
				
				if ( is_array( $aCaps ) ) {
					
					update_usermeta(
						$oMeta->user_id,
						'_geko_role_id',
						self::getRoleIdFromMetaArray( $aCaps, $aRoleHash )
					);
				}
			}
		}
		
		return parent::reconcileAssigned();
	}
	
	
	//
	public function reconcileRoleOnUpdate( Geko_Wp_Role $oOldRole, Geko_Wp_Role $oNewRole ) {
		
		// check if the slugs have changed
		$sOldSlug = $oOldRole->getSlug();
		$sNewSlug = $oNewRole->getSlug();
		$iId = $oNewRole->getId();				// or $oOldRole->getId(), doesn't matter, still the same
		
		if ( $sOldSlug != $sNewSlug ) {
			
			// reconcile the ##pfx##capabilities for matching users
			$oDb = Geko_Wp::get( 'db' );
			
			$sCapsMetaKey = $oDb->replacePrefixPlaceholder( '##pfx##capabilities' );
			
			$oQuery = new Geko_Sql_Select();
			$oQuery
				
				->field( 'c.user_id', 'user_id' )
				->field( 'c.meta_value', 'caps' )
				
				->from( '##pfx##usermeta', 'c' )
				
				->joinLeft( '##pfx##usermeta', 'r' )
					->on( 'r.user_id = c.user_id' )
				
				->where( 'c.meta_key = ?', $sCapsMetaKey )
				->where( 'r.meta_key = ?', '_geko_role_id' )
				->where( 'r.meta_value = ?', $iId )
			;
			
			// check for user records to be reconciled
			$aRes = $oDb->fetchAllObj( strval( $oQuery ) );
			
			foreach ( $aRes as $oMeta ) {
				
				$aMeta = maybe_unserialize( $oMeta->caps );
				
				if ( is_array( $aMeta ) ) {
					
					unset( $aMeta[ $sOldSlug ] );		// unset
					$aMeta[ $sNewSlug ] = TRUE;			// re-set
					update_usermeta( $oMeta->user_id, $sCapsMetaKey, $aMeta );
				}
			}
			
		}
		
		return parent::reconcileRoleOnUpdate( $oOldRole, $oNewRole );
	}
	
	
	
	//
	public function populateCounts() {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			->field( 'c.meta_value', 'role_id' )
			->field( 'COUNT(*)', 'num' )
			->from( '##pfx##usermeta', 'c' )
			->where( 'c.meta_key = ?', '_geko_role_id' )
			->group( 'c.meta_value' )
		;
		
		$aRes = $oDb->fetchAllObj( strval( $oQuery ) );
		
		if ( $aRes ) {
			foreach ( $aRes as $oCount ) {
				$this->_aCounts[ $oCount->role_id ] = $oCount->num;
			}
		}
		
		return $this;
	}
	
	
	
	
	//// helpers
	
	//
	protected function getRoleHash() {
		
		// create a role hash
		$aRoles = new Geko_Wp_Role_Query( array( 'role_type' => $this->getName() ) );
		$aRoleHash = array();
		
		foreach ( $aRoles as $oRole ) {
			$aRoleHash[ $oRole->getSlug() ] = $oRole->getId();
		}
		
		return $aRoleHash;
	}
	
	//
	public static function getRoleIdFromMetaArray( $aMeta, $aRoleHash ) {
		
		foreach ( $aRoleHash as $sSlug => $iId ) {
			if ( isset( $aMeta[ $sSlug ] ) ) {
				return $iId;
			}
		}
		
		return 0;
	}
	
	//
	protected static function getCapabilitiesList( $aOtherCaps = array() ) {
		
		global $wp_roles;
		
		// Get Role List
		$aCapsTemp = array();
		$aCaps = array();
		$aRoles = array();
		
		foreach ( $wp_roles->role_objects as $sRole => $oWpRole ) {
			$aRoles[ $sRole ] = 1;
			// $bGrant is unused
			foreach ( $oWpRole->capabilities as $sCap => $bGrant ) {
				if ( 0 !== strpos( $sCap, 'level_' ) ) {
					$aCapsTemp[ $sCap ] = $sCap;
					if ( isset( $aOtherCaps[ $sCap ] ) ) {
						unset( $aOtherCaps[ $sCap ] );
					}
				}
			}
		}
		
		// $bGrant is unused
		foreach ( $aOtherCaps as $sCap => $bGrant ) {
			if ( !isset( $aRoles[ $sCap ] ) ) {
				$aCapsTemp[ $sCap ] = $sCap;
			}			
		}
		
		sort( $aCapsTemp );
		
		foreach ( $aCapsTemp as $sCap ) {
			$aCap = array(
				'key' => $sCap,
				'label' => ucwords( str_replace( array('_','-') , ' ', $sCap) )
			);
			if ( isset( $aOtherCaps[ $sCap ] ) ) {
				$aCap[ 'other' ] = 1;
			}
			$aCaps[] = $aCap;
		}
		
		unset( $aCapsTemp );
		return $aCaps;	
	}

	
	
	
	
}


