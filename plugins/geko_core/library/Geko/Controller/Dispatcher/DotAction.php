<?php

// extend the standard dispatcher
class Geko_Controller_Dispatcher_DotAction extends Zend_Controller_Dispatcher_Standard
{
    public function formatActionName($sUnformatted)
    {
        // create an anonymous function to inflect unformatted action name
        // news.sub.section = newsSubSectionAction
        $fInflect = create_function(
			'$aMatches',
			'return strtoupper($aMatches[1]);'
		);
        
        $sFormatted = preg_replace_callback(
            '/\.([a-zA-Z0-9_])/',
            $fInflect,
            $sUnformatted
        ) . 'Action';
        
        return $sFormatted;
    }
}

