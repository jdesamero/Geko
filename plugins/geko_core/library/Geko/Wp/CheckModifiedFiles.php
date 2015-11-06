<?php
/*
 * "geko_core/library/Geko/Wp/CheckModifiedFiles.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_Wp_CheckModifiedFiles extends Geko_File_CheckModified
{

	//
	public function changed( $sOptionKey ) {
		
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
			throw new Exception( sprintf( 'Empty option key provided for %s::%s.', __CLASS__, $sMethod ) );
		}
		
	}
	

}


