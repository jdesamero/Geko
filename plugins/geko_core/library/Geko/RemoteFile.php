<?php
/*
 * "geko_core/library/Geko/RemoteFile.php"
 * https://github.com/jdesamero/Geko
 *
 * Copyright (c) 2013 Joel Desamero.
 * Licensed under the MIT license.
 *
 * remote file functions
 */

//
class Geko_RemoteFile
{
	
	// very basic, only works with simple http:// urls
	public static function getInfo($sUrl) {
		
		$aUrl = parse_url($sUrl);
		
		if ('http' == $aUrl['scheme']) {
		
			// assuming $aUrl['scheme'] is 'http'
			$rFileHandle = fsockopen($aUrl['host'], 80);
			
			if ($rFileHandle) {
				
				fputs(
					$rFileHandle,
					'HEAD ' . $aUrl['path'] . ' HTTP/1.1' . "\n" .
					'Host: ' . $aUrl['host'] . "\n\n"
				);
				
				$sRead = fread($rFileHandle, 8192);
				
				fclose($rFileHandle);
				
				if ($sRead == "HTTP/1.1 404 Not Found") {
					return FALSE;
				} else {
					
					preg_match_all('/([a-zA-Z0-9-]+):(.+)/', $sRead, $aRegs);
					
					$aRes = array();
					foreach ($aRegs[1] as $i => $sValue)
					{
						$aRes[strtolower($sValue)] = trim($aRegs[2][$i]);
					}
					
					return $aRes;
				}
		
			} else {
				return FALSE;
			}
		
		} else {
			
			// unknown scheme
			return FALSE;
			
		}
	}
	
	
	//
	public static function getContents($sUrl)
	{
		if (FALSE == (bool) ini_get('allow_url_fopen')) {
			
			if (TRUE == function_exists('curl_init')) {
			
				// use cURL instead
				
				$rCh = curl_init();
				
				curl_setopt($rCh, CURLOPT_URL, $sUrl);
				curl_setopt($rCh, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($rCh, CURLOPT_CONNECTTIMEOUT, 0);
				$sFileContents = curl_exec($rCh);
				curl_close($rCh);
				
				// display file
				return $sFileContents;
			
			} else {
				return FALSE;
			}
			
		} else {
			
			return file_get_contents($sUrl);
			
		}
	}
}



