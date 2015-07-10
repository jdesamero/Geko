<?php

class Gloc_Post_Meta extends Geko_Wp_Post_Meta
{
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		if ( 'post' == $this->getPostType() ) {
			wp_enqueue_style( 'gloc-post_meta' );
			wp_enqueue_script( 'geko-jquery-geko_image_picker' );
		}
		
		return $this;
	}
	
	
	//
	protected function preFormFields() {
		
		$aJsonParams = array(
			'script' => Geko_Wp::getScriptUrls(),
			'prefix' => $this->getPrefixWithSep()
		);
		
		?>
		<!-- styles are now located in <theme folder>/styles/gloc_post_meta.css -->
		<script type="text/javascript">
				
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				$( 'div.image_picker' ).imagePicker( {
					use_id: true
				} );
				
			} );
			
		</script>
		<?php
	}
	
	
	
	//
	protected function formFields() {
		
		if ( $oPost = $this->getCurPost() ) {
			$aImages = $oPost->getAttachments( array( 'images_only' => 1, 'orderby' => 'file_menu_order' ) );
		}
		
		?>
		<table class="fields"><?php
			
			if ( $oPost && $oPost->inCategory( 'news' ) ) {
			
				$this->fieldRow( 'Main Image', 'main_image', array(
					'query' => $aImages
				), 'image_picker' );			
			
			}
			
		?></table>
		<?php
		
	}
	
	
	
}

