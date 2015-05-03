<?php

//
class Geko_Wp_Media_Query extends Geko_Wp_Post_Query
{
	
	// private static $bInitCalled = FALSE;
	
	
	// implement by sub-class to process $aParams
	public function modifyParams( $aParams ) {
		
		$aParams = parent::modifyParams( $aParams );
		
		/* /
		if ( !self::$bInitCalled ) {
			Geko_Wp_Media_QueryHooks::register();
			self::$bInitCalled = TRUE;
		}
		/* */
		
		$aParams = array_merge(
			$aParams,
			array( 'post_files' => 1 )
		);
		
		return $aParams;
	}
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		if ( $aParams[ 'post_files' ] ) {
			
			$oDb = Geko_Wp::get( 'db' );
			
			$oQuery
				
				->field( 'gkpf.ID', 'file_id' )
				->field( 'gkpf.post_title', 'file_title' )
				->field( 'gkpf.post_name', 'file_name' )
				->field( 'gkpf.post_content', 'file_description' )
				->field( 'gkpf.post_excerpt', 'file_desc_excerpt' )
				->field( 'gkpf.post_date', 'file_created' )
				->field( 'gkpf.post_modified', 'file_modified' )
				->field( 'gkpf.post_date_gmt', 'file_created_gmt' )
				->field( 'gkpf.post_modified_gmt', 'file_modified_gmt' )
				->field( 'gkpf.post_mime_type', 'file_mime_type' )
				->field( 'gkpf.menu_order', 'file_menu_order' )
				->field( 'gkia.meta_value', 'file_alt_text' )
				
				->joinInner( '##pfx##posts', 'gkpf' )
					->on( 'gkpf.post_parent = p.ID' )
					->on( 'gkpf.post_type = ?', 'attachment' )
				
				->joinLeft( '##pfx##postmeta', 'gkia' )
					->on( 'gkia.post_id = gkpf.ID' )
					->on( 'gkia.meta_key = ?', '_wp_attachment_image_alt' )
				
			;
			
			
			if ( $sContentLike = $aParams[ 'content_like' ] ) {
				$oQuery->where( sprintf( "gkpf.post_content LIKE '%%%s%%'", $oDb->quote( $sContentLike ) ) );
			}
			
			if ( $sContentNotLike = $aParams[ 'content_not_like' ] ) {
				$oQuery->where( sprintf( "gkpf.post_content NOT LIKE '%%%s%%'", $oDb->quote( $sContentNotLike ) ) );			
			}
			
			if ( $sExcerptLike = $aParams[ 'excerpt_like' ] ) {
				$oQuery->where( sprintf( "gkpf.post_excerpt LIKE '%%%s%%'", $oDb->quote( $sExcerptLike ) ) );
			}
			
			if ( $sExcerptNotLike = $aParams[ 'excerpt_not_like' ] ) {
				$oQuery->where( sprintf( "gkpf.post_excerpt NOT LIKE '%%%s%%'", $oDb->quote( $sExcerptNotLike ) ) );
			}
			
			if ( $aParams[ 'images_only' ] ) {
				$oQuery->where( "1 = LOCATE( 'image', gkpf.post_mime_type )" );
			}
			
			if ( $aParams[ 'non_images_only' ] ) {
				$oQuery->where( "0 = LOCATE( 'image', gkpf.post_mime_type )" );			
			}
			
			if ( $aParams[ 'has_file_ids' ] || $aParams[ 'file_ids' ] ) {
				$oQuery->where( 'gkpf.ID * ($)', $aParams[ 'file_ids' ] );
			}
			
			if ( $sOrderBy = $aParams[ 'orderby' ] ) {
				$sOrder = ( strtoupper( $aParams[ 'order' ] ) == 'DESC' ) ? 'DESC' : 'ASC' ;
				$oQuery->order( $sOrderBy, $sOrder );
			}
			
		}
		
		
		return $oQuery;
	}
	
	
}


