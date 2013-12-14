<?php

//
class Geko_Wp_Category_Template extends Geko_Wp_Category_Meta
{
	protected $sTemplate;
	
	
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
			
			$iCatId = intval( get_query_var('cat') );
			$sTemplate = $this->getTemplate( $iCatId );
			
			if (
				$sTemplate &&
				( $sTemplatePath = realpath( TEMPLATEPATH . '/' . $sTemplate ) ) 
			) {
				$this->sTemplate = $sTemplate;
				include(  $sTemplatePath );
				die();
			}
		}
	}
	
	
	//// accessors
	
	//
	public function getTemplate( $iCatId = NULL ) {
		
		if ( NULL === $iCatId ) {
			return $this->sTemplate;
		} else {
			return $this->getInheritedValue(
				$iCatId,
				$this->getPrefixWithSep() . 'category_template'
			);
		}
	}
	
	//
	public function getTemplates() {
		$oTmpl = Geko_Wp_Template::getInstance();
		return $oTmpl->getTemplateValues( array(
			'prefix' => $this->getPrefixWithSep() . 'category-template',
			'attribute_name' => 'Category Template'
		) );
	}
	
	//
	public function isTemplate( $sTemplate ) {
		return ( $this->sTemplate == $sTemplate );
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


