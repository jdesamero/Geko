<?php

class Geko_Class
{
	
	//
	protected static $_aResolveCache = array();
	
	
	// prevent instantiation
	private function __construct() {
		// do nothing
	}
	
	// Change SomeClass::someMethod to SomeClass->someMethod
	// $sMethod value is typically __METHOD__
	public static function formatMethod( $sMethod ) {
		return str_replace( '::', '->', $sMethod );
	}
	
	//
	public static function isSubclassOf( $mCheck, $sClassName ) {
		
		if ( is_object( $mCheck ) ) {
			return is_subclass_of( $mCheck, $sClassName );
		} else {
			if ( class_exists( $mCheck ) ) {
				$oReflect = new ReflectionClass( $mCheck );
				return (
					is_subclass_of( $mCheck, $sClassName ) ||
					$oReflect->implementsInterface( $sClassName )
				);
			} else {
				return FALSE;
			}
		}
	}
	
	// return the first class that exists from argument list
	public static function existsCoalesce() {
		$aArgs = func_get_args();		
		foreach ( $aArgs as $mValue ) {
			if ( TRUE == is_array( $mValue ) ) {
				return call_user_func_array( array( self, 'existsCoalesce' ), $mValue );
			} else {
				if ( @class_exists( $mValue ) ) return $mValue;
			}
		}
		return '';
	}
	
	//
	public static function resolveRelatedClass( $mBaseClass, $sBaseSuffix, $sRelatedSuffix, $sResolvedClass = '' ) {
		
		if ( $sResolvedClass ) return $sResolvedClass;
		
		$sBaseClass = ( is_object( $mBaseClass ) ) ? get_class( $mBaseClass ) : $mBaseClass;
		
		$sResolveKey = sprintf( '%s|%s|%s', $sBaseClass, $sBaseSuffix, $sRelatedSuffix );
		
		if ( !isset( self::$_aResolveCache[ $sResolveKey ] ) ) {
			
			if ( $sBaseSuffix ) {
				
				$iBaseClassLen = strlen( $sBaseClass );
				$iBaseSuffixLen = strlen( $sBaseSuffix );
				
				if ( '*' == substr( $sBaseSuffix, $iBaseSuffixLen - 1, 1 ) ) {
					$sBaseSuffix = substr( $sBaseSuffix, 0, $iBaseSuffixLen - 1 );
					$sBaseSuffix = substr( $sBaseClass, strrpos( $sBaseClass, $sBaseSuffix ) );
					$iBaseSuffixLen = strlen( $sBaseSuffix );
				}
				
				if (
					( strrpos( $sBaseClass, $sBaseSuffix ) ) != 
					( $iLen = $iBaseClassLen - $iBaseSuffixLen )
				) {
					return FALSE;
				} else {
					$sBaseClass = substr( $sBaseClass, 0, $iLen );
				}
			}
			
			$sRelatedClass = sprintf( '%s%s', $sBaseClass, $sRelatedSuffix );
			
			self::$_aResolveCache[ $sResolveKey ] = ( @class_exists( $sRelatedClass ) ) ?
				$sRelatedClass : FALSE
			;
			
		}

		return self::$_aResolveCache[ $sResolveKey ];
	}
	
	//
	public static function getBestMatch( $aPrefixes, $aSuffixes ) {
		
		$aSuffixes = Geko_Array::wrap( $aSuffixes );
		
		$aClasses = array();
		
		foreach ( $aSuffixes as $sSuffix ) {
			foreach ( $aPrefixes as $sPrefix ) {
				$aClasses[] = sprintf( '%s%s', $sPrefix, $sSuffix );
			}
		}
		
		return Geko_Class::existsCoalesce( $aClasses );	
	}
	
	
	//
	public static function getConstants( $mCheck ) {
		
		$sClass = '';
		
		if ( is_object( $mCheck ) ) {
			$sClass = get_class( $mCheck );
		} elseif ( class_exists( $mCheck ) ) {
			$sClass = $mCheck;
		}
		
		if ( $sClass ) {
			$oReflect = new ReflectionClass( $mCheck );
			return $oReflect->getConstants();
		}
		
		return NULL;
	}
	
	
	//
	public static function createInstance( $sClass, $aParams ) {
		
		// no need to use reflection class if there are no params
		if ( !$aParams ) {
			return new $sClass();
		}
		
		$oReflect = new ReflectionClass( $sClass );
		
		return $oReflect->newInstanceArgs( $aParams );
	}
	
	
}



