<?php

//
class Geko_Wp_Author_Query extends Geko_Wp_User_Query
{
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery
			->joinLeft( '##pfx##usermeta', 'ul' )
				->on( 'ul.user_id = u.ID' )
				->on( 'ul.meta_key = ?', '##pfx##user_level' )
			->where( 'ul.meta_value != ?', '0' )
		;
		
		return $oQuery;
	}
}

