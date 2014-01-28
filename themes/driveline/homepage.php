<?php
/*
Template Name: Homepage
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Link'
	);
	
	
	
	//
	public function echoEnqueue() {
		wp_enqueue_script( 'geko-jquery-geko_slideshow' );
	}
	
	//
	public function echoHeadLate() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'stylesheet_directory' ); ?>/styles/gallery.css" />	

		<script type="text/javascript">
					
			jQuery( document ).ready( function( $ ) {
				
				$( '.gallery' ).gekoSlideshow();
								
			} );
			
		</script>
		<?php
	}
	
	//
	public function echoContent() {		
		
		$oPage = $this->newPage();
		
		//
		$aAtts = $oPage->getAttachments( array(
			'file_group' => 'slideshow_images',
			'orderby' => 'file_menu_order'
		) );
		
		//
		$aBoxes = $oPage->getChildren( array(
			'orderby' => 'menu_order'
		) );
		
		?>
		<div class="gallery">
			<?php foreach ( $aAtts as $oAtt ): ?>
				<div class="gly_item">
					<img class="gly_bg" src="<?php $oAtt->echoUrl(); ?>" />
					<div class="gly_ctls"></div>
					<table class="ovly">
						<tr><td>
							<?php $oAtt->echoContent(); ?>
						</td></tr>
					</table>
				</div>
			<?php endforeach; ?>			
		</div>		
		
		<div id="homeboxes">
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<?php foreach ( $aBoxes as $i => $oBoxPage ):
						$oImage = $oBoxPage->getAttachments( array( 'file_group' => 'main_image' ) )->getOne();
					?>
						<td class="<?php echo $i ? 'homesepline' : ''; ?>">
							<div class="boxcontent">
								<a href="<?php $oBoxPage->echoMeta( 'link' ); ?>">
									<img src="<?php $oImage->echoUrl(); ?>" />
									<h1><?php $oBoxPage->echoTitle(); ?></h1>
								</a>
							</div>
						</td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<?php foreach ( $aBoxes as $i => $oBoxPage ): ?>
						<td class="<?php echo $i ? 'homesepline' : ''; ?>">
							<div class="boxcontent">
								<?php $oBoxPage->echoTheContent(); ?>
								<a href="<?php $oBoxPage->echoMeta( 'link' ); ?>" class="button">Read More</a>
							</div>
						</td>
					<?php endforeach; ?>
				</tr>
			</table>
		</div>
		<?php
		
	}
	
}

geko_render_template();

