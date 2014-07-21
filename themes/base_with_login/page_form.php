<?php
/*
Template Name: Form
*/

//
class Gloc_Layout_PageForm extends Gloc_Layout
{
	
	protected $oPage = NULL;
	protected $oForm = NULL; 
	
	
	
	//
	public function start() {
		
		$oPage = $this->newPage();
		
		$iFormId = intval( $oPage->getMeta( 'form' ) );
		
		if ( $iFormId ) {
		
			$oForm = $this->newForm( $iFormId );
			
			if ( $oForm->isValid() ) {
				$this->oForm = $oForm;
			}
		}
		
		$this->oPage = $oPage;
	}
	
	
	//
	public function echoEnqueue() {
		$this->enqueueScript( 'geko_wp_form_render' );
	}
	
	//
	public function echoHeadLate() {
		
		$oForm = $this->oForm;
		
		if ( !$oForm ) return;
		
		$aItems = $oForm->getFormItems();
		$aSections = $oForm->getFormSections();
		
		$aItemTypes = Geko_Wp_Form::getItemTypes();
		
		
		// construct js arrays
		$aValidation = array();				// validation array
		$aJsResponse = array();				// js response array
		
		foreach ( $aSections as $oSection ) {
			
			$aItemSec = $aItems->subsetSectionId( $oSection->getId() );
			$aItemValid = array();
			
			foreach ( $aItemSec as $oItem ) {
				
				// validation array
				$aItemValid[ $oItem->getElemId() ] = array(
					'question' => $oItem->getTitle(),
					'type' => $oItem->getItemType(),
					'validation' => $oItem->getTheValidation()
				);
				
			}
			
			$aValidation[] = $aItemValid;
		}
		
		// json params
		$aJsonParams = array(
			'script' => $this->getScriptUrls(),
			'status' => array(
				'success' => Gloc_Service_Form::STAT_SUCCESS
			),
			'validation' => $aValidation,
			'responses' => $aJsResponse
		);
		
		?>
		<script type="text/javascript">
			
			jQuery( document ).ready( function( $ ) {
				
				var oParams = <?php echo Zend_Json::encode( $aJsonParams ); ?>;
				
				var form = $( 'form.geko-form' );
				
				form.gekoWpFormRender( $.extend( {
					// add params if needed
				}, oParams ) );
				
			} );
			
		</script>
		<?php
	}
	
	
	//
	public function echoContent() {
		
		$oForm = $this->oForm;
		$oPage = $this->oPage;
		
		// TO DO: implement placeholders in content
		// $oForm->getTitle();
		
		?>
		
		<div id="content">
			<div class="breadcrumbs"><?php $this->doNavBreadcrumb( 'main' ); ?></div>
			<div id="post-<?php $oPage->echoId(); ?>" class="<?php echo $this->applyPostClass(''); ?>">
				
				<h1><?php $oPage->echoTitle(); ?></h1>
				
				<?php if ( $oForm ): ?>
					<a name="form_top"></a>
					
					<?php $oPage->echoTheContent(); ?>
					
					<div class="entry-content">
						<?php
						
						$oFormRender = $oForm->getRenderer();
						$oFormRender->render( array(
							'show_sections' => FALSE,
							'tabbed' => FALSE,
							'show_main_title' => FALSE,
							'list_type' => 'ul',
							'disable_save' => TRUE,
							'hidden_values' => array(
								'form_id' => $oForm->getId(),
								'subaction' => 'submit'
							)
						) ); ?>
					</div>
					<div id="successdiv">
						<p>Thank you for completing the form!</p>
					</div>
				
				<?php else: ?>
					
					<p>Invalid form specified!</p>
					
				<?php endif; ?>
				
				<div class="entry-content">
					<?php $this->doLinkPages(); ?>
					<?php $this->pw( '<span class="edit-link">%s</span>', $oPage->getTheEditLink() ); ?>
				</div>
			</div>
			<div class="breadcrumbs"><?php $this->doNavBreadcrumb( 'main' ); ?></div>
		</div>
		
		
		<?php
		
		// Add a key+value of "comments" to enable comments on this page
		if ( $oPage->getMeta( 'comments' ) ) $this->doCommentsTemplate();
		
	}
	
	
	
}

geko_render_template();

