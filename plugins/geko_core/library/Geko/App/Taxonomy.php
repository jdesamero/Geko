<?php
/*
 * "geko_core/library/Geko/App/Taxonomy.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 */

//
class Geko_App_Taxonomy extends Geko_App_Entity
{
	
	protected $_sEntityIdVarName = 'id';
	
	
	//// static methods
	
	protected static $aTx = NULL;
	protected static $aTxItems = NULL;
	
	
	
	// TO DO: implement $mRelType and $mLang args
	public static function getItems( $mIdOrSlug, $mRelType = NULL, $mLang = NULL ) {
		
		if ( NULL === self::$aTxItems ) {
			
			// query entire taxonomy and taxonomy item tables			
			self::$aTx = new Geko_App_Taxonomy_Query( array(), FALSE );
			self::$aTxItems = new Geko_App_Taxonomy_Item_Query( array(), FALSE );
			
		}
		
		$iTxId = NULL;
		
		if ( !preg_match( '/^[0-9]+$/', $mIdOrSlug ) ) {
			
			// convert slug to tx id
			$aTxSlugSubset = self::$aTx->subsetSlug( $mIdOrSlug );
			
			if (
				( 1 == $aTxSlugSubset->count() ) || 
				( NULL === $mRelType )
			) {
				
				// get first match
				$iTxId = $aTxSlugSubset[ 0 ]->getId();
			}
			
		} else {
			
			$iTxId = intval( $mIdOrSlug );
		}
		
		
		if ( $iTxId ) {
			return self::$aTxItems->subsetTaxonomyId( $iTxId );
		}
		
		return NULL;
	}
	
	
	
	//// instance methods
	
	//
	public function init() {
		
		parent::init();
		
		
		$this
			->setEntityMapping( 'title', 'label' )
		;
		
		return $this;
	}

	
	
}


