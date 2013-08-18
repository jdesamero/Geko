<?php

//
class Geko_String_Highlight
{
	protected $sTokens = ' -_Ð.?!,';
	protected $sStartHighlight = '<strong>';
	protected $sEndHighlight = '</strong>';
	protected $iTruncateLen = 250;
	protected $iPaddingLen = 25;
	protected $sEllipsis = '...';
	protected $aWords = array();
	
	
	// constructor
	public function __construct( $aParams = array() )
	{
		$this->setParams( $aParams );
	}
	
	//
	public function setParams( $aParams )
	{
		if ( $aParams['tokens'] ) $this->sTokens = $aParams['tokens'];
		if ( $aParams['start_highlight'] ) $this->sStartHighlight = $aParams['start_highlight'];
		if ( $aParams['end_highlight'] ) $this->sEndHighlight = $aParams['end_highlight'];
		if ( $aParams['truncate_len'] ) $this->iTruncateLen = $aParams['truncate_len'];
		if ( $aParams['padding_len'] ) $this->iPaddingLen = $aParams['padding_len'];
		if ( $aParams['ellipsis'] ) $this->sEllipsis = $aParams['ellipsis'];
		if ( $aParams['keywords'] ) $this->aWords = $this->splitWords( $aParams['keywords'] );
	}
	
	
	//// helpers
	
	//
	public function trimStart( $sPhrase, $sTokens = NULL )
	{
		if ( NULL === $sTokens ) $sTokens = $this->sTokens;		
		
		$iLen = strlen( $sPhrase );
		
		$sPhrase = ltrim( $sPhrase, $sTokens );
		$iTrimLen = strlen( $sPhrase );
				
		if ( $iLen > $iTrimLen ) return $sPhrase;
		
		$iStartChop = strlen( strtok( $sPhrase, $sTokens ) );
		return ltrim( substr( $sPhrase, $iStartChop ), $sTokens );
	}
	
	//
	public function trimEnd( $sPhrase, $sTokens = NULL )
	{
		return strrev( $this->trimStart( strrev( $sPhrase ), $sTokens ) );
	}
	
	//
	public function splitWords( $sKeywords )
	{
		return Geko_Array::explodeTrim(
			' ', $sKeywords, array( 'remove_empty' => TRUE )
		);
	}
	
	//
	public function getFirstPos( $sPhrase, $aWords )
	{
		$iPos = strlen( $sPhrase );
		foreach ( $aWords as $sWord ) {
			$iCheckPos = stripos( $sPhrase, $sWord );
			if ( ( FALSE !== $iCheckPos ) && ( $iCheckPos < $iPos ) ) {
				$iPos = $iCheckPos;
			}
		}
		return $iPos;
	}
	
	
	
	//// output functions
	
	//
	public function highlightPhrase( $sPhrase, $mWords, $sStartHighlight = NULL, $sEndHighlight = NULL )
	{
		if ( !is_array( $mWords ) ) {
			$aWords = $this->splitWords( $mWords );
		} else {
			$aWords = $mWords;
		}
		
		if ( NULL === $sStartHighlight ) $sStartHighlight = $this->sStartHighlight;
		if ( NULL === $sEndHighlight ) $sEndHighlight = $this->sEndHighlight;
		
		foreach ( $aWords as $sWord ) {
			$sPhrase = preg_replace( '/' . $sWord . '/si', $sStartHighlight . '\0' . $sEndHighlight, $sPhrase );
		}
		
		return $sPhrase;
	}
	
	//
	public function searchHighlight( $sPhrase, $sKeywords = NULL, $iTruncateLen = NULL, $iPaddingLen = NULL )
	{
		if ( NULL === $iTruncateLen ) $iTruncateLen = $this->iTruncateLen;
		if ( NULL === $iPaddingLen ) $iPaddingLen = $this->iPaddingLen;
		
		if ( NULL === $sKeywords ) {
			$aWords = $this->aWords;
		} else {
			$aWords = $this->splitWords( $sKeywords );
		}
		
		$iPhraseLen = strlen( $sPhrase );
		
		$iFirstKwPos = $this->getFirstPos( $sPhrase, $aWords );
		$sFmt = '';
		
		if ( $iPhraseLen > $iTruncateLen ) {
			
			// determine where to trim
			if ( $iFirstKwPos > $iPaddingLen ) {
				
				$iStartPos = $iFirstKwPos - $iPaddingLen;
				
				if ( ( $iPhraseLen - $iStartPos ) > $iTruncateLen ) {
					// trim both ends
					$sFmt = $this->sEllipsis . $this->trimEnd( $this->trimStart( substr( $sPhrase, $iStartPos, $iTruncateLen ) ) ) . $this->sEllipsis;				
				} else {
					// trim the start part
					$sFmt = $this->sEllipsis . $this->trimStart( substr( $sPhrase, $iPhraseLen - $iTruncateLen ) );
				}
				
			} else {
				// trim the end part
				$sFmt = $this->trimEnd( substr( $sPhrase, 0, $iTruncateLen ) ) . $this->sEllipsis;
			}
			
		} else {
			// no trimming needed
			$sFmt = $sPhrase;
		}
		
		return $this->highlightPhrase( $sFmt, $aWords );
	}
	
}

