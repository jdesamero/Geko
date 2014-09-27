<?php

//
class Geko_Wp_Enumeration_Query extends Geko_Wp_Entity_Query
{
	
	protected static $aCache = array();
	
	protected $_bUseManageQuery = TRUE;
	
	
	
	//
	public static function getSet( $sParentSlug ) {
		
		if ( !self::$aCache[ $sParentSlug ] ) {
			
			$sQueryClass = get_called_class();
			self::$aCache[ $sParentSlug ] = new $sQueryClass( array(
				'parent_slug' => $sParentSlug,
				'showposts' => -1,
				'posts_per_page' => -1
			), FALSE );
		
		}
		
		return self::$aCache[ $sParentSlug ];
	}
	
	//
	public static function getJoinQuery( $sParentSlug, $sAlias, $aFields = NULL ) {
		
		if ( NULL === $aFields ) {
			$aFields = array( 'enum_id', 'title', 'slug', 'value' );
		} elseif ( 'ALL' == $aFields ) {
			// magic param
			$aFields = array( 'enum_id', 'title', 'slug', 'value', 'description', 'params', 'rank' );
		}
		
		$sEnAlias = sprintf( '%s_en', $sAlias );
		$sEpAlias = sprintf( '%s_ep', $sAlias );
		
		$oQuery = new Geko_Sql_Select();
		
		foreach ( $aFields as $sField ) {
			$oQuery->field( sprintf( '%s.%s', $sEnAlias, $sField ) );
		}
		
		$oQuery
			->from( '##pfx##geko_enumeration', $sEnAlias )
			->joinLeft( '##pfx##geko_enumeration', $sEpAlias )
				->on( sprintf( '%s.enum_id = %s.parent_id', $sEpAlias, $sEnAlias ) )
			->where( sprintf( '%s.slug = ?', $sEpAlias ), $sParentSlug )
		;
		
		return $oQuery;
	}
	
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		
		
		// enum id
		if ( isset( $aParams[ 'geko_enum_id' ] ) ) {
			$oQuery->where( 'e.enum_id * ($)', $aParams[ 'geko_enum_id' ] );
		}
		
		// enum slug
		if ( isset( $aParams[ 'geko_enum_slug' ] ) ) {
			$oQuery->where( 'e.slug = ?', $aParams[ 'geko_enum_slug' ] );
		}
		
		
		
		// parent_id
		if ( $aParams[ 'parent_id' ] ) {
			$oQuery->where( 'e.parent_id = ?', $aParams[ 'parent_id' ] );
		}
		
		// parent_slug
		if ( $aParams[ 'parent_slug' ] ) {
			$oQuery
				->joinLeft( '##pfx##geko_enumeration', 'ep' )
					->on( 'ep.enum_id = e.parent_id' )
				->where( 'ep.slug = ?', $aParams[ 'parent_slug' ] )
			;
		}
		
		//
		if ( $aParams[ 'is_root' ] ) {
			$oQuery->where( '( e.parent_id IS NULL ) OR ( 0 = e.parent_id )' );
		}
		
		
		
		// apply default sorting
		if ( !isset( $aParams[ 'orderby' ] ) ) {
			$oQuery
				->order( 'e.rank', 'ASC', 'rank' )
				->order( 'e.title', 'ASC', 'title' )
			;
		}
		
		return $oQuery;
	}
	
	
	
	
	//
	public function getParent() {
		return new $this->_sEntityClass( Geko_String::coalesce(
			$this->_aParams[ 'parent_id' ], $this->_aParams[ 'parent_slug' ]
		) );
	}
	
	
	
	//// matches
	
	//
	public function matches( $mSubject, $mMatchTo, $sSubjectKey, $sMatchToKey ) {
		
		$aMatchTo = ( !is_array( $mMatchTo ) ) ? array( $mMatchTo ) : $mMatchTo;
		
		if ( $sSubjectKey != $sMatchToKey ) {
		
			// normalize so values can be compared
			$aNormalize = array();
			foreach ( $this as $oEntity ) {
				if ( in_array( $oEntity->getEntityPropertyValue( $sMatchToKey ), $aMatchTo ) ) {
					$aNormalize[] = $oEntity->getEntityPropertyValue( $sSubjectKey );
				}
			}
			
			$aMatchTo = $aNormalize;
		}
		
		return in_array( $mSubject, $aMatchTo );
	}


	
	//
	public function valueMatchesTitle( $mValue, $mTitle ) {
		return $this->matches( $mValue, $mTitle, 'value', 'title' );
	}
	
	//
	public function valueMatchesSlug( $mValue, $mSlug ) {
		return $this->matches( $mValue, $mSlug, 'value', 'slug' );
	}
	
	
	
	//// get <a value> from <b value>
	
	//
	public function getFrom( $sValue, $sBaseKey, $sReturnKey ) {
		
		foreach ( $this as $oEntity ) {
			if ( $sValue == $oEntity->getEntityPropertyValue( $sBaseKey ) ) {
				return $oEntity->getEntityPropertyValue( $sReturnKey );
			}
		}
		
		return NULL;
	}
	
	
	
	//// magic methods
	
	//
	public function __call( $sMethod, $aArgs ) {
		
		$aRegs = array();
		
		if ( preg_match( '/^get([A-Za-z]+)From([A-Za-z]+)/', $sMethod, $aRegs ) ) {
			
			$sToFld = strtolower( $aRegs[ 1 ] );
			$sFromFld = strtolower( $aRegs[ 2 ] );
			
			return call_user_func( array( $this, 'getFrom' ), $aArgs[ 0 ], $sFromFld, $sToFld );
		}
		
		throw new Exception( sprintf( 'Invalid method %s::%s() called.', __CLASS__, $sMethod ) );
	}
	


	
	
}


