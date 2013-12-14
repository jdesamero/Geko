<?php

//
class Geko_Wp_Sort
{
	
	protected $sDefaultOrderBy = '';
	protected $sOrderByVarName = 'orderby';
	protected $sDefaultOrder = 'ASC';
	protected $sOrderVarName = 'order';
	
	protected $sImageDir = '';
	protected $sAscIconUrl = '';
	protected $sDescIconUrl = '';
	
	protected $sIconTag = '<img src="%s" border="0" class="sort_indicator" />';
	protected $sIconSortLink = '<a href="%s" class="sort_link">%s</a>';
	protected $sTitleSortLink = '<a href="%s" class="sort_link">%s</a>';
	protected $sIconPlacement = 'before';										// [before|after]
	
	
	//
	public function __construct( $aParams = array() ) {
		
		// set array params
		if ( $aParams[ 'default_orderby' ] ) $this->sDefaultOrderBy = $aParams[ 'default_orderby' ];
		if ( $aParams[ 'orderby_var_name' ] ) $this->sOrderByVarName = $aParams[ 'orderby_var_name' ];
		if ( $aParams[ 'default_order' ] ) $this->sDefaultOrder = $aParams[ 'default_order' ];
		if ( $aParams[ 'order_var_name' ] ) $this->sOrderVarName = $aParams[ 'order_var_name' ];
		
		if ( $aParams[ 'image_dir' ] ) $this->sImageDir = $aParams[ 'image_dir' ];
		if ( $aParams[ 'asc_icon_url' ] ) $this->sAscIconUrl = $aParams[ 'asc_icon_url' ];
		if ( $aParams[ 'desc_icon_url' ] ) $this->sDescIconUrl = $aParams[ 'desc_icon_url' ];
		
		if ( $aParams[ 'icon_tag' ] ) $this->sIconTag = $aParams['icon_tag'];
		if ( $aParams[ 'icon_sort_link' ] ) $this->sIconSortLink = $aParams[ 'icon_sort_link' ];
		if ( $aParams[ 'title_sort_link' ] ) $this->sTitleSortLink = $aParams[ 'title_sort_link' ];
		if ( $aParams[ 'icon_placement' ] ) $this->sIconPlacement = $aParams[ 'icon_placement' ];
		
		// set default values
		if ( !$this->sImageDir ) $this->sImageDir = get_bloginfo( 'template_directory' ) . '/images';
		
	}
	
	
	//// accessors
	
	//
	public function setDefaultOrderBy( $sDefaultOrderBy ) {
		$this->sDefaultOrderBy = $sDefaultOrderBy;
		return $this;
	}
	
	//
	public function setOrderByVarName( $sOrderByVarName ) {
		$this->sOrderByVarName = $sOrderByVarName;
		return $this;
	}
	
	//
	public function setDefaultOrder( $sDefaultOrder ) {
		$this->sDefaultOrder = $sDefaultOrder;
		return $this;
	}
	
	//
	public function setOrderVarName( $sOrderVarName ) {
		$this->sOrderVarName = $sOrderVarName;
		return $this;
	}

	//
	public function setImageDir( $sImageDir ) {
		$this->sImageDir = $sImageDir;
		return $this;
	}
	
	//
	public function setAscIconUrl( $sAscIconUrl ) {
		$this->sAscIconUrl = $sAscIconUrl;
		return $this;
	}
	
	//
	public function setDescIconUrl( $sDescIconUrl ) {
		$this->sDescIconUrl = $sDescIconUrl;
		return $this;
	}
	
	//
	public function setIconTag( $sIconTag ) {
		$this->sIconTag = $sIconTag;
		return $this;
	}
	
	//
	public function setIconSortLink( $sIconSortLink ) {
		$this->sIconSortLink = $sIconSortLink;
		return $this;
	}
	
	//
	public function setTitleSortLink( $sTitleSortLink ) {
		$this->sTitleSortLink = $sTitleSortLink;
		return $this;
	}
	
