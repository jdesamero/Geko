<?php

//
class Geko_Wp_Category_Alias extends Geko_Wp_Category_Meta
{	
	//
	protected $oActualCat;
	protected $oApparentCat;
	
	
	//// init
	
	//
	public function addTheme() {
		parent::addTheme();
		add_action( 'pre_get_posts', array( $this, 'resolveTheme' ) );
		add_filter( 'Geko_Wp_NavigationManagement_Page_Category::isCurrentCategory::category', array( $this, 'isNavCurrentCategory' ), 10, 2 );
		return $this;
	}
	
	
	//
	public function isNavCurrentCategory( $bRes, $iCatId ) {
		if (
			( is_category() ) && 
			( $this->getActualId() != $this->getApparentId() )
		) {
			return ( $iCatId == $this->getApparentId() );
		}
		return $bRes;
	}
	
	
	//
	public function resolveTheme( $aQuery ) {
		
		if ( is_category() ) {
			
			static $bDefaultQuery = FALSE;
			
			if ( !$bDefaultQuery ) {
				
				// use backtrace to determine if this is the default query
				
				$aBt = debug_backtrace( FALSE );
				if (
					( 'WP_Query' == $aBt[ 4 ][ 'class' ] ) && 
					( 'query' == $aBt[ 4 ][ 'function' ] )
				) {
					
					$iCatId = $aQuery->query_vars[ 'cat' ];
					if ( !$iCatId ) {
						$iCatId = Geko_Wp_Category::get_ID( $aQuery->query_vars[ 'category_name' ] );
					}

					$oApparentCat = new Geko_Wp_Category( $iCatId );
					$this->oApparentCat = $oApparentCat;
					
					$iAliasCatId = $this->getCatId( $iCatId );
					
					if ( $iAliasCatId ) {
						
						$oActualCat = new Geko_Wp_Category( $iAliasCatId );
						$this->oActualCat = $oActualCat;
						
						// set these properties to force the page to render as the original
						$aQuery->queried_object_id = $iAliasCatId;
						$aQuery->query_vars[ 'category_name' ] = $oActualCat->getSlug();
						$aQuery->query_vars[ 'cat' ] = $iAliasCatId;
						$aQuery->parse_tax_query( $aQuery->query_vars );
						
					} else {
						$oActualCat = $oApparentCat;
						$this->oActualCat = $oActualCat;
					}
										
					$bDefaultQuery = TRUE;
					
				}
				
			}
			
		}
	}
	
	
	
	
	
	
	//// accessors
	
	// get the actual category, if one is specified
	public function getCatId( $iCatId ) {
		return $this->getMeta( $iCatId, $this->getPrefixWithSep() . 'category_alias' );
	}
	
	
	// actual
	
	//
	public function getActualCat() {
		return $this->oActualCat;
	}

	//
	public function getActualId() {
		return ( $this->oActualCat ) ? 
			$this->oActualCat->getId() : 
			NULL
		;	
	}
	
	//
	public function getActualSlug() {
		return ( $this->oActualCat ) ? 
			$this->oActualCat->getSlug() : 
			NULL
		;	
	}
	
	
	// apparent
	
	//
	public function getApparentCat() {
		return $this->oApparentCat;
	}
	
	//
	public function getApparentId() {
		return ( $this->oApparentCat ) ? 
			$this->oApparentCat->getId() : 
			NULL
		;
	}
	
	//
	public function getApparentSlug() {
		return ( $this->oApparentCat ) ? 
			$this->oApparentCat->getSlug() : 
			NULL
		;
	}
	
	
	
	
	//// front-end display methods
	
	//
	public function addAdminHead() {
		
		parent::addAdminHead();
		
		$sPfs = $this->getPrefixWithSep();
		
		?>
		<script type="text/javascript">
		
			jQuery( document ).ready( function( $ ) {
				
				var pfs = '<?php echo $sPfs; ?>';
				
				// get rid of -1 value
				$( '#' + pfs + 'category_alias option:first-child' ).val( '' );
				
				var updateAliasFields = function ( fade, delay ) {
					
					catAliasId = parseInt( $( '#' + pfs + 'category_alias').val() );
					
					if ( catAliasId ) {
						$( '.form-field' ).each( function () {
							if ( !$(this).find( '#' + pfs + 'category_alias, #cat_name, #category_nicename, #category_parent, #name, #slug, #parent' ).length ) {
								$( this ).hideX( fade, delay );
							}
						} );
					} else {
						$( '.form-field' ).each( function () {
							$( this ).showX( fade, delay );
						} );
					}
				}
				
				updateAliasFields();								// update with no effects
				ajaxUpdateCallbacks.push( updateAliasFields );		// register so it's triggered during an ajax request
				
				$( '#' + pfs + 'category_alias').change( function () {
					updateAliasFields( 200 );
				} );
				
			} );
			
		</script>
		<?php
		
		return $this;
	}
	
	//
	public function formFields() {
		
		$aParams = array(
			'hide_empty' => 0,
			'name' => 'category_alias',
			'orderby' => 'name',
			'hierarchical' => TRUE,
			'show_option_none' => 'None'
		);
		
		if ( $iCatdId = $this->_getCatId() ) {
			$aParams[ 'exclude' ] = $iCatdId;
		}
		
		?>
		<p>
			<label class="main">Category Alias</label> 
			<?php wp_dropdown_categories( $aParams ); ?>
			<label class="description">Choosing a category alias makes this category behave like the alias. Useful if a category has to appear in different parts of a navigation tree, which can cause an undesired "on" state for multiple items.</label>
		</p>
		<?php
	}
	
}


