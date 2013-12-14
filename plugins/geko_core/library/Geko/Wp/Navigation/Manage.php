<?php

//
class Geko_Wp_Navigation_Manage extends Geko_Wp_Options_Manage
{
	private static $aTypes;
	
	protected $_bPrefixFormElems = FALSE;		// turn off prefixing

	protected $_sEntityIdVarName = 'nav_id';
	
	protected $_sSubject = 'Navigation';
	protected $_sSubjectPlural = 'Navigation';
	protected $_sListingTitle = 'Label';	
	protected $_sDescription = 'An API/UI for creating navigation structures.';
	protected $_sIconId = 'icon-tools';
	protected $_sType = 'nav';
	
	//// init
	
	
	//
	public function affix() {
		
		global $wpdb;
		
		Geko_Wp_Db::addPrefix( 'geko_navigation' );
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( $wpdb->geko_navigation, 'n' )
			->fieldBigInt( 'nav_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldLongText( 'label' )
			->fieldVarChar( 'code', array( 'size' => 255, 'unq' ) )
			->fieldLongText( 'description' )
			->fieldLongText( 'notes' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
		;
		
		$this->addTable( $oSqlTable );
		
		return $this;
		
	}
		
	
	
	// create table
	public function install() {
		$this->createTable( $this->getPrimaryTable() );
		return $this;
	}
	
	
	
	//// front-end display methods
	
	//
	public function columnTitle() {
		?>
		<th scope="col" class="manage-column column-code">Code</th>
		<th scope="col" class="manage-column column-description">Description</th>
		<th scope="col" class="manage-column column-date">Date Created</th>
		<th scope="col" class="manage-column column-date">Date Modified</th>
		<?php
	}
	
	//
	public function columnValue( $oEntity ) {
		?>
		<td class="column-code"><?php $oEntity->echoSlug(); ?></td>
		<td class="column-description"><?php $oEntity->echoDescription(); ?></td>
		<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeCreated( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateCreated( 'Y/m/d' ); ?></abbr></td>
		<td class="date column-date"><abbr title="<?php $oEntity->echoDateTimeModified( 'Y/m/d g:i A' ); ?>"><?php $oEntity->echoDateModified( 'Y/m/d' ); ?></abbr></td>
		<?php
	}
	
	
	
	//
	public function formFields() {
		
		?>
		<h3><?php echo $this->_sSubject; ?> Options</h3>
		<style type="text/css">
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<tr>
				<th><label for="nav_label">Label</label></th>
				<td>
					<input id="nav_label" name="nav_label" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<tr>
				<th><label for="nav_code">Code</label></th>
				<td>
					<input id="nav_code" name="nav_code" type="text" class="regular-text" value="" />
				</td>
			</tr>
			<?php $this->customFieldsPre(); ?>
			<tr>
				<th><label for="nav_description">Description</label></th>
				<td>
					<textarea cols="30" rows="5" id="nav_description" name="nav_description" />
				</td>
			</tr>
			<tr>
				<th><label for="nav_notes">Notes</label></th>
				<td>
					<textarea cols="30" rows="5" id="nav_notes" name="nav_notes" />
				</td>
			</tr>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
	}
	
	
	
	//// crud methods
	
	// insert overrides
	
	//
	public function getInsertData( $aParams ) {
		
		list( $aValues, $aFormat ) = parent::getInsertData( $aParams );
		
		if ( !$aValues[ 'code' ] ) $aValues[ 'code' ] = $aValues[ 'label' ];
		$aValues[ 'code' ] =  Geko_Wp_Db::generateSlug(
			$aValues[ 'code' ], $this->getPrimaryTable(), 'code'
		);
		
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return array( $aValues, $aFormat );
		
	}
	
	//
	public function getInsertContinue( $aInsertData, $aParams ) {
		
		$bContinue = parent::getInsertContinue( $aInsertData, $aParams );
		
		list( $aValues, $aFormat ) = $aInsertData;
		
		//// do checks
		
		$sLabel = $aValues[ 'label' ];
		
		// check label
		if ( $bContinue && !$sLabel ) {
			$bContinue = FALSE;
			$this->triggerErrorMsg( 'm201' );										// empty label was given
		}
		
		return $bContinue;
		
	}
	
	
	
	// update overrides
	
	//
	public function getUpdateData( $aParams, $oEntity ) {
		
		list( $aValues, $aWhere, $aFormat, $aWhereFmt ) = parent::getUpdateData( $aParams, $oEntity );
		
		if ( !$aValues[ 'code' ] ) $aValues[ 'code' ] = $aValues[ 'label' ];
		if ( $aValues[ 'code' ] != $oEntity->getSlug() ) {
			$aValues[ 'code' ] = Geko_Wp_Db::generateSlug(
				$aValues[ 'code' ], $this->getPrimaryTable(), 'code'
			);
		}
			
		$sDateTime = Geko_Db_Mysql::getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return array( $aValues, $aWhere, $aFormat, $aWhereFmt );
	}
	
	
	
	// delete overrides	
	
	
	
}