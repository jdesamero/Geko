<?php

class Gloc_Page_Meta extends Geko_Wp_Page_Meta
{
	
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		wp_enqueue_script( 'geko-jquery-geko_image_picker' );
		
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
		<style type="text/css">
			
			.fix {
				clear: both;
				height: 1px;
				margin: 0 0 -1px 0;
				overflow: hidden;
			}
			
			#gloc_page_meta table.fields {
				width: 100%;
			}
			
			#gloc_page_meta table.fields input[type=text] {
				width: 95%;
			}

			#gloc_page_meta table.fields input[type=text].short {
				width: 30%;
			}
			
			#gloc_page_meta table.fields textarea {
				width: 95%;
				height: 150px;
			}
			
			#gloc_page_meta table.fields textarea.short {
				width: 95%;
				height: 30px;
			}
			
			/* ---------------------------------------------------------------------------------- */
			
			#gloc_page_meta .image_picker {
				border: solid 1px #dfdfdf;		
				padding: 5px;
				width: 346px;
				height: 163px;
				overflow: auto;
				-moz-border-radius: 5px;
				border-radius: 5px;
			}

			#gloc_page_meta .image_picker img {
				border: solid 2px #dfdfdf;
			}
			
			#gloc_page_meta .image_picker img.ip_not_selected {
				border: solid 2px #fff;		
			}
			
			#gloc_page_meta .image_picker img.ip_selected {
				border: solid 2px red;
			}
			
			#gloc_page_meta .selmulti {
				height: 100px;
			}
			
		</style>
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
		
		$oPage = $this->getCurPage();
		
		if ( $oPage ) {
			$oParent = $oPage->getParent();
			$aImages = $oPage->getAttachments( array( 'images_only' => 1, 'orderby' => 'file_menu_order' ) );
			$aFiles = $oPage->getAttachments( array( 'non_images_only' => 1, 'orderby' => 'file_menu_order' ) );
		}
		
		?>
		<table class="fields">
			<?php if ( $oPage && ( 'homepage.php' == $oPage->getPageTemplate() ) ): ?>
				<tr>
					<th><label for="slideshow_images">Slideshow Images</label></th>
					<td><div class="image_picker multi">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="slideshow_images" name="slideshow_images" class="imgpck_field" _member_ids="yes" />
					</div></td>
				</tr>
			<?php elseif ( $oParent && ( 'homepage.php' == $oParent->getPageTemplate() ) ): ?>		
				<tr>
					<th><label for="main_image">Main Image</label></th>
					<td><div class="image_picker">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="main_image" name="main_image" class="imgpck_field" _member_ids="yes" />
					</div></td>
				</tr>
				<tr>
					<th><label for="link">Link</label></th>
					<td><input type="text" id="link" name="link" /></td>
				</tr>
			<?php elseif ( $oPage && ( 'page_banner.php' == $oPage->getPageTemplate() ) ): ?>	
				<tr>
					<th><label for="main_image">Main Image</label></th>
					<td><div class="image_picker">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="main_image" name="main_image" class="imgpck_field" _member_ids="yes" />
					</div></td>
				</tr>
			<?php elseif ( $oPage && ( 'nosidebar.php' == $oPage->getPageTemplate() ) ): ?>	
				<tr>
					<th><label for="intro_text">Intro Copy (Yellow Text)</label></th>
					<td><textarea id="intro_text" name="intro_text"></textarea></td>
				</tr>
			<?php elseif ( $oPage && ( 'page_product_landing.php' == $oPage->getPageTemplate() ) ): ?>
				<tr>
					<th><label for="main_image">Main Image</label></th>
					<td><div class="image_picker">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="main_image" name="main_image" class="imgpck_field" _member_ids="yes" />
					</div></td>
				</tr>
				<tr>
					<th><label for="intro_text">Intro Copy (Yellow Text)</label></th>
					<td><textarea id="intro_text" name="intro_text"></textarea></td>
				</tr>
			<?php elseif ( $oParent && ( 'page_product_detail.php' == $oPage->getPageTemplate() ) ):
			
				$aBrands = new Gloc_Post_Query( array(
					'category__in' => array(
						Geko_Wp_Category::get_ID( 'brands' )
					),
					'showposts' => -1,
					'posts_per_page' => -1,
					'orderby' => 'title',
					'order' => 'ASC'
				), FALSE );
				
			?>
				<tr>
					<th><label for="main_image">Main Image</label></th>
					<td><div class="image_picker">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="main_image" name="main_image" class="imgpck_field" _member_ids="yes" />
					</div></td>
				</tr>
				<tr>
					<th><label for="slideshow_images">Slideshow Images</label></th>
					<td><div class="image_picker multi">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="slideshow_images" name="slideshow_images" class="imgpck_field" _member_ids="yes" />
					</div></td>
				</tr>
				<tr>
					<th><label for="intro_text">Intro Copy (Yellow Text)</label></th>
					<td><textarea id="intro_text" name="intro_text"></textarea></td>
				</tr>
				<tr>
					<th><label for="gallery_images">Gallery Images</label></th>
					<td><div class="image_picker multi">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="gallery_images" name="gallery_images" class="imgpck_field" />
					</div></td>
				</tr>
				<tr>
					<th><label for="brand_logos">Brands</label></th>
					<td><select id="brand_logos" class="selmulti" name="brand_logos" multiple="multiple">
						<?php echo $aBrands->implode( '<option value="##Id##">##Title##</option>' ); ?>
					</select></td>
				</tr>
				<tr>
					<th><label for="more_info">More Information Box</label></th>
					<td><textarea id="more_info" name="more_info"></textarea></td>
				</tr>
			<?php elseif ( $oPage && ( 'page_form.php' == $oPage->getPageTemplate() ) ):
				
				$aForms = new Geko_Wp_Form_Query( array(
					'showposts' => -1,
					'posts_per_page' => -1
				), FALSE );
				
				?><tr>
					<th><label for="form">Form</label></th>
					<td><select id="form" name="form">
						<option value="">- Select -</option>
						<?php echo $aForms->implode( array( '<option value="##Id##">##Title##</option>', '' ) ); ?>
					</select></td>
				</tr>
				
			<?php endif; ?>
		</table>
		<?php
	}
	
	//// helpers
	
	//
	public function formatMimeType( $sValue ) {
		$sValue = str_replace(
			array(
				'application/pdf'
			),
			array(
				'PDF'
			),
			$sValue
		);
		return $sValue;
	}
	
	//
	public function echoImagePickerItems( $aImages ) {
		$aThumbParams = array( 'w' => 75, 'h' => 75 );
		foreach ( $aImages as $oAtt ): ?>
			<a href="<?php $oAtt->echoUrl(); ?>" title="<?php $oAtt->escechoTitle(); ?>" id="<?php $oAtt->echoId(); ?>">
				<img src="<?php $oAtt->echoTheImageUrl( $aThumbParams ); ?>" width="75" height="75" />
			</a><?php
		endforeach;
	}
	
}


