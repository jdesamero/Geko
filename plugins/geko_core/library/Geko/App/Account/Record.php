<?php
/*
 * "geko_core/library/Geko/App/Account/Record.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Account_Record extends Geko_Entity_Record
{
	
	
	// keep password encryption simple, for now
	public static function encryptPassword( $sPassword ) {
		
		// $sPassword cannot be empty, leading and trailing spaces are trimmed
		if ( $sPassword ) {
			return md5( trim( $sPassword ) );
		}
		
		return FALSE;
	}
	
	
	//
	public function setTheRelType( $sRelTypeSlug ) {
		
		return $this->setEntityPropertyValue(
			'rel_type_id',
			Gloc_Egg_Entity_Type::_getId( $sRelTypeSlug )
		);
	}
	
	//
	public function setThePassword( $sRawPassword ) {
		
		return $this->setEntityPropertyValue(
			'password',
			self::encryptPassword( $sRawPassword )
		);
	}
	
	
	
	//
	public function formatUpdateValues( $aVals ) {
		
		$aOrigValues = $this->_aOrigValues;
		
		list( $aAllValues, $aValues, $aOtherValues ) = $aVals;
		
		// retain original, if not set
		if ( !$aValues[ 'password' ] ) {
			$aValues[ 'password' ] = $aOrigValues[ 'password' ];
		}
		
		return array( $aAllValues, $aValues, $aOtherValues );
	}
	
	
	// input validation
	public function validate( $aValues, $sMode ) {
		
		$aErrors = array();
		$aOrigValues = $this->_aOrigValues;
		$oSubject = $this->_oSubject;
		
		
		//// login
		
		$sLogin = trim( $aValues[ 'login' ] );
		
		if ( !$sLogin ) {
			
			$aErrors[ 'login' ] = 'Login must be specified!';
		
		} else {
			
			$bCheckForDup = TRUE;
			
			// if record is being updated and login did not change, don't check for duplicate values
			if (
				( 'update' == $sMode ) && 
				( $sLogin == $aOrigValues[ 'login' ] )
			) {
				$bCheckForDup = FALSE;
			}
			
			if ( $bCheckForDup ) {
				
				// check for duplicates
				$oTable = $oSubject->getPrimaryTable();
				
				$mCheckDup = Geko_Validate::duplicateDbValues( $oTable, 'login', $sLogin );
				
				if ( FALSE === $mCheckDup ) {
					$aErrors[ 'login' ] = 'Login cannot be verified!';			
				} elseif ( is_int( $mCheckDup ) && ( $mCheckDup > 0 ) ) {
					$aErrors[ 'login' ] = 'Login already exists!';			
				}
			}
			
		}
		
		
		//// password
		
		// $aErrors[ 'first_name' ] = sprintf( '(%s) (%s)', self::encryptPassword( $oSubject->getData( 'current_password' ) ), $aOrigValues[ 'password' ] );
		
		
		// password should be sanitized at this point
		$sPassword = $aValues[ 'password' ];
		
		if ( !$sPassword ) {
			
			// only allow blank passwords when updating
			if ( 'insert' == $sMode ) {
				$aErrors[ 'password' ] = 'Password cannot be blank!';
			}
			
		} else {
			
			if ( !Geko_Validate::md5( $sPassword ) ) {
				
				$aErrors[ 'password' ] = 'Password is in an incorrect format!';
			
			} else {
				
				if ( 'update' == $sMode ) {
					
					$sCurrentPwd = self::encryptPassword( $oSubject->getData( 'current_password' ) );
					
					if ( $sCurrentPwd != $aOrigValues[ 'password' ] ) {
						$aErrors[ 'current_password' ] = 'Current password provided is incorrect!';
					}
				}
				
			}
			
		}
		
		
		return $aErrors;
	}
	
	
	
	//
	
	
	
	
}


