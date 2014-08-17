<?php

//
class Geko_Wp_Category_Template extends Geko_Wp_Category_Meta
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
		
		if ( is_category() ) {
			
			$iCatId = intval( get_query_var( 'cat' ) );
			$sTemplate = $this->getTemplate( $iCatId );
			
			if (
				$sTemplate &&
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
				sprintf( '%scategory_template', $this->getPrefixWithSep() )
			);
		}
	}
	
	//
	public function getTemplates() {
		$oTmpl = Geko_Wp_Template::getInstance();
		return $oTmpl->getTemplateValues( array(
			'prefix' => sprintf( '%scategory-template', $this->getPrefixWithSep() ),
			'attribute_name' => 'Category Template'
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
			<label class="main">Category Template</label> 
			<select id="category_template" name="category_template">
				<option value="">Default Template</option>
				<?php foreach ( $aTemplates as $sTemplateName => $sTemplateFile ): ?>
					<option value="<?php echo $sTemplateFile; ?>"><?php echo $sTemplateName; ?></option>
				<?php endforeach; ?>
			</select>
			<label class="description">Specify a template file for the category.</label>
		</p>			
		<?php
	}
	
}