	//
	public function setIconPlacement( $sIconPlacement ) {
		$this->sIconPlacement = $sIconPlacement;
		return $this;
	}
	
	
	
	
	//
	public function getAscIconUrl( $sAscIconUrl ) {
		return ( $this->sAscIconUrl ) ? $this->sAscIconUrl : $this->sImageDir . '/sort_asc.gif' ;
	}
	
	//
	public function getDescIconUrl( $sDescIconUrl ) {
		return ( $this->sDescIconUrl ) ? $this->sDescIconUrl : $this->sImageDir . '/sort_desc.gif' ;
	}

	
	
	
	//// main methods
	
	//
	public function setQueryParam( $aParams ) {
		
		$sObvn = $this->sOrderByVarName;
		$sOvn = $this->sOrderVarName;
		
		if ( isset( $_GET[ $sObvn ] ) ) $aParams[ $sObvn ] = $_GET[ $sObvn ];
		if ( isset( $_GET[ $sOvn ] ) ) $aParams[ $sOvn ] = $_GET[ $sOvn ];
		
		if ( !$aParams[ $sObvn ] && $this->sDefaultOrderBy ) $aParams[ $sObvn ] = $this->sDefaultOrderBy;
		if ( !$aParams[ $sOvn ] && $this->sDefaultOrder ) $aParams[ $sOvn ] = $this->sDefaultOrder;
		
		return $aParams;
	}
	
	
	
	//
	public function getLink( $sTitle, $sThisField = '', $sDefaultOrder = 'ASC', $bIconFlip = FALSE ) {
		
		// determine the actual field to use in the query
		if ( '' == $sThisField ) {
			$sThisField = str_replace( '-', '_', sanitize_title( $sTitle ) );
		}
		
		$oUrl = new Geko_Uri();											// get the current url
		$bCurrent = FALSE;												// set flag which determines if sorting by this field
		
		// get sort values from the current url
		$sUrlOrderBy = $oUrl->getVar( 'orderby' );
		$sUrlOrder = strtoupper( $oUrl->getVar( 'order' ) );
		
		if ( !$sUrlOrderBy && ( $sThisField == $this->sDefaultOrderBy ) ) {
			// use this field for default sorting
			$sUrlOrderBy = $sThisField;
		}

		if ( !$sUrlOrder ) $sUrlOrder = $sDefaultOrder;
		
		// set toggle logic
		if ( $sThisField == $sUrlOrderBy ) {
			// toggle order
			$sOrderAct = $sOrder = $sUrlOrder;
			$sOrder = ( 'ASC' == $sOrder ) ? 'DESC' : 'ASC';
			$bCurrent = TRUE;
		} else {
			$sOrderAct = $sOrder = $sDefaultOrder;
		}
		
		// set the URL for this sort link
		$oUrl
			->setVar( 'orderby', $sThisField )
			->setVar( 'order', $sOrder )
		;
		
		$sTitleLink = sprintf( $this->sTitleSortLink, strval( $oUrl ), $sTitle );
		
		// attach sort indicator
		if ( $bCurrent ) {
			
			if ( $bIconFlip ) $sOrderIcon = ( 'ASC' == $sOrderAct ) ? 'DESC' : 'ASC';
			else $sOrderIcon = $sOrderAct;
			
			$sIconSrc = ( 'ASC' == $sOrderIcon ) ? $this->getAscIconUrl() : $this->getDescIconUrl();
			$sIconTag = sprintf( $this->sIconTag, $sIconSrc );
			$sIconLink = sprintf( $this->sIconSortLink, strval( $oUrl ), $sIconTag );
			
			if ( 'after' == $this->sIconPlacement ) $sTitleLink .= ' ' . $sIconLink;
			else $sTitleLink = $sIconLink . ' ' . $sTitleLink;
			
		}
		
		return $sTitleLink;
	}
	
	
	//
	public function echoLink( $sTitle, $sThisField = '', $sDefaultOrder = 'ASC', $bIconFlip = FALSE ) {
		echo $this->getLink( $sTitle, $sThisField, $sDefaultOrder, $bIconFlip );
	}
	
	
}



