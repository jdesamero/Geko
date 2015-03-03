<?php

abstract class Geko_App_Entity_Query extends Geko_Entity_Query
{
	
	
	//// query methods
	
	
	//
	public function getFoundRows() {
		
		$oDb = Geko_App::get( 'db' );
		
		$sFoundRowsQuery = $oDb->gekoQueryFoundRows( $this );
		
		return $oDb->fetchOne( $sFoundRowsQuery );
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
	
	
	
	//// query methods
	
	//
	public function modifyQuery( $oQuery, $aParams ) {
		
		$oDb = Geko_App::get( 'db' );
		
		// apply super-class manipulations
		$oQuery = parent::modifyQuery( $oQuery, $aParams );
		
		$oQuery = $oDb->gekoQueryInit( $oQuery, $aParams );
		
		return $oQuery;
	}
	
	
	// manipulate query object for random ordering
	public function modifyQueryOrderRandom( $oQuery, $aParams ) {
		
		$oDb = Geko_App::get( 'db' );
		
		$oQuery = $oDb->gekoQueryOrderRandom( $oQuery, $aParams );
		
		return $oQuery;
	}

	
	
}


