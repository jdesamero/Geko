<?php

//
class Geko_Fb_User extends Geko_Fb_Entity
{
	protected $_sEntityIdVarName = 'uids';
	
	//
	public function init()
	{
		$this
			->setEntityMapping( 'id', 'uid' )
			->setEntityMapping( 'title', 'name' )
			// ->setEntityMapping( 'date_created', 'created' )
			// ->setEntityMapping( 'date_modified', 'modified' )
		;
		
		return parent::init();
	}
	
	//
	public function modifySingleEntityQueryParams( $aParams )
	{
		$sQueryClass = $this->_sQueryClass;
		$oQuery = new $sQueryClass( NULL, FALSE );
		
		return array_merge(
			$aParams,
			$oQuery->getDefaultParams()
		);
	}
	
	
	/*
	//
	public function getPhotoAlbums( $aParams = array() )
	{
		return $this->formatEntityArray(
			'Geko_Fb_PhotoAlbum',
			$this->_oFb->api_client->photos_getAlbums( $this->_iId, '' ),
			$aParams
		);
	}
	*/
	
	//
	/* public function uploadPhotoToProfile( $sLocalImagePath, $sCaption = '' )
	{
		$aAlbums = $this->_oFb->api_client->photos_getAlbums( $this->_iId, '' );
		foreach ( $aAlbums as $aAlbum ) {
			// if ( 'Random Clubbing' == $aAlbum['name'] ) {
			if ( 'profile' == $aAlbum['type'] ) {
				// echo $aAlbum['aid'];
				return $this->_oFb->api_client->photos_upload(
					$sLocalImagePath, $aAlbum['aid'], $sCaption, $this->_iId
				);
			}
		}
	} */
	
}

