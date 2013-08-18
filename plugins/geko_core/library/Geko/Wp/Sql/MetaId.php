<?php

//
class Geko_Wp_Sql_MetaId extends Geko_Sql_Callback
{
	protected $fCallback = array( 'Geko_Wp_Options_MetaKey', 'getId' );
	
	//
	public function __construct( $sMetaKey ) {
		$this->aArgs = array( $sMetaKey );
	}
	
}

