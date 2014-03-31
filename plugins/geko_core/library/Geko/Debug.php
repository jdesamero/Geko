<?php

class Geko_Debug
{
	//
	const META_SECTION = 0;
	const VALUE_SECTION = 1;

	const VSC_NAME = 0;
	const VSC_VALUE = 1;
	
	const MSC_LEVEL = 0;
	const MSC_SCOPE = 1;
	const MSC_TYPE = 2;
	
	
	//
	protected static $iMaxLevels = 20;
	protected static $aObjectRegistry = array();
	protected static $bShowLevels = FALSE;
	protected static $bShowType = FALSE;
	protected static $bShowObjectMethods = TRUE;
	protected static $bShowClassConstants = TRUE;
	protected static $bShowScope = TRUE;
	protected static $bRecognizeClassNames = TRUE;
	protected static $aScopes = array( 'public', 'protected', 'private' );
	
	
	protected static $sOutBreak = '<br />';
	protected static $aOutEnable = NULL;
	protected static $aOutDisable = NULL;
	protected static $bShowOut = FALSE;
	
	
	
	
	// prevent instantiation
	protected function __construct() {
		// do nothing
	}
	
	
	
	////// static setters
	
	//
	public static function setMaxLevels( $iMaxLevels ) {
		self::$iMaxLevels = $iMaxLevels;
	}

	//
	public static function setShowLevels( $bShowLevels ) {
		self::$bShowLevels = $bShowLevels;
	}
	
	//
	public static function setShowType( $bShowType ) {
		self::$bShowType = $bShowType;
	}
	
	//
	public static function setShowObjectMethods( $bShowObjectMethods ) {
		self::$bShowObjectMethods = $bShowObjectMethods;
	}
	
	//
	public static function setShowClassConstants( $bShowClassConstants ) {
		self::$bShowClassConstants = $bShowClassConstants;
	}
	
	//
	public static function setShowScope( $bShowScope ) {
		self::$bShowScope = $bShowScope;
	}
	
	//
	public static function setRecognizeClassNames( $bRecognizeClassNames ) {
		self::$bRecognizeClassNames = $bRecognizeClassNames;
	}	
	
	//
	public static function setScopes() {
		$aScopes = func_get_args();
		self::$aScopes = $aScopes;
	}
	
	//
	public static function setToDefaults() {
		self::$iMaxLevels = 20;
		self::$aObjectRegistry = array();
		self::$bShowLevels = FALSE;
		self::$bShowType = FALSE;
		self::$bShowObjectMethods = TRUE;
		self::$bShowClassConstants = TRUE;
		self::$bShowScope = TRUE;
		self::$bRecognizeClassNames = TRUE;
		self::$aScopes = array( 'public', 'protected', 'private' );
	}
	
	
	
	//////
	
	// check if the object is already in the registry
	// if so, output should be suppressed
	private static function inRegistry( $mData ) {
		foreach (self::$aObjectRegistry as $mObject) {
			if ( $mData === $mObject ) {
				return TRUE;
			}
		}
		
		// object not in registry, track it
		self::$aObjectRegistry[] = $mData;
		return FALSE;
	}
	
	//
	private static function getRegistryRefNum( $mData ) {
		foreach ( self::$aObjectRegistry as $i => $mObject ) {
			if ( $mData === $mObject ) {
				return $i;
			}
		}	
	}
	
