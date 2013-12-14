<?php

// static class author related stuff
// wrapper for the sem_author_image plugin
// object oriented wrapper for a $user object
class Geko_Wp_Author extends Geko_Wp_User
{	
	//// static methods
	
	//
	public static function get_all($params = '', $aQueryParams = array())
	{
		global $wpdb;
		
		$aParams = array();
		parse_str($params, $aParams);
		
		// query the thumbnails
		$sQuery = "
			SELECT
				" . $aQueryParams['field'] . "
				u.ID,
				u.user_email,
				u.user_login,
				umfn.meta_value AS first_name,
				umln.meta_value AS last_name,
				umdesc.meta_value AS description,
				(SELECT COUNT(*) FROM $wpdb->posts p WHERE (p.post_author = u.ID) AND (p.post_type = 'post')) AS num_posts,
				UNIX_TIMESTAMP((SELECT MAX(p.post_date) FROM $wpdb->posts p WHERE (p.post_author = u.ID) AND (p.post_type = 'post'))) AS latest_post_date_uts
			FROM
				$wpdb->users u
			LEFT JOIN
				$wpdb->usermeta umfn
				ON (u.ID = umfn.user_ID) AND (umfn.meta_key = 'first_name')
			LEFT JOIN
				$wpdb->usermeta umln
				ON (u.ID = umln.user_ID) AND (umln.meta_key = 'last_name')
			LEFT JOIN
				$wpdb->usermeta umdesc
				ON (u.ID = umdesc.user_ID) AND (umdesc.meta_key = 'description')
				" . $aQueryParams['join'] . "
			WHERE
				" . $aQueryParams['where'] . "
				u.user_login <> 'admin' " . 
			
			( ( $aParams['show_with_zero_posts'] ) ? '' : "HAVING
				num_posts > 0") . 
			
			"ORDER BY
				" . $aQueryParams['order'] . "
				last_name ASC
		";
		
		$aRes = $wpdb->get_results($sQuery, ARRAY_A);
		
		if ( !is_array( $aRes ) )  {
			$aRes = array();
		}
		
		// transform the array
		foreach ($aRes as $i => $aRow) {
			
			if ($sAuthImgSrc = self::get_author_image(TRUE, $aRow['user_login'])) {
				$aRes[$i]['img_src'] = $sAuthImgSrc;
			}
			
			if ($aParams['use_only_first_names_in_desc']) {
				$aRes[$i]['description'] = str_replace(
					$aRow['first_name'] . ' ' . $aRow['last_name'],
					$aRow['first_name'],
					$aRes[$i]['description']
				);
			}
			
		}
		
		return $aRes;
	}
	
	
	//
	public static function get_author_image($bSrc = FALSE, $iAuthorId = NULL)
	{
		if (class_exists('sem_author_image')) {
			return sem_author_image::get($bSrc, $iAuthorId);
		} elseif (class_exists('author_image')) {
			return author_image::get($bSrc, $iAuthorId);		
		} else {
			return NULL;
		}
	}
	
	
	
	//// implement concrete methods for author
	
	//
	public function getDefaultEntityValue()
	{
		global $wp_query;
		
		if ( is_author() ) {
			return $wp_query->query_vars['author_name'];
		}
		
		return NULL;
	}
	
	//
	public function retPermalink()
	{
		return get_author_posts_url( $this->getId() );
	}
	
	
}


