<?php
/*
Template Name: Homepage
*/

//
class Gloc_Layout_Homepage extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Link'
	);
	
	protected $_mStyles = 'gloc-gallery';
	protected $_mScripts = 'geko-jquery-geko_slideshow';
	
	
	
	//
	public function echoHeadLate() {
		?>
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
					<table class="ovly">
						<tr><td class="top"></td></tr>
						<tr><td class="inner">
							<h1><?php $oAtt->echoTitle(); ?></h1>
							<p class="content"><?php $oAtt->echoContent(); ?></p>
							<div class="gly_ctls"></div>
						</td></tr>
						<tr><td class="bottom"></td></tr>
					</table>
				</div>
			<?php endforeach; ?>			
		</div>
		
		<div id="homeboxes">
			
			<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<?php foreach ( $aBoxes as $i => $oBoxPage ): ?>
						<td class="<?php echo $i ? 'homesepline' : ''; ?>"><div class="boxcontent">
							<h1><?php $oBoxPage->echoTitle(); ?></h1>
							<?php $oBoxPage->echoContent(); ?>
						</div></td>
					<?php endforeach; ?>
				</tr>
				<tr>
					<?php foreach ( $aBoxes as $i => $oBoxPage ): ?>
						<td class="<?php echo $i ? 'homesepline' : ''; ?>"><div class="boxcontent">
							<div class="boxcontent"><span class="boxlinkout"><a href="<?php $oBoxPage->echoUrl(); ?>"><?php $this->l_101(); ?></a></span></div>
						</div></td>
					<?php endforeach; ?>
				</tr>
			</table>
		</div>
		<?php
		
	}
	
}



