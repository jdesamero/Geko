<?php

//
class Geko_Wp_Category_PostTemplate extends Geko_Wp_Category_Meta
{
	
	protected $_sTemplate;
	
	
	
	//// init
	
	//
	public function addTheme() {
		
		parent::addTheme();
		
		add_action( 'template_redirect', array( $this, 'templateRedirect' ) );
		
		return $this;
	}
	
	//
	public function templateRedirect() {
		
		if ( is_single() ) {
			
			global $post;
			
			$iActualCatId = FALSE;
			
			$oPost = new Geko_Wp_Post( $post );
			$iActualCatId = $oPost->getCategory()->getId();
			
			$iActualCatId = apply_filters( sprintf( '%s::actualCatId', __METHOD__ ), $iActualCatId, $oPost, $this );
			
			// see if there is a matching template
			if (
				$iActualCatId && 
				( $sTemplate = $this->getTemplate( $iActualCatId ) ) &&
				( $sTemplatePath = realpath( sprintf( '%s/%s', TEMPLATEPATH, $sTemplate ) ) ) 
			) {
				$this->_sTemplate = $sTemplate;
				include( apply_filters( 'template_include', $sTemplatePath ) );
				die();
			}
		}
	}
	
	
	//// accessors
	
	//
	public function getTemplate( $iCatId = NULL ) {
		
		if ( NULL === $iCatId ) {
			return $this->_sTemplate;
		} else {
			return $this->getInheritedValue(
				$iCatId,
				sprintf( '%scategory_post_template', $this->getPrefixWithSep() )
			);
		}
	}
	
	//
	public function getTemplates() {
		$oTmpl = Geko_Wp_Template::getInstance();
		return $oTmpl->getTemplateValues( array(
			'prefix' => sprintf( '%scategory-post-template', $this->getPrefixWithSep() ),
			'attribute_name' => 'Category Post Template'
		) );
	}
	
	//
	public function isTemplate( $sTemplate ) {
		return ( $this->_sTemplate == $sTemplate );
	}
	
	
	//// front-end display methods
	
	//
	public function formFields() {
		
		$aTemplates = $this->getTemplates();
		
		?>
		<p _is_inheritable="yes">
			<label class="main">Category Post Template</label> 
			<select id="category_post_template" name="category_post_template">
				<option value="">Default Template</option>
				<?php foreach ( $aTemplates as $sTemplateName => $sTemplateFile ): ?>
					<option value="<?php echo $sTemplateFile; ?>"><?php echo $sTemplateName; ?></option>
				<?php endforeach; ?>
			</select>
			<label class="description">Specify a post template file for the category.</label>
		</p>			
		<?php
	}
	
}