	// View object contents
	public static function &explore( $mData, $iLevel = 0 ) {
		
		if ( $iLevel < self::$iMaxLevels ) {
			
			$aOutput = array();
			
			if ( TRUE == self::$bShowLevels ) {
				$aOutput[ self::META_SECTION ][ self::MSC_LEVEL ] = $iLevel;
			}
			
			if ( TRUE == self::$bShowType ) {
				$aOutput[ self::META_SECTION ][ self::MSC_TYPE ] = gettype( $mData );
			}
			
			if ( $mData instanceof Geko_Debug_Introspect ) {
				
				if ( TRUE == self::$bShowType ) {
					$aOutput[ self::META_SECTION ][ self::MSC_TYPE ] = 'class';
				}
				
				////
				$sClassName = $mData->getClassName();
				$aClassName[ self::VALUE_SECTION ] = $sClassName;
				
				$aOutput[ self::VALUE_SECTION ][ 'Class Name:' ] = $aClassName;
				
				
				////
				if ( TRUE == self::$bShowClassConstants ) {
					$aConstants = array();
					$aConstants[ self::VALUE_SECTION ] = self::getClassConstants(
						$iLevel, $sClassName
					);
					
					if ( count( $aConstants[ self::VALUE_SECTION ] ) > 0 ) {
						$aOutput[ self::VALUE_SECTION ][ 'Constants:' ] = $aConstants;
					} else {
						$aNone = array();
						$aNone[ self::VALUE_SECTION ] = '[none]';
						$aOutput[ self::VALUE_SECTION ][ 'Constants:' ] = $aNone;					
					}
				}
				
				
				////
				$aProperties = array();
				$aProperties[ self::VALUE_SECTION ] = self::getClassProperties(
					$iLevel, $sClassName
				);
				
				if ( count( $aProperties[ self::VALUE_SECTION ] ) > 0 ) {
					$aOutput[ self::VALUE_SECTION ][ 'Properties:' ] = $aProperties;
				} else {
					$aNone = array();
					$aNone[ self::VALUE_SECTION ] = '[none]';
					$aOutput[ self::VALUE_SECTION ][ 'Properties:' ] = $aNone;					
				}
				
				////
				$aMethods = array();
				$aMethods[ self::VALUE_SECTION ] = self::getClassMethods(
					$mData, $iLevel, $sClassName
				);
				
				$aOutput[ self::VALUE_SECTION ][ 'Methods:' ] = $aMethods;
				
			} elseif ( TRUE == is_string( $mData ) ) {
				
				if (
					( TRUE == @class_exists( $mData ) ) && 
					( TRUE == self::$bRecognizeClassNames )
				) {
					return self::explore( new Geko_Debug_Introspect( $mData ), $iLevel );
				} else {
					if ( '' === $mData ) {
						$aOutput[ self::VALUE_SECTION ] = '[empty string]';
					} else {
						$aOutput[ self::VALUE_SECTION ] = htmlspecialchars( $mData );
					}
				}
			
			} elseif ( TRUE == is_bool( $mData ) ) {
			
				$aOutput[ self::VALUE_SECTION ] = ( TRUE === $mData) ? '[TRUE]' : '[FALSE]';
			
			} elseif ( TRUE == is_null( $mData ) ) {
			
				$aOutput[ self::VALUE_SECTION ] = '[NULL]';
			
			} elseif ( TRUE == is_array( $mData ) ) {
				
				if ( count( $mData ) > 0 ) {
					
					foreach ( $mData as $mKey => $mValue ) {
						$aOutput[ self::VALUE_SECTION ][ $mKey ] = self::explore(
							$mValue, $iLevel + 1
						);
					}
				} else {
					$aOutput[ self::VALUE_SECTION ] = '[empty array]';
				}
			
			} elseif ( TRUE == is_object( $mData ) ) {
				
				$sClassName = get_class( $mData );
				
				if ( FALSE == self::inRegistry( $mData ) ) {
					
					////
					$aClassName = array();
					$aClassName[ self::VALUE_SECTION ] = $sClassName;
										
					$aOutput[ self::VALUE_SECTION ][ 'Object Type:' ] = $aClassName;
					
					
					////
					$iRefNum = self::getRegistryRefNum( $mData );
					
					$aRefNum = array();
					$aRefNum[ self::VALUE_SECTION ] = $iRefNum;
					
					$aOutput[ self::VALUE_SECTION ][ 'Ref #:' ] = $aRefNum;
					
					
					////
					if ( TRUE == self::$bShowClassConstants) {
						$aConstants = array();
						$aConstants[ self::VALUE_SECTION ] = self::getClassConstants(
							$iLevel, $sClassName
						);
						
						if (count($aConstants[ self::VALUE_SECTION ]) > 0) {
							$aOutput[ self::VALUE_SECTION ][ 'Constants:' ] = $aConstants;
						} else {
							$aNone = array();
							$aNone[ self::VALUE_SECTION ] = '[none]';
							$aOutput[ self::VALUE_SECTION ][ 'Constants:' ] = $aNone;					
						}
					}
					
					
					////
					$aProperties = array();
					$aProperties[ self::VALUE_SECTION ] = self::getObjectProperties(
						$mData, $iLevel, $sClassName
					);
					
					if ( count( $aProperties[ self::VALUE_SECTION ] ) > 0 ) {
						$aOutput[ self::VALUE_SECTION ][ 'Properties:' ] = $aProperties;
					} else {
						$aNone = array();
						$aNone[ self::VALUE_SECTION ] = '[none]';
						$aOutput[ self::VALUE_SECTION ][ 'Properties:' ] = $aNone;					
					}
					
					////
					if ( TRUE == self::$bShowObjectMethods) {
						
						$aMethods = array();
						$aMethods[ self::VALUE_SECTION ] = self::getClassMethods(
							$mData, $iLevel, $sClassName
						);
						
						$aOutput[ self::VALUE_SECTION ][ 'Methods:' ] = $aMethods;
					}
				
				} else {
					
					$iRefNum = self::getRegistryRefNum( $mData );
					
					$aOutput[ self::VALUE_SECTION ] = sprintf(
						'%s [Ref #: %s]',
						$sClassName, $iRefNum
					);
				}
				
			} else {
				$aOutput[ self::VALUE_SECTION ] = $mData;
			}
			
			return $aOutput;
		}
	}
	
