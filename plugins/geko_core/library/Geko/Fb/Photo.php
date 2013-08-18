<?php

//
class Geko_Fb_Photo extends Geko_Fb_Entity
{	
	protected $_sEntityIdVarName = 'pids';
	
	//
	public function init()
	{
		$this
			->setEntityMapping( 'id', 'pid' )
			->setEntityMapping( 'title', 'caption' )
			// ->setEntityMapping( 'date_created', 'created' )
			// ->setEntityMapping( 'date_modified', 'modified' )
		;
		
		return parent::init();
	}
	
	
	/*
	
	//
	public static function fetch( $oFb, $aParams )
	{
		$aPhotos = $oFb->api_client->photos_get(
			$aParams['subj_id'], $aParams['aid'], $aParams['pids']
		);
		
		$sClass = __CLASS__;
		
		$aFmt = array();
		foreach ( $aPhotos as $aPhoto ) {
			$aFmt[] = new $sClass( $oFb, $aPhoto );
		}
		
		if ( 1 == count( $aFmt ) ) {
			return $aFmt[0];
		} else {
			return $aFmt;
		}
	}
	
	
	//
	public function getIdFromEntity()
	{
		return $this->_aEntity['pid'];
	}
	
	*/
	
	
	//
	public function getSmlImgTag( $aAtts = array() )
	{
		$aAtts = Geko_Html::assignAtts(
			$aAtts,
			array(
				'src' => $this->getSrcSmall(),
				'width' => $this->getSrcSmallWidth(),
				'height' => $this->getSrcSmallHeight()
			)
		);
		
		return $this->_getImgTag( $aAtts );
	}
	
	//
	public function getMedImgTag( $aAtts = array() )
	{
		$aAtts = Geko_Html::assignAtts(
			$aAtts,
			array(
				'src' => $this->getSrc(),
				'width' => $this->getSrcWidth(),
				'height' => $this->getSrcHeight()
			)
		);
		
		return $this->_getImgTag( $aAtts );
	}
	
	//
	public function getImgTag( $aAtts = array() )
	{
		$aAtts = Geko_Html::assignAtts(
			$aAtts,
			array(
				'src' => $this->getSrcBig(),
				'width' => $this->getSrcBigWidth(),
				'height' => $this->getSrcBigHeight()
			)
		);
		
		return $this->_getImgTag( $aAtts );
	}
	
	//
	protected function _getImgTag( $aAtts )
	{
		$aAtts = Geko_Html::assignAtt( $aAtts, 'alt', $this->getCaption() );
		
		return '<img ' . Geko_Html::formatAsAtts( $aAtts ) . ' />';
	}
	
}


/*
[pid] =&gt; 100000836651182_9502
[aid] =&gt; 100000836651182_786
[owner] =&gt; 100000836651182
[src_small] =&gt; http://photos-h.ak.fbcdn.net/hphotos-ak-snc3/hs495.snc3/27041_100462483324973_100000836651182_9502_5252584_t.jpg
[src_small_width] =&gt; 75
[src_small_height] =&gt; 88
[src_big] =&gt; http://photos-h.ak.fbcdn.net/hphotos-ak-snc3/hs495.snc3/27041_100462483324973_100000836651182_9502_5252584_n.jpg
[src_big_width] =&gt; 469
[src_big_height] =&gt; 555
[src] =&gt; http://photos-h.ak.fbcdn.net/hphotos-ak-snc3/hs495.snc3/27041_100462483324973_100000836651182_9502_5252584_s.jpg
[src_width] =&gt; 109
[src_height] =&gt; 130
[link] =&gt; http://www.facebook.com/photo.php?pid=9502&id=100000836651182
[caption] =&gt; 
[created] =&gt; 1266727819
[modified] =&gt; 1266727819
[object_id] =&gt; 100462483324973
[album_object_id] =&gt; 100462433324978
*/

