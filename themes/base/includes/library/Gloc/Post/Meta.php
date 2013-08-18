<?php

class Gloc_Post_Meta extends Geko_Wp_Post_Meta
{
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		wp_enqueue_script( 'geko-jquery-geko_image_picker' );
		
		return $this;
	}
	
	
	//
	protected function preFormFields() {
		
		$oPost = $this->getCurPost();
		
		$aJsonParams = array(
			'url' => get_bloginfo( 'url' ),
			'prefix' => $this->getPrefixWithSep()
		);
		
		?>
		<style type="text/css">
			
			.fix {
				clear: both;
				height: 1px;
				margin: 0 0 -1px 0;
				overflow: hidden;
			}
			
			#wp_post_meta table.fields {
				width: 100%;
			}
			
			#wp_post_meta table.fields input[type=text] {
				width: 95%;
			}

			#wp_post_meta table.fields input[type=text].short {
				width: 30%;
			}
			
			#wp_post_meta table.fields textarea {
				width: 95%;
				height: 150px;
			}
			
			/* ---------------------------------------------------------------------------------- */
			
			#wp_post_meta .image_picker {
				border: solid 1px #dfdfdf;		
				padding: 5px;
				width: 346px;
				height: 163px;
				overflow: auto;
				-moz-border-radius: 5px;
				border-radius: 5px;
			}
			
			#wp_post_meta .image_picker img {
				border: solid 2px #dfdfdf;
			}
			
			#wp_post_meta .image_picker img.ip_not_selected {
				border: solid 2px #fff;
			}
			
			#wp_post_meta .image_picker img.ip_selected {
				border: solid 2px red;
			}
			
			#wp_post_meta .selmulti {
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
		
		$oPost = $this->getCurPost();
		if ( $oPost ) {
			$aImages = $oPost->getAttachments( array( 'images_only' => 1, 'orderby' => 'file_menu_order' ) );
		}
		
		?>
		<table class="fields">
			<?php if ( FALSE ): ?>
				<tr>
					<th><label for="slideshow_images">Slideshow Images</label></th>
					<td><div class="image_picker multi">
						<?php $this->echoImagePickerItems( $aImages ); ?>
						<input type="hidden" id="slideshow_images" name="slideshow_images" class="imgpck_field" _member_ids="yes" />
					</div></td>
				</tr>
			<?php endif; ?>
		</table>
		<?php
		
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

