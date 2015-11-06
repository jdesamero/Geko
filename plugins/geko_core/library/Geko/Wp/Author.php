<?php
/*
 * "geko_core/library/Geko/Wp/Author.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * static class author related stuff
 * wrapper for the sem_author_image plugin
 * object oriented wrapper for a $user object
 */

//
class Geko_Wp_Author extends Geko_Wp_User
{	
	//// static methods
	
	//
	public static function get_all( $params = '', $aQueryParams = array() ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aParams = array();
		parse_str( $params, $aParams );
		
		$oNumPostsQuery = new Geko_Sql_Select();
		$oNumPostsQuery
			->field( 'COUNT(*)' )
			->from( '##pfx##posts', 'p' )
			->where( 'p.post_author = u.ID' )
			->where( 'p.post_type = ?', 'post' )
		;
		
		$oLatestPostQuery = new Geko_Sql_Select();
		$oLatestPostQuery
			->field( 'MAX(p.post_date)' )
			->from( '##pfx##posts', 'p' )
			->where( 'p.post_author = u.ID' )
			->where( 'p.post_type = ?', 'post' )
		;
		
		$oQuery = new Geko_Sql_Select();
		$oQuery
			
			->field( 'u.ID' )
			->field( 'u.user_email' )
			->field( 'u.user_login' )
			->field( 'umfn.meta_value', 'first_name' )
			->field( 'umln.meta_value', 'last_name' )
			->field( 'umdesc.meta_value', 'description' )
			
			->field( $oNumPostsQuery, 'num_posts' )
			->field( array( 'UNIX_TIMESTAMP(?)', $oLatestPostQuery ), 'latest_post_date_uts' )
			
			->from( '##pfx##users', 'u' )
			
			->joinLeft( '##pfx##usermeta', 'umfn' )
				->on( 'u.ID = umfn.user_ID' )
				->on( 'umfn.meta_key = ?', 'first_name' )
			
			->joinLeft( '##pfx##usermeta', 'umln' )
				->on( 'u.ID = umln.user_ID' )
				->on( 'umln.meta_key = ?', 'last_name' )
			
			->joinLeft( '##pfx##usermeta', 'umdesc' )
				->on( 'u.ID = umdesc.user_ID' )
				->on( 'umdesc.meta_key = ?', 'description' )
		;
		
		$oQuery = apply_filters( sprintf( '%s::query', __METHOD__ ), $oQuery );
		
		echo strval( $oQuery );
		
		$aRes = $oDb->fetchAllAssoc( strval( $oQuery ) );
		
		if ( !is_array( $aRes ) )  {
			$aRes = array();
		}
		
		// transform the array
		foreach ( $aRes as $i => $aRow ) {
			
			if ( $sAuthImgSrc = self::get_author_image( TRUE, $aRow[ 'user_login' ] ) ) {
				$aRes[ $i ][ 'img_src' ] = $sAuthImgSrc;
			}
			
			if ( $aParams[ 'use_only_first_names_in_desc' ] ) {
				$aRes[ $i ][ 'description' ] = str_replace(
					sprintf( '%s %s', $aRow[ 'first_name' ], $aRow[ 'last_name' ] ),
					$aRow[ 'first_name' ],
					$aRes[ $i ][ 'description' ]
				);
			}
			
		}
		
		return $aRes;
	}
	
	
	//
	public static function get_author_image( $bSrc = FALSE, $iAuthorId = NULL ) {
		
		if ( class_exists( 'sem_author_image' ) ) {
			return sem_author_image::get( $bSrc, $iAuthorId );
		} elseif ( class_exists( 'author_image' ) ) {
			return author_image::get( $bSrc, $iAuthorId );		
		} else {
			return NULL;
		}
	}
	
	
	
	//// implement concrete methods for author
	
	//
	public function getDefaultEntityValue() {
		
		global $wp_query;
		
		if ( is_author() ) {
			return $wp_query->query_vars[ 'author_name' ];
		}
		
		return NULL;
	}
	
	//
	public function retPermalink() {
		return get_author_posts_url( $this->getId() );
	}
	
	
}


