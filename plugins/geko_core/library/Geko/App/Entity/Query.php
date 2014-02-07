<?php

abstract class Geko_App_Entity_Query extends Geko_Entity_Query
{
	
	
	//// query methods
	
	
	//
	public function getFoundRows() {
		
		$oDb = Geko_App::get( 'db' );
		
		return $oDb->fetchOne( 'SELECT FOUND_ROWS()' );
	}
	
	
	//
	public function getEntities( $mParam ) {
		
		$oDb = Geko_App::get( 'db' );
		
		if ( $this->_bProfileQuery ) {
			echo $this->getEntityQuery( $mParam );
			return array();
		} else {
			return $oDb->fetchAll(
				$this->getEntityQuery( $mParam )
			);
		}
	}
	
	//
	public function getSingleEntity( $mParam ) {
		
		$oDb = Geko_App::get( 'db' );
		
		return $oDb->fetchRow(
			$this->getEntityQuery( $mParam )
		);
	}
	
	
	//
	public function createSqlSelect() {
		
		$oDb = Geko_App::get( 'db' );
		
		return new Geko_Sql_Select( $oDb );
	}

	
	
}


