<?php

class Gloc_Page_Meta extends Geko_Wp_Page_Meta
{
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		if ( 'page' == $this->getPostType() ) {
			wp_enqueue_style( 'gloc-page_meta' );
			wp_enqueue_script( 'geko-jquery-geko_image_picker' );
		}
		
		return $this;
	}
	
	//
	protected function preFormFields() {
		
		$aJsonParams = array(
			'template_directory' => get_bloginfo( 'template_directory' )
		);
		
		if ( $oPage = $this->getCurPage() ) {
			$aJsonParams[ 'page_template' ] = $oPage->getPageTemplate();
		}
		
		?>
		<!-- styles are now located in <theme folder>/styles/gloc_page_meta.css -->
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
		
		if ( $oPage = $this->getCurPage() ) {
			$aImages = $oPage->getAttachments( array( 'images_only' => 1 ) );
		}
		
		?>
		<table class="fields"><?php
			
			if ( $oPage && ( 'homepage.php' == $oPage->getPageTemplate() ) ) {
				
				$this
					->fieldRow( 'Main Image', 'main_image', array(
						'query' => $aImages
					), 'image_picker' )
					->fieldRow( 'Slideshow Images', 'slideshow_images', array(
						'query' => $aImages,
						'multi' => TRUE
					), 'image_picker' )
				;
				
			} elseif ( $oPage && ( 'page_form.php' == $oPage->getPageTemplate() ) ) {
				
				$aForms = $this->newForm_Query( array(
					'showposts' => -1,
					'posts_per_page' => -1
				), FALSE );
				
				if ( count( $aForms ) > 0 ) {
					
					$this->fieldRow( 'Form', 'form', array(
						'query' => $aForms
					), 'select' );
					
				}
				
			}
			
		?></table>
		<?php
	}
	
	
	
}


