<?php

class Geko_Wp_Point_Operation extends Geko_Wp_Options_Operation
{
	
	protected $_sTitle = 'Point Adjustments';
	protected $_sMenuTitle = 'Adjustments';
	protected $_sPageTitle = 'Point Adjustments';
	
	protected $_sSubMenuPage = 'Geko_Wp_Point_Manage';
	
	protected $_aOperations = array(
		'award_points' => array()
	);
	
	
	
	
	
	
	//// error message handling
	
	//
	protected function getNotificationMsgs() {
		return array(
			'm101' => 'Adjustment was performed successfully!'
		);
	}
	
	//
	protected function getErrorMsgs() {
		
		$aRes = array(
			'm201' => 'Please specify a user id or email address',
			'm202' => 'Please select an operation to perform',
			'm203' => 'Please specify a point value',
			'm204' => 'Please comment on why this operation is being performed'			
		);
		
		$aErrors = Geko_Wp_Enumeration_Query::getSet( 'geko-point-award-error' );
		
		foreach ( $aErrors as $oError ) {
			$aRes[ intval( $oError->getValue() ) ] = $oError->getContent();
		}
		
		return $aRes;
	}

	
	
	
	
	
	//// front-end display methods
	
	//
	protected function preWrapDiv() {
		?>
		<style type="text/css">

			input.short {
				width: 120px;
			}
			
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
			
		</style>
		<?php
	}

	
	
	//
	protected function formFieldsAwardPoints() {
		?>
		<table class="form-table">
			<tr>
				<th><label for="user_id_or_email">User ID or Email</label></th>
				<td>
					<input id="user_id_or_email" name="user_id_or_email" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="operation">Operation</label></th>
				<td>
					<input id="operation-redeem" name="operation" type="radio" value="redeem" /> Redeem 
					&nbsp;&nbsp;&nbsp;
					<input id="operation-deduct" name="operation" type="radio" value="deduct" /> Deduct 
					&nbsp;&nbsp;&nbsp;
					<input id="operation-award" name="operation" type="radio" value="award" /> Award 
				</td>
			</tr>
			<tr>
				<th><label for="points">Points</label></th>
				<td>
					<input id="points" name="points" type="text" class="regular-text short" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="comments">Comments</label></th>
				<td>
					<textarea cols="30" rows="5" id="comments" name="comments"></textarea>
				</td>
			</tr>
		</table>
		<?php
	}
	
	
	
	//
	public function doActionAwardPoints( $aOperation ) {
		
		$bError = FALSE;
		
		$mUserIdOrEmail = trim( $_REQUEST[ 'user_id_or_email' ] );
		$sOperation = trim( $_REQUEST[ 'operation' ] );
		$iPoints = intval( $_REQUEST[ 'points' ] );
		$sComments = trim( $_REQUEST[ 'comments' ] );
		
		if ( !$mUserIdOrEmail ) {
			$this->triggerErrorMsg( 'm201' );
			$bError = TRUE;
		}
		
		if ( !$bError && !$sOperation ) {
			$this->triggerErrorMsg( 'm202' );
			$bError = TRUE;
		}
		
		if ( !$bError && !$iPoints ) {
			$this->triggerErrorMsg( 'm203' );
			$bError = TRUE;
		}
		
		if ( !$bError && !$sComments ) {
			$this->triggerErrorMsg( 'm204' );
			$bError = TRUE;
		}
		
		if ( !$bError ) {
			
			$aPoints = array(
				'point_event_slug' => $sOperation,
				'point_value' => $iPoints,
				'comments' => $sComments
			);
			
			if ( preg_match( '/^[0-9]+$/', $mUserIdOrEmail ) ) {
				$aPoints[ 'user_id' ] = $mUserIdOrEmail;
			} else {
				$aPoints[ 'email' ] = $mUserIdOrEmail;
			}
			
			$oPtMng = Geko_Wp_Point_Manage::getInstance();
			$mRes = $oPtMng->awardPoints( $aPoints );
			if ( TRUE === $mRes ) {
				$this->triggerNotifyMsg( 'm101' );
			} else {
				$this->triggerErrorMsg( $mRes );
			}
			
		}
		
	}
	
	
}

