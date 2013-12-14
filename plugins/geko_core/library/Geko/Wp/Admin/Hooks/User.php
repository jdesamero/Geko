<?php

//
class Geko_Wp_Admin_Hooks_User extends Geko_Wp_Admin_Hooks_PluginAbstract
{
	//
	public function getStates() {
		
		$oUrl = Geko_Uri::getGlobal();
		$sUrlPath = $oUrl->getPath();
		
		if ( FALSE !== strpos( $sUrlPath, '/wp-admin/users.php' ) ) {
			if ( $oUrl->hasVar( 'page' ) ) {
				return array( 'user' );
			} else {
				return array( 'user', 'user_list' );
			}
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/user-new.php' ) ) {
			return array( 'user', 'user_add' );
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/user-edit.php' ) ) {
			return array( 'user', 'user_edit' );
		} elseif ( FALSE !== strpos( $sUrlPath, '/wp-admin/profile.php' ) ) {
			return array( 'user', 'user_profile' );
		}
		
		return FALSE;
	}
	
	//
	public function applyFilters( $sContent, $sState ) {
		
		if ( 'user_list' == $sState ) {
			
			$sContent = $this->replace(
				$sContent,
				'admin_user_role_select_pq',
				'/<select[^>]*?id="new_role".+?<\/select>/s'
			);
			
		}
		
		if ( ( 'user_edit' == $sState ) || ( 'user_add' == $sState ) ) {
			
			$sContent = $this->replace(
				$sContent,
				'admin_user_role_select_pq',
				'/<select[^>]*?id="role".+?<\/select>/s'
			);
			
		}
		
		if ( ( 'user_profile' == $sState ) || ( 'user_edit' == $sState ) ) {
			
			$sContent = $this->replace(
				$sContent,
				'admin_user_fields_pq',
				'/<form[^>]*?id="your-profile".+?<\/form>/s'
			);
		
		}
		
		if ( 'user_edit' == $sState ) {
			
			$sContent = $this->replace(
				$sContent,
				'admin_user_h2_pq',
				'/<h2>.+?<\/h2>/s'
			);
			
		}
		
		return $sContent;
	}
}