	//
	private static function &getObjectProperties( $mData, $iLevel, $sClassName ) {
		
		$aOutput = array();
		
		$aProperties = ( array ) $mData;
		
		foreach ( $aProperties as $sKey => $mValue ) {
			
			if ( preg_match( sprintf( '/^%s(.+)/i', trim( $sClassName ) ), trim( $sKey ), $aRegs ) > 0 ) {
				$sPropName = $aRegs[ 1 ];
				$sScope = 'private';
			} elseif ( preg_match( '/^\*(.+)/', trim( $sKey ), $aRegs ) > 0 ) {
				$sPropName = $aRegs[ 1 ];
				$sScope = 'protected';					
			} else {
				$sPropName = $sKey;
				$sScope = 'public';					
			}
			
			$aValue = self::explore(
				$mValue, $iLevel + 1
			);
			
			if ( TRUE == self::$bShowScope ) {
				$aValue[ self::META_SECTION ][ self::MSC_SCOPE ] = $sScope;
			}
			
			// check if variable in scope needs to be shown
			if ( TRUE == in_array( $sScope, self::$aScopes ) ) {
				$aOutput[ $sPropName ] = $aValue;
			}
		}
		
		//
		$oReflect = new ReflectionClass( $sClassName );
		$aClassProperties = self::extractClassProperties( $oReflect, $iLevel, TRUE );
		
		return array_merge( $aOutput, $aClassProperties );
	}
	
	
	//
	private static function &getClassProperties( $iLevel, $sClassName ) {
		
		$oReflect = new ReflectionClass( $sClassName );
		
		$oConstructorMethod = $oReflect->getConstructor();
		
		if ( NULL !== $oConstructorMethod ) {
			
			// has constructor
			if (
				0 == $oConstructorMethod->getNumberOfRequiredParameters() &&
				TRUE == $oConstructorMethod->isPublic()
			) {
				$oInstance = $oReflect->newInstance();
				return self::getObjectProperties( $oInstance, $iLevel, $sClassName );
			}
		} else {
			// no constructor
			$oInstance = $oReflect->newInstance();
			return self::getObjectProperties( $oInstance, $iLevel, $sClassName );		
		}
		
		return self::extractClassProperties( $oReflect, $iLevel );
	}
	
