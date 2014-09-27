<?php

//
class Geko_Wp_Group_Member_Query extends Geko_Wp_Entity_Query
{
	protected $_sGroupType;
	
	//
	public function getDefaultParams() {
		
		$aRet = array();
		
		if ( !$aRet[ 'geko_group_type' ] && $this->_sGroupType ) {
			$aRet[ 'geko_group_type' ] = $this->_sGroupType;
		}
		
		return $aRet;
	}
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			
			->field( 'gpm.group_id' )
			->field( 'gpm.user_id' )
			->field( 'gpm.status_id' )
			->field( 'gpm.date_requested' )
			->field( 'gpm.date_joined' )
			->from( '##pfx##geko_group_members', 'gpm' )
			
			
			->field( 'g.title', 'group_title' )
			->joinLeft( '##pfx##geko_group', 'g' )
				->on( 'g.group_id = gpm.group_id' )
			
			
			->field( 'u.display_name', 'user_display_name' )
			->joinLeft( '##pfx##users', 'u' )
				->on( 'u.ID = gpm.user_id' )
			
			->fieldKvp( 'um1.meta_value', 'first_name' )
			->fieldKvp( 'um2.meta_value', 'last_name' )
			->joinLeftKvp( '##pfx##usermeta', 'um*' )
				->on( 'um*.user_id = u.ID' )
				->on( 'um*.meta_key = ?', '*' )
			
		;
		
		//// filter by status
		
		if ( $aParams[ 'status' ] ) {
			$aParams[ 'status_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'status' ] );
		}
		
		if ( $aParams[ 'status_id' ] ) {
			$oQuery->where( 'gpm.status_id = ?', $aParams[ 'status_id' ] );
		}
		
		//// filter by group
		
		if ( $aParams[ 'group_id' ] ) {
			$oQuery->where( 'gpm.group_id = ?', $aParams[ 'group_id' ] );
		}
		
		// group_type, grptype_id
		if ( $aParams[ 'geko_group_type' ] ) {
			$aParams[ 'geko_group_type_id' ] = Geko_Wp_Options_MetaKey::getId( $aParams[ 'geko_group_type' ] );
		}
		
		if ( $aParams[ 'geko_group_type_id' ] ) {
			$oQuery->where( 'g.grptype_id = ?', $aParams[ 'geko_group_type_id' ] );
		}
		
		//// filter by user

		if ( $aParams[ 'user_id' ] ) {
			$oQuery->where( 'gpm.user_id = ?', $aParams[ 'user_id' ] );
		}
		
		return $oQuery;
		
	}

}


