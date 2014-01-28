<?php
/*
Template Name: Product/Application Detail
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	//
	public function echoEnqueue() {
		wp_enqueue_script( 'geko-jquery-geko_slideshow' );
		wp_enqueue_script( 'geko-jquery-prettyphoto' );
		
		wp_enqueue_style( 'geko-jquery-prettyphoto' );
	}
	
	//
	public function echoHeadLate() {
		
		?>
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'stylesheet_directory' ); ?>/styles/gallery.css" />
		
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				$( '.interior-gallery' ).gekoSlideshow();
				
				//
				$("a[rel^='prettyPhoto']").prettyPhoto( {
					theme: 'light_square',
					overlay_gallery: false					
				} );
				
			} );
			
        </script>
		<?php
	}
	
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		$aThumbParams = array( 'w' => 96, 'h' => 59 );
		
		//
		$aSlideAtts = $oPage->getAttachments( array(
			'file_group' => 'slideshow_images',
			'orderby' => 'file_menu_order'
		) );
		
		$aGallery = $oPage->getAttachments( array( 'file_group' => 'gallery_images' ) );
		
		$aBrands = $this->newPost_Query( array(
			'post__in' => $oPage->getMetaMemberIds( 'brand_logos' ),
			'showposts' => -1,
			'posts_per_page' => -1
		), FALSE );
		
		?>
		
		<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass( '' ); ?>">
			<div class="entry-content">
				
				<div class="interior-gallery">
					<?php foreach ( $aSlideAtts as $oAtt ): ?>
						<div class="gly_item">
							<img class="gly_bg" src="<?php $oAtt->echoUrl(); ?>" />
							<div class="gly_ctls"></div>
						</div>
					<?php endforeach; ?>
				</div>		
				
				<h1 class="page-title"><?php $oPage->echoTitle(); ?></h1>
				
				<div class="dk-gray">
					<div class="intro-text">
						<?php $oPage->echoMeta( 'intro_text' ); ?>
					</div>
					
					<div class="two-cols">
						<?php $oPage->echoTheContent(); ?>
					</div>
					<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
					
					<?php if ( $aGallery->count() > 0 ): ?>
						<h3>Gallery</h3>
						<ul class="pretty-gallery">
							<?php foreach ( $aGallery as $oAtt ): ?>
								<li>
									<a href="<?php $oAtt->echoUrl(); ?>" rel="prettyPhoto[pp_gal]" title="<?php $oAtt->escechoTitle(); ?>">
										<img src="<?php $oAtt->echoTheImageUrl( array( 'w' => 154, 'h' => 78 ) ); ?>" width="154" height="78" />
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
						<div class="clear"></div>
					<?php endif; ?>
				</div>
				
				<div class="zigs">
					<?php if ( $aBrands->count() > 0 ): ?>
						<div class="brand-box">
							<h3>Brands</h3>
							<ul class="brand-list">
								<?php foreach ( $aBrands as $oBrand ):
									$oAtt = $oBrand->getAttachments( array( 'file_group' => 'brand_logo' ) )->getOne();
								?>
									<li><img src="<?php $oAtt->echoTheImageUrl( $aThumbParams ); ?>" width="96" height="59" /></li>
								<?php endforeach; ?>
							</ul>
							<div class="vert-line"></div>
						</div>
					<?php endif; ?>
					
					<div class="info-box">
						<h3>More Information</h3>
						<?php $oPage->echoMeta( 'more_info' ); ?>
					</div>
					<div class="clear"></div>
					
					<div class="breadcrumbs"><?php $this->doNavBreadcrumb( 'main' ); ?></div>
				</div>
				
				<?php $this->doLinkPages(); ?>
				
			</div>
		</div>
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
}

geko_render_template();