	//
	private static function &extractClassProperties( $oReflect, $iLevel, $bStaticOnly = FALSE ) {
		
		$aOutput = array();
		
		$aProperties = $oReflect->getProperties();
		$aStaticPropertyValues = $oReflect->getStaticProperties();
		
		foreach ( $aProperties as $oProperty ) {
			
			$aValue = array();
			$aKeywords = array();
			
			$sKey = trim( $oProperty->getName() );
			
			if ( TRUE == self::$bShowType ) {
				$aValue[ self::META_SECTION ][ self::MSC_TYPE ] = 'unknown';
			}
			
			$aValue[ self::VALUE_SECTION ] = '[hidden value]';
			
			if ( TRUE == $oProperty->isPrivate() ) {
				$sScope = 'private';
			} elseif ( TRUE == $oProperty->isProtected() ) {
				$sScope = 'protected';
			} else {
				$sScope = 'public';
			}
			
			$aKeywords[] = $sScope;
			
			if ( TRUE == $oProperty->isStatic() ) {
				$aKeywords[] = 'static';
				
				$aValue = self::explore(
					$aStaticPropertyValues[ $sKey ], $iLevel + 1
				);
			}
			
			if ( TRUE == self::$bShowLevels ) {
				$aValue[ self::META_SECTION ][ self::MSC_LEVEL ] = $iLevel + 1;
			}
			
			if ( TRUE == self::$bShowScope ) {
				$aValue[ self::META_SECTION ][ self::MSC_SCOPE ] = implode( ', ', $aKeywords );
			}
			
			if (
				( FALSE == $bStaticOnly ) ||
				( TRUE == $bStaticOnly && TRUE == $oProperty->isStatic() )
			) {
				// check if variable in scope needs to be shown
				if ( TRUE == in_array( $sScope, self::$aScopes ) ) {
					$aOutput[$sKey] = $aValue;
				}
			}
		}
		
		return $aOutput;		
	}
	

	//
	private static function &getClassConstants( $iLevel, $sClassName ) {
		
		$oReflect = new ReflectionClass( $sClassName );
		
		$aOutput = array();
		$aConstants = $oReflect->getConstants();
		
		foreach ( $aConstants as $sKey => $mValue ) {
			
			$aValue = array();
			$aValue = self::explore(
				$mValue, $iLevel + 1
			);
			
			$aOutput[ $sKey ] = $aValue;
		}
		
		return $aOutput;
	}
	
	
	//
	private static function &getClassMethods( $mData, $iLevel, $sClassName ) {
		
		$aOutput = array();
		
		$oReflect = new ReflectionClass( $sClassName );
		$aMethods = $oReflect->getMethods();
		
		foreach ( $aMethods as $oMethod ) {
			
			$aParams = $oMethod->getParameters();
			
			$sKey = sprintf(
				'%s%s',
				$oMethod->getName(),
				self::getMethodParams( $aParams, $iLevel + 1 )
			);
			
			$aKeywords = array();
			
			if ( TRUE == $oMethod->isPrivate() ) {
				$sScope = 'private';
			} elseif ( TRUE == $oMethod->isProtected() ) {
				$sScope = 'protected';
			} else {
				$sScope = 'public';
			}
			
			$aKeywords[] = $sScope;
			
			if ( $oMethod->isStatic() ) {
				$aKeywords[] = 'static';
			}
			
			if ( $oMethod->isAbstract() ) {
				$aKeywords[] = 'abstract';
			}
			
			if ( $oMethod->isFinal() ) {
				$aKeywords[] = 'final';
			}
			
			$aValue = array();

			if ( TRUE == self::$bShowLevels ) {
				$aValue[ self::META_SECTION ][ self::MSC_LEVEL ] = $iLevel + 1;
			}
			
			if ( TRUE == self::$bShowScope ) {
				$aValue[ self::META_SECTION ][ self::MSC_SCOPE ] = implode( ', ', $aKeywords );
			}
			
			// check if variable in scope needs to be shown
			if ( TRUE == in_array( $sScope, self::$aScopes ) ) {
				$aOutput[ $sKey ] = $aValue;
			}
		}
		
		return $aOutput;
	}

