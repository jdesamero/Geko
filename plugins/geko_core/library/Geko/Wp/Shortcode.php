<?php

//
class Geko_Wp_Shortcode extends Geko_Singleton_Abstract
{
	
	protected $_bCalledInit = FALSE;
	
	protected $_aOverrides = array();
	
	
	protected $_aShortcodes = array(
		'imageCaption' => array( 'img_caption_shortcode', 10, 3 )
	);
	
	
	//
	public function init() {
		
		if ( !$this->_bCalledInit ) {
			
			foreach ( $this->_aOverrides as $sKey ) {
				
				if ( $aShortcode = $this->_aShortcodes[ $sKey ] ) {
					
					list( $sFilter, $iPriority, $iNumArgs ) = $aShortcode;

					// Add the filter to override the standard shortcode
					add_filter( $sFilter, array( $this, $sKey ), $iPriority, $iNumArgs );
				}
			}
			
			$this->_bCalledInit = TRUE;
		}
		
	}
	
	
	//
	public function setOverrides() {
		
		$aOverrides = func_get_args();
		$this->_aOverrides = array_merge( $this->_aOverrides, $aOverrides );
		
		return $this;
	}
	
	//
	public function resetOverrides() {
		
		$this->_aOverrides = array();
		
		return $this;
	}
	
	
	// http://sevenspark.com/code/how-to-add-links-to-wordpress-image-captions
	public function imageCaption( $a, $attr, $content = NULL ) {
		
		extract( shortcode_atts( array(
			'id'    => '',
			'align' => 'alignnone',
			'width' => '',
			'caption' => '',
			'popup_href' => '',
			'popup_width' => 400,
			'popup_height' => 400
		), $attr ) );
		
		if ( 1 > ( int ) $width || empty( $caption ) )
			return $content;
	 
		if ( $id ) $id = 'id="' . esc_attr( $id ) . '" ';
		
		if ( $popup_href ) {
			
			$sOnClick = sprintf(
				"MM_openBrWindow( '%s', '', 'width=%d,height=%d' ); return false;",
				str_replace( '/', '\/', $popup_href ), $popup_width, $popup_height
			);
			
			$content = sprintf( '<a href="#" onClick="%s">%s</a>', $sOnClick, $content );
			
			$caption = sprintf( '<a href="#" onClick="%s">%s</a>', $sOnClick, $caption );
			
		}
		
		return sprintf(
			'<div %s class="wp-caption %s" style="width: %dpx">%s<p class="wp-caption-text">%s</p></div>',
			$id, esc_attr( $align ), ( 10 + ( int ) $width ), do_shortcode( $content ), $caption
		);
	}
	
	
	
}



