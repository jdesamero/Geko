<?php

//
class Geko_Wp_Page_Location_Manage extends Geko_Wp_Post_Location_Manage
{
	protected $_sPostType = 'page';
	protected $_sSubAction = 'page';
	
	protected $_oCurPage;
	
	//
	public function getCurPage() {
		if ( !$this->_oCurPage ) {
			$this->_oCurPage = $this->newPage( Geko_String::coalesce( $_REQUEST[ 'post' ], $_REQUEST[ 'post_ID' ] ) );
		}
		return $this->_oCurPage;
	}
	
	
	// Adds a custom section to the "advanced" Post and Page edit screens
	public function attachPage() {
		$this->initEntities();
		if ( function_exists( 'add_meta_box' ) ) {			
			$this->addMetaBox( 'page', 'advanced' );
		} else {
			add_action( 'dbx_page_advanced', array( $this, 'oldCustomBox' ) );
		}
	}

	
	
}




