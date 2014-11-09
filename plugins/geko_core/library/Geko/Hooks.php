<?php

// concept of hooks derived from Wordpress
// essentially, a callback registry system
class Geko_Hooks
{
	const STATIC_CALLBACK = TRUE;
	
	private static $aActions = array();
	private static $aFilters = array();
	
	private static $aCalledActions = array();
	private static $aCalledFilters = array();
	
	
	
	// NOTE: $mSubjectIndex is how arguments from doAction or applyFilter is mapped to
	//		 $aParams in addAction or addFilter
	
	
	//// actions
	
	//
	public static function hasAction( $sActionName ) {
		
		$sActionName = strtolower( $sActionName );
		
		return array_key_exists( $sActionName, self::$aActions );
	}
	
	//
	public static function addAction( $sActionName, $mCallback, $aParams = array(), $mSubjectIndex = 0, $iPriority = 500 ) {
		
		$sActionName = strtolower( $sActionName );
		
		$iIndex = count( self::$aActions[ $sActionName ] );
		
		self::$aActions[ $sActionName ][] = array(
			'callback' => $mCallback,
			'params' => $aParams,
			'subj_idx' => Geko_Array::wrap( $mSubjectIndex ),
			'priority' => $iPriority,
			'index' => $iIndex
		);
	}
	
	//
	public static function doAction() {
		
		$aArgs = func_get_args();
		
		$sActionName = strtolower( array_shift( $aArgs ) );
		
		$aActions = ( is_array( self::$aActions[ $sActionName ] ) ) ? self::$aActions[ $sActionName ] : array() ;
		$aActions = self::sortParamsByPriority( $aActions );
		
		foreach ( $aActions as $aAction ) {
			
			$aParams = $aAction[ 'params' ];
			$aSubjIdx = $aAction[ 'subj_idx' ];
			
			// merge $aArgs with $aParams
			foreach ( $aArgs as $i => $mArg ) {
				
				if ( isset( $aSubjIdx[ $i ] ) ) {
					$aParams[ $aSubjIdx[ $i ] ] = $mArg;
				} else {
					$aParams[ $i ] = $mArg;
				}
				
			}
			
			call_user_func_array( $aAction[ 'callback' ], $aParams );
		}
		
		self::$aCalledActions[] = $sActionName;
	}
	
	//
	public static function didAction( $sActionName ) {
		
		$sActionName = strtolower( $sActionName );
		
		return in_array( $sActionName, self::$aCalledActions );
	}
	
	//
	public static function removeAction( $sActionName, $mMatchCallback = NULL ) {
		
		$sActionName = strtolower( $sActionName );
		
		if ( NULL == $mMatchCallback ) {
			unset( self::$aActions[ $sActionName ] );
		} else {
			self::$aActions[ $sActionName ] = self::removeCallback( self::$aActions[ $sActionName ], $mMatchCallback );
		}
	}
	
	
	//// filters
	
	//
	public static function hasFilter( $sFilterName ) {
		
		$sFilterName = strtolower( $sFilterName );
		
		return array_key_exists( $sFilterName, self::$aFilters );
	}
	
	//
	public static function addFilter( $sFilterName, $mCallback, $aParams = array(), $mSubjectIndex = 0, $iPriority = 500 ) {
		
		$sFilterName = strtolower( $sFilterName );
		
		$iIndex = count( self::$aFilters[ $sFilterName ] );
		
		self::$aFilters[ $sFilterName ][] = array(
			'callback' => $mCallback,
			'params' => $aParams,
			'subj_idx' => Geko_Array::wrap( $mSubjectIndex ),
			'priority' => $iPriority,
			'index' => $iIndex
		);
	}

