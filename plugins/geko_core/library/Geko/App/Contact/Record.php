<?php
/*
 * "geko_core/library/Geko/App/Contact/Record.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * this is a Geko_Delegate
 */

//
class Geko_App_Contact_Record extends Geko_Entity_Record
{
	
	//
	public function init() {
		
		$this->addPlugin( 'Geko_App_Contact_Record_Meta' );
		
		parent::init();
		
		return $this;
	}
	
	
	// input validation
	public function validate( $aValues, $sMode ) {
		
		$aErrors = array();
		$aOrigValues = $this->_aOrigValues;
		
		
		/*if ( !trim( $aValues[ 'first_name' ] ) ) {
			$aErrors[ 'first_name' ] = 'First name must be specified!';
		}
		
		if ( !trim( $aValues[ 'last_name' ] ) ) {
			$aErrors[ 'last_name' ] = 'Last name must be specified!';
		}*/
		

		$aEmails = array(
			'email' => 'email',
			'alt_email' => 'alternate email',
			'business_email' => 'business email'
		);
		
		$aEmailKeys = array_keys( $aEmails );
		
		$bHasNoEmail = TRUE;
		
		foreach ( $aEmailKeys as $sKey ) {
			if ( trim( $aValues[ $sKey ] ) ) {
				$bHasNoEmail = FALSE;
			}
		}
		
		if ( $bHasNoEmail ) {
			
			$aErrors[ '_missing_email' ] = 'Email, alternate email, or business email must be specified!';
		
		} else {
			
			$bHasBadEmail = FALSE;
			
			foreach ( $aEmails as $sKey => $sLabel ) {
				
				$sCheckEmail = trim( $aValues[ $sKey ] );

				if ( $sCheckEmail && !Geko_Validate::email( $sCheckEmail ) ) {
					$aErrors[ $sKey ] = sprintf( 'A valid %s address must be specified!', $sLabel );
					$bHasBadEmail = TRUE;
				}
				
			}
			
			if ( !$bHasBadEmail ) {
				
				// check db for duplicates
				$aDontCheckDups = array();
				
				
				// don't check for dups if email info wasn't changed
				if ( 'update' == $sMode ) {
					
					$aOrigEmails = array();
					
					foreach ( $aEmailKeys as $sKey ) {
						if ( $sOrigEmail = trim( $aOrigValues[ $sKey ] ) ) {
							$aOrigEmails[] = $sOrigEmail;
						}
					}
					
					foreach ( $aEmailKeys as $sKey ) {
						if (
							( $sCheckEmail = trim( $aValues[ $sKey ] ) ) && 
							( in_array( $sCheckEmail, $aOrigEmails ) )
						) {
							$aDontCheckDups[ $sKey ] = TRUE;
						}
					}
					
				}
				
				
				$oSubject = $this->_oSubject;
				$oTable = $oSubject->getPrimaryTable();

				foreach ( $aEmails as $sKey => $sLabel ) {
					
					$sCheckEmail = trim( $aValues[ $sKey ] );
					
					if ( $sCheckEmail && !$aDontCheckDups[ $sKey ] ) {
						
						$mCheckDup = Geko_Validate::duplicateDbValues( $oTable, $aEmailKeys, $sCheckEmail );
						
						if ( FALSE === $mCheckDup ) {
							$aErrors[ $sKey ] = sprintf( 'The specified %s address cannot be verified!', $sLabel );						
						} elseif ( is_int( $mCheckDup ) && ( $mCheckDup > 0 ) ) {
							$aErrors[ $sKey ] = sprintf( 'The specified %s address already exists!', $sLabel );
						}
						
					}
					
				}
				
				
			}
			
		}
		
		return $aErrors;
	}
	
	
}
