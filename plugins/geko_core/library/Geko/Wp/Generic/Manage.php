<?php

//// !!! Re-factoring in progress...

//
class Geko_Wp_Generic_Manage extends Geko_Wp_Options_Manage
{
	protected $_bPrefixFormElems = FALSE;
	
	protected $_sEntityIdVarName = 'generic_id';
	
	protected $_sObjectType = 'generic';
	protected $_sIconId = 'icon-edit-pages';
	protected $_sType = 'generic';
	
	
	//
	public function add() {
		
		parent::add();
		
		
		Geko_Wp_Options_MetaKey::init();
		
		$oSqlTable = new Geko_Sql_Table();
		$oSqlTable
			->create( '##pfx##geko_generic', 'g' )
			->fieldBigInt( 'generic_id', array( 'unsgnd', 'notnull', 'autoinc', 'prky' ) )
			->fieldSmallInt( 'gentype_id', array( 'unsgnd', 'key' ) )
			->fieldBigInt( 'object_id', array( 'unsgnd' ) )
			->fieldSmallInt( 'objtype_id', array( 'unsgnd' ) )
			->fieldLongText( 'flags' )
			->fieldDateTime( 'date_created' )
			->fieldDateTime( 'date_modified' )
			->indexKey( 'obj_id_type', array( 'object_id', 'objtype_id' ) )
		;
		
		$this->addTable( $oSqlTable );
		
		
		return $this;
	}
	
	
	//
	public function install() {
		
		parent::install();
		
		Geko_Wp_Options_MetaKey::install();
		
		$this->createTableOnce();
		
		return $this;
	}
	
	
	
	
	
	
	
	
	
	//// front-end display methods
	
		
	//
	public function formFields() {
		
		$sEndings = '';
		
		?>
		<h3><?php echo $this->_sListingTitle; ?> Options</h3>
		<style type="text/css">
			textarea {
				margin-bottom: 6px;
				width: 500px;
			}
		</style>
		<table class="form-table">
			<?php $this->formDateFields(); ?>
			<?php $this->customFieldsMain(); ?>
		</table>
		<?php
		
		$this->parentEntityField();
		
		echo $sEndings;
	}
	
		
	
	
	//// crud methods
	
	// insert overrides
	
	//
	public function modifyInsertPostVals( $aValues ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aValues[ 'gentype_id' ] = Geko_Wp_Options_MetaKey::getId( $this->_sSlug );
		//	$aValues[ 'object_id' ] = $iObjectId;		// ???
		$aValues[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $this->_sObjectType );
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_created' ] = $sDateTime;
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}
	
	// update overrides
	
	//
	public function modifyUpdatePostVals( $aValues, $oEntity ) {
		
		$oDb = Geko_Wp::get( 'db' );
		
		$aValues[ 'gentype_id' ] = Geko_Wp_Options_MetaKey::getId( $this->_sSlug );
		// $aValues[ 'object_id' ] = $iObjectId;		// ???
		$aValues[ 'objtype_id' ] = Geko_Wp_Options_MetaKey::getId( $this->_sObjectType );
		
		$sDateTime = $oDb->getTimestamp();
		$aValues[ 'date_modified' ] = $sDateTime;
		
		return $aValues;
	}

	
	
}


