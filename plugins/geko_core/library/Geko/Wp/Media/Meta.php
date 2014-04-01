<?php

//
class Geko_Wp_Media_Meta extends Geko_Wp_Options_Meta
{
	
	
	//// init
	
	//
	public function addAdmin() {
		
		parent::addAdmin();
		
		add_filter( 'admin_media_edit_fields_pq', array( $this, 'setupEditFields' ) );
		
		return $this;
	}	
	
	
	
	//// form processing/injection methods
	
	// plug into the edit category form
	public function setupEditFields( $oCatDoc ) {
		
		$aParts = $this->extractParts();
		$sFields = '';
		
		foreach ( $aParts as $aPart ) {
			
			$sLabel = ( $aPart[ 'label' ] ) ? '<label for="' . $aPart[ 'name' ] . '">' . $aPart[ 'label' ] . '</label>' : '' ;
			$sFieldGroup = $aPart[ 'field_group' ];
			$sDescription = ( $aPart[ 'description' ] ) ? '<span class="description">' . $aPart[ 'description' ] . '</span>' : '' ;
			
			$sFields .= '
				<tr class="form-field">
					<th scope="row" valign="top">' . $sLabel . '</th>
					<td>
						' . $sFieldGroup . '<br />
						' . $sDescription . '
					</td>
				</tr>
			';
		}
		
		Geko_PhpQuery::last( $oCatDoc[ 'div.media-item .form-table tr' ] )->after( $sFields );
		
		return $oCatDoc;
	}
	
	
}



