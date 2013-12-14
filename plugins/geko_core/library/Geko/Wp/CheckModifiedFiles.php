<?php

//
class Geko_Wp_CheckModifiedFiles extends Geko_File_CheckModified
{

	//
	public function changed( $sOptionKey )
	{
		if ( '' != $sOptionKey ) {
		
			$sOptionVal = get_option( $sOptionKey );
			
			if ( '' == $sOptionVal ) {
				// initialize
				add_option( $sOptionKey, $this->sFileHash );
				return TRUE;
			} elseif ( $this->sFileHash != $sOptionVal ) {
				// update
				update_option( $sOptionKey, $this->sFileHash );
				return TRUE;
			} else {
				// no change
				return FALSE;
			}
		
		} else {
			throw new Exception('Empty option key provided for ' . __CLASS__ . '::' . $sMethod . '.');
		}
		
	}
	

}


