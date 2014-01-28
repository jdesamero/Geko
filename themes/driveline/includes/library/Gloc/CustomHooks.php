<?php

//
class Gloc_CustomHooks extends Geko_Singleton_Abstract
{
	
	
	//
	public function start() {
		
		parent::start();
		
		add_filter( 'Geko_Wp_Ext_StoreLocatorPlus_AjaxHandler::search_query', array( $this, 'slpQuery' ), 12, 2 );
		add_filter( 'Geko_Wp_Ext_StoreLocatorPlus_AjaxHandler::search_result', array( $this, 'slpResult' ), 12, 2 );
		
	}
	
	
	//
	public function slpQuery( $oQuery, $aParams ) {
		
		// sl_store
		// sl_description
		
		// sl_url
		// sl_pages_url
		
		// sl_email
		// sl_phone
		// sl_fax
		// sl_hours
		// sl_image
		
		// sl_tags
		// sl_option_value (json)
		
		global $wpdb;

		$oPm = Gloc_Post_Meta::getInstance();
		
		$oQuery
			
			->field( 'p.ID', 'post_id' )
			->field( 'p.post_title', 'sl_store' )
			
			->field( 'pm1.meta_value', 'sl_email' )
			->field( 'pm2.meta_value', 'sl_phone' )
			->field( 'pm3.meta_value', 'sl_fax' )
			->field( 'pm4.meta_value', 'sl_url' )
			->field( 'pm5.meta_value', 'sl_hours' )
			->field( 'pm6.meta_value', 'sl_description' )
			
			// ->field( '"http://apple.com"', 'sl_pages_url' )
			// ->field( '"http://driveproducts.com"', 'sl_url' )
			
			->joinLeft( $wpdb->posts, 'p' )
				->on( 'p.ID = la.object_id' )
				->on( 'la.objtype_id = ?', Geko_Wp_Options_MetaKey::getId( 'post' ) )
			
			->joinLeft( $wpdb->postmeta, 'pm1' )
				->on( 'pm1.post_id = p.ID' )
				->on( 'pm1.meta_key = ?', $oPm->applyPrefix( 'email' ) )

			->joinLeft( $wpdb->postmeta, 'pm2' )
				->on( 'pm2.post_id = p.ID' )
				->on( 'pm2.meta_key = ?', $oPm->applyPrefix( 'phone' ) )

			->joinLeft( $wpdb->postmeta, 'pm3' )
				->on( 'pm3.post_id = p.ID' )
				->on( 'pm3.meta_key = ?', $oPm->applyPrefix( 'fax' ) )

			->joinLeft( $wpdb->postmeta, 'pm4' )
				->on( 'pm4.post_id = p.ID' )
				->on( 'pm4.meta_key = ?', $oPm->applyPrefix( 'website' ) )
				
			->joinLeft( $wpdb->postmeta, 'pm5' )
				->on( 'pm5.post_id = p.ID' )
				->on( 'pm5.meta_key = ?', $oPm->applyPrefix( 'hours' ) )

			->joinLeft( $wpdb->postmeta, 'pm6' )
				->on( 'pm6.post_id = p.ID' )
				->on( 'pm6.meta_key = ?', $oPm->applyPrefix( 'description' ) )
			
			->where( 'p.post_type = ?', 'post' )
			
		;
		
		return $oQuery;
	}
	
	//
	public function slpResult( $aResult, $aParams ) {
		
		foreach ( $aResult as $i => $aRow ) {
			
			$iPostId = $aRow[ 'post_id' ];
			
			$aResult[ $i ][ 'sl_option_value' ] = maybe_serialize( array(
				'post_id' => $iPostId,
				'details_page_url' => get_permalink( $iPostId )
			) );
		}
		
		return $aResult;
	}

}

