<?php

//
class Geko_Db
{
	const GROC_NAMED_QUERY = 'groc.named.query';
	
	//
	public static function getRecord($sTableName)
	{
		$sDbClass = $sTableName;
		return new $sDbClass;
	}
	
	//
	public static function getTable($sTableName)
	{
		return Doctrine::getTable($sTableName);		
	}
	
	//
	public static function getRecordOrCreate(
		$sTableName, $aParams, $sNamedQuery = self::GROC_NAMED_QUERY
	) {

		try {
			
			// Check if record already exists
			$oRec = Gapp_Db::getTable($sTableName)
				->createNamedQuery($sNamedQuery)
				->fetchOne($aParams)
			;
			
			if (FALSE == is_object($oRec))
			{	
				// Insert new node
				$oRec = Gapp_Db::getRecord($sTableName);
				$oRec->merge($aParams);
				$oRec->save();
			}
			
			return $oRec;
			
    	} catch (Exception $e) {
    		
    		// TO DO: log exception
    		// echo $e;
    		return NULL;
    		
    	}
    	
	}
	
	
	//
	public static function insert($oCollection)
	{
		return $oCollection[$oCollection->count()];
	}
	
}


