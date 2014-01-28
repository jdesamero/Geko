<?php
/*
Template Name: Locations
*/


//
class Gloc_Layout_Template extends Gloc_Layout
{

	//
	public function echoEnqueue() {
		$this->enqueueScript( 'geko-jquery-geko_util' );
	}
	
	//
	public function echoHeadLate() {
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls()
		);
		
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
			
				$( document ).bind( 'ajaxSuccess', function( evt, xhr, stngs ) {
					
					var res = xhr.responseJSON;
					
					if (
						( 'object' === $.type( res ) ) && 
						( ( 'load' === res.type ) || ( 'search' === res.type ) )
					) {
						
						var eSlDiv = $( '#sl_div' );
						
						$.each( res.response, function( i, v ) {
							
							if ( v.attributes && v.attributes.details_page_url ) {
								var eTitle = eSlDiv.find( '#slp_results_table_%s .location_name'.printf( v.id ) );
								var eLink = $( '<a><\/a>' );
								eLink.attr( 'href', v.attributes.details_page_url );
								eTitle.wrap( eLink );								
							}
							
						} );
					}
					
				} );
			
			} );
			
		</script>
		<?php
		
	}



	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<div class="entry-content">				
				
				<?php echo do_shortcode( '[STORE-LOCATOR]' ) ?> 
				
				<div class="contact-area">
					<h1 class="page-title"><?php $oPage->echoTitle(); ?></h1>
					
					<div class="main-contact">
						<?php $oPage->echoTheContent(); ?>
					</div>
					<div class="intro-text">
						<?php $oPage->echoMeta( 'intro_text' ); ?>
					</div>
				</div>
				
				<?php $this->doLinkPages(); ?>
				<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				
				<div class="clear"></div>
				<div class="breadcrumbs"><?php $this->doNavBreadcrumb( 'main' ); ?></div>
				
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}

geko_render_template();

