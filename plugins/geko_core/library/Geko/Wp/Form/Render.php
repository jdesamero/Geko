<?php

//
class Geko_Wp_Form_Render extends Geko_Singleton_Abstract
{
	
	protected $_oForm;
	protected $_aSections;
	protected $_aItems;
	protected $_aItemValues;
	
	protected $_iFormId;
	
	
	//
	public function setForm( $oForm ) {
		
		$this->_oForm = $oForm;
		$this->_aSections = $oForm->getFormSections();
		$this->_aItems = $oForm->getFormItems();
		$this->_aItemValues = $oForm->getFormItemValues();
		
		return $this;
		
	}
	
	//
	public function render( $aParams = array() ) {
		
		if ( !isset( $aParams[ 'show_sections' ] ) ) $aParams[ 'show_sections' ] = TRUE;
		if ( !isset( $aParams[ 'show_main_title' ] ) ) $aParams[ 'show_main_title' ] = TRUE;
		
		$bSections = $aParams[ 'show_sections' ];
		$bTabbed = $aParams[ 'tabbed' ];
		$bShowMainTitle = $aParams[ 'show_main_title' ];
		$bDisableSave = $aParams[ 'disable_save' ];
		$aResponses = $aParams[ 'responses' ];
		$aHiddenValues = $aParams[ 'hidden_values' ];
		$sListType = ( $aParams[ 'list_type' ] ) ? $aParams[ 'list_type' ] : 'ol' ;
		
		$aItemTypes = Geko_Wp_Form::getItemTypes();
		
		$oForm = $this->_oForm;
		$aSections = $this->_aSections;
		$aItems = $this->_aItems;
		$aItemValues = $this->_aItemValues;
		
		?>
		<form id="<?php $oForm->echoElemId(); ?>" class="geko-form">
			
			<div class="loading"><img src="<?php bloginfo( 'template_directory' ); ?>/images/loader.gif" /></div>
			<div class="error"></div>
			<div class="success"></div>
			
			<?php if ( $bShowMainTitle ): ?>
				<h1><?php $oForm->echoTitle(); ?></h1>
			<?php endif; ?>
			
			<?php if ( $bSections ):
				if ( $bTabbed ): ?>
					<div class="geko-form-tabs">
						<ul class="geko-tab-controls">
							<?php foreach ( $aSections as $oSection ): ?>
								<li><a href="#<?php $oSection->echoElemId(); ?>" class="geko-form-tab"><?php $oSection->echoTitle(); ?></a></li>
							<?php endforeach; ?>					
						</ul>
						<?php foreach ( $aSections as $oSection ):
							$oSectionRender = Geko_Wp_Form_Render_Section::getInstance();
							$oSectionRender->render( $oSection, $aItems, $aItemValues, $aResponses, $aParams );
						endforeach; ?>					
					</div>
				<?php else:
					foreach ( $aSections as $oSection ):
						$oSectionRender = Geko_Wp_Form_Render_Section::getInstance();
						$oSectionRender->render( $oSection, $aItems, $aItemValues, $aResponses, $aParams );
					endforeach;
				endif;
			else:
			
				$aOlAtts = array( 'class' => 'geko-form-no-section' );
				
				$oOl = _ge( $sListType, $aOlAtts );
				
				$oItemsRender = Geko_Wp_Form_Render_Items::getInstance();
				$oItemsRender->render( $oOl, $aItems, $aItemValues, $aResponses, $aParams );
				
			endif;
			
			foreach ( $aHiddenValues as $sKey => $sValue ) {
				$oInput = _ge( 'input', array(
					'type' => 'hidden',
					'name' => $sKey,
					'value' => $sValue
				) );
				echo strval( $oInput );
			}
			
			?>
			
			<div class="geko-form-buttons">
				
				<?php if ( $bSections ): ?>
					
					<input type="button" id="geko-form-prev" value="Prev" />
					<input type="button" id="geko-form-next" value="Next" />
					
					<div class="geko-form-spacer spacer-1"></div>
					
				<?php endif; ?>
				
				<input type="reset" value="Reset" /> 
				
				<?php if ( !$bDisableSave ): ?>
					<input type="button" value="Save and Complete Later" class="geko-form-save" />
				<?php endif; ?>
				
				<div class="geko-form-spacer spacer-2"></div>
				
				<input type="submit" value="Submit" class="geko-form-submit" />
				
			</div>
			
		</form>
		<?php
		
	}
	
}