	//
	private static function getMethodParams( $aParams, $iLevel ) {
		
		//return implode(', ', get_class_methods( $mData ) );
		//return self::explore(get_class_methods( $mData ), $iLevel);
		
		$aResult = array();
		foreach ( $aParams as $oParam ) {
			
			if ( TRUE == $oParam->isOptional() ) {
				$aResult[] = sprintf( '[%s]', $oParam->getName() );
			} else {
				$aResult[] = $oParam->getName();			
			}
		}
		
		return sprintf( '(%s)', implode( ', ', $aResult ) );
	}
	
	
	//
	private static function render( $aOutput ) {
		
		$sOutput = '';
		$sOutput .= sprintf(
			'<table border="0" cellspacing="1" cellpadding="2" bgcolor="#000000">%s',
			"\n"
		);
		
		$aMeta = $aOutput[ self::META_SECTION ];
		$mValues = $aOutput[ self::VALUE_SECTION ];
				
		$iLevelColspan = 1;
		$sSubOutput = '';
		
		if ( TRUE == is_array( $mValues ) ) {
			
			$bAddToLevelColspan = FALSE;
			
			foreach ( $mValues as $mKey => $aRow ) {
				
				$aSubMeta = $aRow[ self::META_SECTION ];
				
				if ( TRUE == isset( $aRow[ self::VALUE_SECTION ] ) ) {
					$bShowValues = TRUE;
					$mSubValues = $aRow[ self::VALUE_SECTION ];
				} else {
					$bShowValues = FALSE;
				}
				
				$sSubOutput .= "<tr>\n";
				
				$sSubOutput .= sprintf( '<th bgcolor="#CCCCCC">%s</th>%s', $mKey, "\n" );
				
				if ( TRUE == isset( $aSubMeta[ self::MSC_SCOPE ] ) ) {
					$sSubOutput .= sprintf( '<td bgcolor="#FFFFFF">%s</td>%s', $aSubMeta[ self::MSC_SCOPE ], "\n" );
					if ( FALSE == $bAddToLevelColspan ) $iLevelColspan++;
				}
				
				if ( TRUE == isset( $aSubMeta[ self::MSC_TYPE ] ) ) {
					$sSubOutput .= sprintf( '<td bgcolor="#FFFFFF">%s</td>%s', $aSubMeta[ self::MSC_TYPE ], "\n" );
					if ( FALSE == $bAddToLevelColspan ) $iLevelColspan++;
				}
				
				if ( TRUE == $bShowValues ) {
					if ( TRUE == is_array( $mSubValues ) ) {
						$sSubOutput .= sprintf( '<td bgcolor="#FFFFFF">%s</td>%s', self::render( $aRow ), "\n" );
					} else {
						$sSubOutput .= sprintf( '<td bgcolor="#FFFFFF">%s</td>%s', $mSubValues, "\n" );
					}
					if ( FALSE == $bAddToLevelColspan ) $iLevelColspan++;
				}
				
				$bAddToLevelColspan = TRUE;		// do this only once
				$sSubOutput .= "</tr>\n";	
			}
			
		} else {
			
			$sSubOutput .= "<tr>\n";
			
			if ( TRUE == isset( $aMeta[ self::MSC_TYPE ] ) ) {				
				$sSubOutput .= sprintf( '<td bgcolor="#FFFFFF">%s</td>%s', $aMeta[ self::MSC_TYPE ], "\n" );
				$iLevelColspan++;
			}
			
			$sSubOutput .= sprintf( '<td bgcolor="#FFFFFF">%s</td>%s', $mValues, "\n" );
			
			$sSubOutput .= "</tr>\n";	
		}
		
		if ( TRUE == isset( $aMeta[ self::MSC_LEVEL ] ) ) {
			$sLevel = sprintf(' Level: %s;', $aMeta[ self::MSC_LEVEL ] );
		} else {
			$sLevel = '';
		}
		
		if ( TRUE == isset( $aMeta[ self::MSC_TYPE ] ) ) {
			$sType = sprintf(' Type: %s;', $aMeta[ self::MSC_TYPE ] );
		} else {
			$sType = '';
		}
		
		if ( ( '' != $sLevel ) || ( '' != $sType ) ) {
			$sOutput .= sprintf(
				'<tr bgcolor="#999999"><td colspan="%s"><b>%s%s</b></td></tr>%s',
				$iLevelColspan, $sLevel, $sType, "\n"
			);
		}
		
		$sOutput .= $sSubOutput;
		
		$sOutput .= "</table>\n";
		
		return $sOutput;
	}
	
	
	//
	public static function dump( $mData ) {
		
		if (
			TRUE == is_scalar( $mData ) &&
			TRUE == @class_exists( $mData ) &&
			TRUE == self::$bRecognizeClassNames
		) {
			$mData = new Geko_Debug_Introspect( $mData );
		}
		
		if ( TRUE == is_scalar( $mData ) || TRUE == is_null( $mData ) ) {
			
			$bShowLevels = self::$bShowLevels;
			$bShowType = self::$bShowType;
			
			self::$bShowLevels = TRUE;				// temporarily set as true
			self::$bShowType = TRUE;				// temporarily set as true
			
			$aOutput = self::explore( $mData );
			$sOutput = self::render($aOutput);
			
			self::$bShowLevels = $bShowLevels;		// revert to original settings
			self::$bShowType = $bShowType;			// revert to original settings
			
			return $sOutput;
		
		} else {
			
			$aOutput = self::explore( $mData );
			
			//ViewObject( self::$aObjectRegistry );
			
			self::$aObjectRegistry = array();
			
			return self::render( $aOutput );
		}
	}
	
	
	//
	public static function display( $mData ) {
		printf( '<br />%s<br />', self::dump( $mData ) );
	}
	
	
	
	
	//// out*() debugging methods
	
	
	//
	public static function setOutBreak( $sOutBreak ) {
		self::$sOutBreak = $sOutBreak;
	}
	
