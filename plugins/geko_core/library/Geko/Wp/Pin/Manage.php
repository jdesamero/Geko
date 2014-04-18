<?php

//
class Geko_Wp_Pin_Manage extends Geko_Wp_Options_Manage
{
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'pin_id';
	
	protected $_sSubject = 'PIN';
	protected $_sDescription = 'Manage PINs for use in contests/promos.';
	protected $_sIconId = 'icon-users';
	protected $_iEntitiesPerPage = 500;
	protected $_sType = 'pin';
	protected $_bHasKeywordSearch = TRUE;
	
	
	
	
	
	//// init
	
	//
	public function add() {
		
		global $wpdb;
		
		parent::add();
		
		// Geko_Wp_Enumeration_Manage::getInstance()->add();
		
		$sTableName = 'geko_pin';
		Geko_Wp_Db::addPrefix( $sTableName );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->$sTableName, 'p' )
			->fieldBigInt( 'pin_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldVarChar( 'pin', array( 'size' => 256, 'unq' ) )
			->fieldBool( 'redeemed' )
			->fieldBool( 'testing' )
			->fieldBool( 'npn' )		// no purchase necessary
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
			->fieldDateTime( 'date_redeemed' )
			->fieldDateTime( 'date_completed' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
	
	
	
	// create table
	public function install() {
		
		parent::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
	//// accessors
	
	
	
	
	// hook method
	public function modifyListingParams( $aParams ) {
		
		$aMergeParams = array (
			'kwsearch' => $_GET[ 's' ],
			'redeemed' => $_GET[ 'redeemed' ],
			'testing' => $_GET[ 'testing' ],
			'npn' => $_GET[ 'npn' ]
		);
		
		return array_merge( $aParams, $aMergeParams );
	}

	
	
	
	
	
	//// front-end display methods
		
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-redeemed">Redeemed</th>
		<th scope="col" class="manage-column column-testing">Testing Only</th>
		<th scope="col" class="manage-column column-npn">No Purchase Necessary</th>
		<th scope="col" class="manage-column column-date-created">Date Created</th>
		<th scope="col" class="manage-column column-date-modified">Date Modified</th>
		<th scope="col" class="manage-column column-date-redeemed">Date Redeemed</th>
		<th scope="col" class="manage-column column-date-completed">Date Completed</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-redeemed"><?php $oEntity->echoBoolRedeemed(); ?></td>
		<td class="column-testing"><?php $oEntity->echoBoolTesting(); ?></td>
		<td class="column-npn"><?php $oEntity->echoBoolNpn(); ?></td>
		<td class="date column-date-created"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-modified"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-redeemed"><abbr title="<?php $oEntity->echoDateRedeemed( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateRedeemed( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date-completed"><abbr title="<?php $oEntity->echoDateCompleted( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCompleted( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	// hook method
	public function getFilterSelects() {
		
		//
		return array(
			'redeemed' => array(
				'' => 'Show Redeemed Any',
				'yes' => 'Redeemed',
				'no' => 'Not Redeemed'
			),
			'testing' => array(
				'' => 'Show Testing Any',
				'yes' => 'Testing Only',
				'no' => 'Not Testing'
			),
			'npn' => array(
				'' => 'Show NPN Any',
				'yes' => 'NPN Only',
				'no' => 'Not NPN'
			)
		);
	}
	
	
	
	
	
	
	
	//
	public function formFields() {
		
		$oEntity = $this->_oCurrentEntity;
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<?php if ( $oEntity ): ?>
				
				<tr>
					<th>Date Redeemed</th>
					<td><?php $oEntity->echoDateRedeemed(); ?></td>
				</tr>
				<tr>
					<th>Date Completed</th>
					<td><?php $oEntity->echoDateCompleted(); ?></td>
				</tr>
				
				<tr>
					<th>PIN Id</th>
					<td><?php $oEntity->echoId(); ?></td>
				</tr>
				
				
			<?php endif; ?>

			<tr>
				<th><label for="pin_pin">PIN</label></th>
				<td>
					<input id="pin_pin" name="pin_pin" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="pin_redeemed">PIN Was Redeemed</label></th>
				<td>
					<input id="pin_redeemed" name="pin_redeemed" type="checkbox" value="1" />
				</td>
			</tr>
			<tr>
				<th><label for="pin_testing">For Testing Only</label></th>
				<td>
					<input id="pin_testing" name="pin_testing" type="checkbox" value="1" />
				</td>
			</tr>
			<tr>
				<th><label for="pin_npn">No Purchase Necessary</label></th>
				<td>
					<input id="pin_npn" name="pin_npn" type="checkbox" value="1" />
				</td>
			</tr>
			
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	
	
	
	//// crud methods
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}

	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}
	
	
	
	
	
}