	//
	public static function applyFilter() {
		
		$aArgs = func_get_args();
		
		$sFilterName = strtolower( array_shift( $aArgs ) );
		
		$mSubject = $aArgs[ 0 ];
		
		$aFilters = ( is_array( self::$aFilters[ $sFilterName ] ) ) ? self::$aFilters[ $sFilterName ] : array();
		$aFilters = self::sortParamsByPriority( $aFilters );
		
		foreach ( $aFilters as $aFilter ) {
			
			$aParams = $aFilter[ 'params' ];
			$aSubjIdx = $aFilter[ 'subj_idx' ];
			
			$bFirst = TRUE;
			
			// merge $aArgs with $aParams
			foreach ( $aArgs as $i => $mArg ) {
				
				if ( $bFirst ) {
					$mArg = $mSubject;			// always pass the filtered value
					$bFirst = FALSE;
				}
				
				if ( isset( $aSubjIdx[ $i ] ) ) {
					$aParams[ $aSubjIdx[ $i ] ] = $mArg;
				} else {
					$aParams[ $i ] = $mArg;
				}
				
			}
			
			$mSubject = call_user_func_array( $aFilter[ 'callback' ], $aParams );
		}
		
		self::$aCalledFilters[] = $sFilterName;
		
		return $mSubject;
	}

	//
	public static function appliedFilter( $sFilterName ) {
		
		$sFilterName = strtolower( $sFilterName );
		
		return in_array( $sFilterName, self::$aCalledFilters );
	}
	
	//
	public static function removeFilter( $sFilterName, $mMatchCallback = NULL ) {
		
		$sFilterName = strtolower( $sFilterName );
		
		if ( NULL == $mMatchCallback ) {
			unset( self::$aFilters[ $sFilterName ] );
		} else {
			self::$aFilters[ $sFilterName ] = self::removeCallback( self::$aFilters[ $sFilterName ], $mMatchCallback );
		}
	}
	
	
	
	
	//// remove callback
	
	//
	private static function removeCallback( $aHooks, $mMatchCallback ) {
		
		if ( is_array( $aHooks ) ) {
			
			$aRes = $aHooks;
			foreach ( $aHooks as $i => $aCallback ) {
				
				$mCallBack = $aCallback[ 'callback' ];
				
				if (
					(
						is_string( $mMatchCallback ) &&
						is_string( $mCallBack ) && 
						( strtolower( $mMatchCallback ) == strtolower( $mCallBack ) )
					) || 
					(
						is_array( $mMatchCallback ) &&
						is_array( $mCallBack ) && 
						(
							(
								is_string( $mCallBack[ 0 ] ) && 
								( $mCallBack[ 0 ] == $mMatchCallback[ 0 ] ) &&
								( $mCallBack[ 1 ] == $mMatchCallback[ 1 ] ) &&
								( self::STATIC_CALLBACK == $mMatchCallback[ 2 ] )
							) ||
							(
								is_object( $mCallBack[ 0 ] ) && 
								( get_class( $mCallBack[ 0 ] ) == $mMatchCallback[ 0 ] ) &&
								( $mCallBack[ 1 ] == $mMatchCallback[ 1 ] )
							)
						)
					)					
				) {
					unset( $aRes[ $i ] );
				}
			}
		}
		return $aRes;
	}
	
	
	
	
	//// priority sorting
	
	//
	private static function sortParamsByPriority( $aSort ) {
		usort( $aSort, array( __CLASS__, '_sortCompare' ) );
		return $aSort;
	}
	
	//
	public static function _sortCompare( $a, $b ) {
		if ( $a[ 'priority'] < $b[ 'priority' ] ) {
			return -1;
		} elseif ( $a[ 'priority' ] > $b[ 'priority' ] ) {
			return 1;
		} else {
			return ( $a[ 'index' ] < $b[ 'index' ] ) ? -1 : 1 ;
		}
	}
	
	
	//// helpers
	
	//
	public static function inject( $sAction, $sSource, $sPatternMatch, $sPatternReplace = '{INJECT}' ) {
		
		ob_start();
		self::doAction( $sAction );
		$sInject = ob_get_contents();
		ob_end_clean();
		
		return preg_replace( $sPatternMatch, str_replace( '{INJECT}', $sInject, $sPatternReplace ), $sSource );
		
	}
	
	
	//
	public static function debug() {
		print_r( self::$aActions );
		print_r( self::$aFilters );
	}
	
	
}