	//
	public static function setShowOut( $bShowOut ) {
		self::$bShowOut = $bShowOut;
	}
	
	
	//// setOutEnable/Disable takes a list of "groups"
	
	//
	public static function setOutEnable() {
		
		self::$aOutDisable = NULL;			// switch off the other mode
		
		self::$aOutEnable = func_get_args();
	}

	//
	public static function setOutDisable() {
		
		self::$aOutEnable = NULL;			// switch off the other mode
		
		self::$aOutDisable = func_get_args();
	}
	
	//
	public static function out( $sValue, $sGroup = '' ) {
		
		if ( self::$bShowOut ) {
			
			if (
				(
					( is_array( self::$aOutEnable ) ) && 
					( in_array( $sGroup, self::$aOutEnable ) )
				) || (
					( is_array( self::$aOutDisable ) ) && 
					( !in_array( $sGroup, self::$aOutDisable ) )				
				) || (
					( NULL === self::$aOutEnable ) && 
					( NULL === self::$aOutDisable )					
				)
			) {
				
				$sPrefix = ( $sGroup ) ? sprintf( '%s - ', $sGroup ) : '' ;
				
				printf( '%s%s%s', $sPrefix, $sValue, self::$sOutBreak );
				
			}				
			
		}
	}
	
	
}

