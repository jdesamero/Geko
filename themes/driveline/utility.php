<?php
/*
Template Name: Utility
*/

//
class Gloc_Layout_Template extends Gloc_Layout
{
	
	protected $_aLabels = array(
		101 => 'Loading Ajax Content...'
	);
	
	
	
	//
	public function echoHeadLate() {
		
		$aJsonParams = array(
			'script' => $this->getScriptUrls()
		);
		
		?>
		<script type="text/javascript">
				
			jQuery( document ).ready( function( $ ) {
			
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				$( window ).load( function() {
					$.get(
						oParams.script.url + '\/ajax-content\/',
						{
							section: 'blog'
						},
						function ( data ) {
							$( '#ajax-content' ).html( '<p>' + data.content + '<\/p>' );
						},
						'json'
					);
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
			<h1><?php $oPage->echoTitle(); ?></h1>
			<div class="entry-content">
				<?php $oPage->echoTheContent(); ?>
				
				<div id="ajax-content">
					<p><?php $this->e_101(); ?></p>
				</div>
				
				<?php $this->doLinkPages(); ?>
				<?php echo $this->sw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
			</div>
		</div>
		<?php
				
	}
}

geko_render_template();

