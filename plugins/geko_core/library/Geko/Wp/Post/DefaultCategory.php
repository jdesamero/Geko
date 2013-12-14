<?php

//
class Geko_Wp_Post_DefaultCategory extends Geko_Wp_Post_Meta
{
	
	//// init
	
	
	//
	public function add() {
		
		parent::add();
		
		add_filter( 'Geko_Wp_NavigationManagement_Page_Post::isCurrentPost::single', array( $this, 'isDefaultCategory' ), 10, 4 );
		add_filter( 'Geko_Wp_Post::getCategory', array( $this, 'getActualCat' ), 10, 3 );
		
		return $this;	
	}
	
	
	//
	public function addAdmin() {
		parent::addAdmin();
		add_action( 'admin_post_categories_meta_box_pq', array( $this, 'attachToCategoryBox' ) );
		return $this;
	}
	
	//
	public function attachToCategoryBox( $oPq ) {
		
		global $post;
		
		if (
			( $post ) &&
			count ( $aCats = wp_get_post_categories( $post->ID ) ) > 1
		) {
			$oPq[ 'ul#category-tabs' ]->append('<li class="hide-if-no-js"><a href="#categories-default" tabindex="3">Default</a></li>');
			$oPq[ 'ul#category-tabs' ]->after('<div id="categories-default" class="tabs-panel" style="display: none;">' . $this->inject() . '</div>');
		}
		
		return $oPq;
	}
	
	// override so that this does nothing
	public function attachPage() { }
	
	
	//// filter methods
	
	//
	public function isDefaultCategory( $bMatch, $iPostTypeId, $iMatchId, $oPost ) {
		
		if (
			( !$_GET[ Geko_Wp_Category_PostParent::getInstance()->getCatVarName() ] ) && 
			( Geko_Wp_NavigationManagement_PageManager_Post::TYPE_CAT == $iPostTypeId ) && 
			( $iDefaultCatId = $this->getCatId( $oPost->getId() ) )
		) {
			return ( $iMatchId == $iDefaultCatId );
		}
		
		return $bMatch;
	}


	
	//
	public function getActualCat( $oCat, $oPost ) {
		
		if ( $iDefaultCatId = $this->getCatId( $oPost->getId() ) ) {
			return new Geko_Wp_Category( $iDefaultCatId );
		}
		
		return $oCat;
	}
	
	
	//// accessors
	
	// get the actual category, if one is specified
	public function getCatId( $iPostId ) {
		return $this->getMeta( $iPostId, $this->getPrefixWithSep() . 'default_post_cat_id' );
	}
	
	//
	protected function formFields() {
		
		global $post;
		
		if ( !$post ) {
			if ( $iPostId = intval( Geko_String::coalesce( $_POST[ 'post_ID' ], $_GET[ 'post' ] ) ) ) {
				$oPost = new Geko_Wp_Post( $iPostId );
			}
		} else {
			$oPost = new Geko_Wp_Post( $post );	
		}
		
		if ( $oPost ):
			$aCats = $oPost->getCategories();
			?><select id="default_post_cat_id" name="default_post_cat_id">
				<option value="">None Selected</option>
				<?php echo $aCats->implode('<option value="##Id##">##Title##</option>'); ?>
			</select><?php
		endif;
		
	}

}


