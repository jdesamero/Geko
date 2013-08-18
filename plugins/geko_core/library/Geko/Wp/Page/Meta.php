<?php

//
class Geko_Wp_Page_Meta extends Geko_Wp_Post_Meta
{
	protected $_iPageId = NULL;
	protected $_oCurPage;
	
	
	//
	public function getCurPage() {
		$iPageId = $this->resolvePageId();
		if ( !$this->_oCurPage && $iPageId ) {
			$this->_oCurPage = $this->newPage( $iPageId );
		}
		return $this->_oCurPage;
	}
	
	
	// force a page id
	public function setPageId( $iPageId ) {
		$this->_iPageId = $iPageId;
		return $this;
	}
	
	//
	public function resolvePageId( $iPageId = NULL ) {
		if ( NULL === $iPageId ) {
			return Geko_String::coalesce( $this->_iPageId, $_REQUEST[ 'post' ], $_REQUEST[ 'post_ID' ] );
		}
		return $iPostId;
	}
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_action( 'admin_init_page', array( $this, 'coft_install' )  );
		add_action( 'admin_head_page', array( $this, 'coft_affixAdminHead' )  );
		add_action( 'admin_head_page', array( $this, 'co_addAdminHead' )  );
		
		return $this;
	}
	
	//
	public function attachPage() {
		if ( TRUE == function_exists( 'add_meta_box' ) ) {
			add_meta_box( sanitize_title( $this->_sInstanceClass ), $this->aThemeData[ 'Name' ] . ' Custom Settings', array( $this, 'outputForm' ), 'page', 'normal' );
		}
	}
	
	
}


