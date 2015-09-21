<?php

//
class Geko_Wp_Ext_WpFilebase_Files extends Geko_Wp_Entity
{
	
	//
	public function getSizeFmt() {
		return Geko_File::formatBytes( intval( $this->getSize() ) );
	}
	
	
}



