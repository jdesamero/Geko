<?php

// this is a Geko_Delegate
class Geko_Wp_User_Record_Meta extends Geko_Entity_Record_Plugin
{
	
	//
	public function handleOtherValues( $aValues, $sMode, $oSubject, $oRecord ) {
		
		$iUserId = $oSubject->getId();
		
		foreach ( $aValues as $sKey => $mValue ) {
			
			update_user_meta( $iUserId, $sKey, $mValue );
		}
		
		
	}
	
	
}

