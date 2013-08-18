<?php

//
class Geko_Fb_PhotoAlbum extends Geko_Fb_Entity
{
	protected $_sEntityIdVarName = 'aids';
	
	//
	public function init()
	{
		$this
			->setEntityMapping( 'id', 'aid' )
			->setEntityMapping( 'title', 'name' )
			// ->setEntityMapping( 'date_created', 'created' )
			// ->setEntityMapping( 'date_modified', 'modified' )
		;
		
		return parent::init();
	}
	
	
	//
	public function retPermalink()
	{
		return $this->getEntityPropertyValue( 'link' );
	}

	
	/*
	//
	public function getIdFromEntity()
	{
		return $this->_aEntity['aid'];
	}
	
	//
	public function getPhotos( $aParams = array() )
	{
		return $this->formatEntityArray(
			'Geko_Fb_Photo',
			$this->_oFb->api_client->photos_get( '', $this->_iId, '' ),
			$aParams
		);
	}
	*/
	
}

/*

[aid] =&gt; 100000836651182_-3
[cover_pid] =&gt; 100000836651182_9449
[owner] =&gt; 100000836651182
[name] =&gt; Profile Pictures
[created] =&gt; 1266721089
[modified] =&gt; 1266721089
[description] =&gt; 
[location] =&gt; 
[link] =&gt; http://www.facebook.com/album.php?aid=-3&id=100000836651182
[edit_link] =&gt; http://www.facebook.com/editphoto.php?aid=-3
[size] =&gt; 1
[visible] =&gt; everyone
[type] =&gt; profile
[object_id] =&gt; 0
[can_upload] =&gt; 0
[modified_major] =&gt; 1266721089

*/