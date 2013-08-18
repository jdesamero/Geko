<?php

//
class Geko_Wp_Page_Alias extends Geko_Wp_Page_Meta
{
	//
	protected $oActualPage;
	protected $oApparentPage;
	
	
	
	//// init


	//
	public function addTheme() {
		parent::addTheme();
		add_action( 'pre_get_posts', array( $this, 'resolveTheme' ) );
		add_filter( 'Geko_Wp_NavigationManagement_Page_Page::isCurrentPage::page', array( $this, 'isNavCurrentPage' ), 10, 2 );
		return $this;
	}
	
	
	
	
	//
	public function addAdmin() {
		parent::addAdmin();
		add_action( 'admin_page_attributes_pq', array( $this, 'attachToAttributesDiv' ) );
		return $this;
	}
	
	// override so that this does nothing
	public function attachPage() { }
	
	
	//
	public function isNavCurrentPage( $bRes, $iPageId ) {
		if (
			( is_page() ) && 
			( $this->getActualId() != $this->getApparentId() )
		) {
			return ( $iPageId == $this->getApparentId() );
		}
		return $bRes;
	}
	
	
	//
	public function resolveTheme( $aQuery ) {
		
		if ( is_page() ) {
			
			static $bDefaultQuery = FALSE;
			
			if ( !$bDefaultQuery ) {
				
				// use backtrace to determine if this is the default query
				
				$aBt = debug_backtrace( FALSE );
				
				if (
					( 'WP_Query' == $aBt[ 4 ][ 'class' ] ) && 
					( 'query' == $aBt[ 4 ][ 'function' ] )
				) {
					
					$iPageId = $aQuery->query_vars[ 'page_id' ];
					if ( !$iPageId ) {
						$oPg = get_page_by_path( $aQuery->query_vars[ 'pagename' ] );
						$iPageId = $oPg->ID;
					}
					
					if ( $iPageId ) {
						
						$oApparentPage = new Geko_Wp_Page( $iPageId );
						$this->oApparentPage = $oApparentPage;
									
						$iAliasPageId = $this->getPageId( $oApparentPage->getId() );
						
						if ( $iAliasPageId ) {
							
							$oActualPage = new Geko_Wp_Page( $iAliasPageId );
							$this->oActualPage = $oActualPage;
							
							// set these properties to force the page to render as the original
							$aQuery->queried_object = get_page( $iAliasPageId );
							$aQuery->queried_object_id = $iAliasPageId;
							$aQuery->query_vars[ 'page_id' ] = $iAliasPageId;
							
						} else {
							$oActualPage = $oApparentPage;
							$this->oActualPage = $oActualPage;
						}
						
						$bDefaultQuery = TRUE;
						
					}
					
				}
				
			}
		
		}
		
	}
	
	
	
	
	//// accessors

	// get the actual page, if one is specified
	public function getPageId( $iPageId ) {
		return $this->getMeta( $iPageId, $this->getPrefixWithSep() . 'page_alias' );
	}
	
	// actual
	
	//
	public function getActualPage() {
		return $this->oActualPage;
	}
	
	//
	public function getActualId() {
		return ( $this->oActualPage ) ? 
			$this->oActualPage->getId() : 
			NULL
		;
	}
	
	//
	public function getActualSlug() {
		return ( $this->oActualPage ) ? 
			$this->oActualPage->getSlug() : 
			NULL
		;
	}
	
	
	// apparent
	
	//
	public function getApparentPage() {
		return $this->oApparentPage;
	}
	
	//
	public function getApparentId() {
		return ( $this->oApparentPage ) ?
			$this->oApparentPage->getId() : 
			NULL
		;
	}
	
	//
	public function getApparentSlug() {
		return ( $this->oApparentPage ) ?
			$this->oApparentPage->getSlug() : 
			NULL
		;
	}

	
	
	//// front-end display methods
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		if ( $this->isDisplayMode( 'add|edit' ) ) {
			
			$sPfs = $this->getPrefixWithSep();
			
			?><script type="text/javascript">
				
				jQuery( document ).ready( function( $ ) {
					
					var pfs = '<?php echo $sPfs; ?>';
					
					var updateAliasFields = function ( fade, delay ) {
						
						pageAliasId = parseInt( $( '#' + pfs + 'page_alias' ).val() );
						
						if ( pageAliasId ) {
							$( '#postdivrich, #normal-sortables, .__not_page_alias' ).each( function () {
								$( this ).hideX( fade, delay );
							} );
						} else {
							$( '#postdivrich, #normal-sortables, .__not_page_alias' ).each( function () {
								$( this ).showX( fade, delay );
							} );
						}
					}
					
					updateAliasFields();									// update with no effects
					// ajaxUpdateCallbacks.push( updateAliasFields );		// register so it's triggered during an ajax request
					
					$( '#' + pfs + 'page_alias' ).change( function () {
						updateAliasFields( 200 );
					} );
					
				} );
				
			</script><?php
		}
		
		return $this;
	}
	
	
	//
	public function attachToAttributesDiv( $oPq ) {
		
		global $post;
		
		if ( $post ) {
			$oPq[ '.inside > *' ]->addClass( '__not_page_alias' );
			$oPq[ '.inside > p:first-child, .inside > label[for=parent_id], .inside > #parent_id' ]->removeClass( '__not_page_alias' );
			$oPq[ '.inside' ]->append('
				<h5>Alias</h5>
				<label for="menu_order" class="screen-reader-text">Page Alias</label>
				' . $this->inject() . '
				<p>Choosing a page alias makes this page behave like the alias. Useful if a page has to appear in different parts of a navigation tree, which can cause an undesired "on" state for multiple items.</p>
			');
		}
		
		return $oPq;
	}
	
	
	
	//
	protected function formFields() {
		
		global $post;
		
		$aParams = array(
			'post_type' => $post->post_type,
			'exclude_tree' => $post->ID,
			'name' => 'page_alias',
			'show_option_none' => __( 'None' ),
			'sort_column'=> 'menu_order, post_title',
		);
		
		wp_dropdown_pages( $aParams );
		
	}
	
	
}



