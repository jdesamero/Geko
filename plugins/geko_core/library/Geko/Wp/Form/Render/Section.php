<?php

//
class Geko_Wp_Form_Render_Section extends Geko_Singleton_Abstract
{
	
	//
	public function render( $oSection, $aItems, $aValues, $aResponses, $aParams = array() ) {
		
		$sListType = ( $aParams[ 'list_type' ] ) ? $aParams[ 'list_type' ] : 'ol' ;
		
		$iSecId = $oSection->getId();
		$aItemsSec = $aItems->subsetSectionId( $iSecId );
		$aValsSec = $aValues->subsetSectionId( $iSecId );
		
		$sSecContent = trim( $oSection->getContent() );
		
		?>
		<div id="<?php $oSection->echoElemId(); ?>" class="geko-form-section">
		
			<h2><?php $oSection->echoTitle(); ?></h2>
			
			<?php if ( $sSecContent ): ?>
				<p><em><?php echo $sSecContent; ?></em></p>
			<?php endif; ?>
			
			<?php
			
			$aOlAtts = array(
				'id' => $oSection->getSlug(),
				'class' => 'geko-form-section'
			);
			
			$oOl = _ge( $sListType, $aOlAtts );

			$oItemsRender = Geko_Wp_Form_Render_Items::getInstance();
			$oItemsRender->render( $oOl, $aItemsSec, $aValsSec, $aResponses, $aParams );
			
			?>
			
		</div>
		<?php
		
	}

}


