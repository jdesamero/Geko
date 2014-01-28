<?php

/**
 * The wpCSL Themes Class
 *
 * @package wpCSL\Themes
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 *
 */
class wpCSL_themes__slplus {

    /**
     * Theme constructor.
     * 
     * @param mixed[] $params named array of properties
     */
    function __construct($params) {
        
        // Properties with default values
        //
        $this->columns = 1;                 // How many columns/row in our display output.
        $this->css_dir = 'css/';
        
        foreach ($params as $name => $value) {            
            $this->$name = $value;
        }

        // Remember the base directory path, then
        // Append plugin path to the directories
        //
        $this->css_url = $this->plugin_url . '/'. $this->css_dir;
        $this->css_dir = $this->plugin_path . $this->css_dir;       
    }
    
    /*-------------------------------------
     * method: add_admin_settings
     *
     * Add the theme settings to the admin panel.
     *
     */
    function add_admin_settings($settingsObj = null) {        
        if ($settingsObj == null) {
            $settingsObj = $this->settings;
        }
        
        // Exit is directory does not exist
        //
        if (!is_dir($this->css_dir)) {
            if (isset($this->notifications)) {
                $this->notifications->add_notice(
                    2,
                    sprintf(
                        __('The theme directory:<br/>%s<br/>is missing. ' .
                            'Create it to enable themes and get rid of this message.',
                            WPCSL__slplus__VERSION
                            ),                        
                        $this->css_dir
                        )
                );
            }            
            return;
        }

        // The Themes
        // No themes? Force the default at least
        //
        $themeArray = get_option($this->prefix.'-theme_array');
        if (count($themeArray, COUNT_RECURSIVE) < 2) {
            $themeArray = array('Default' => 'default');
        } 
    
        // Check for theme files
        //
        $lastNewThemeDate = get_option($this->prefix.'-theme_lastupdated');
        $newEntry = array();
        if ($dh = opendir($this->css_dir)) {
            while (($file = readdir($dh)) !== false) {
                
                // If not a hidden file
                //
                if (!preg_match('/^\./',$file)) {                
                    $thisFileModTime = filemtime($this->css_dir.$file);
                    
                    // We have a new theme file possibly...
                    //
                    if ($thisFileModTime > $lastNewThemeDate) {
                        $newEntry = $this->GetThemeInfo($this->css_dir.$file);
                        $themeArray = array_merge($themeArray, array($newEntry['label'] => $newEntry['file']));                                        
                        update_option($this->prefix.'-theme_lastupdated', $thisFileModTime);
                    }
                }
            }
            closedir($dh);
        }


        // Remove empties and sort
        $themeArray = array_filter($themeArray);
        ksort($themeArray);

        // Delete the default theme if we have specific ones
        //
        $resetDefault = false;
        
        if ((count($themeArray, COUNT_RECURSIVE) > 1) && isset($themeArray['Default'])){        
            unset($themeArray['Default']);
            $resetDefault = true;
        }

        // We added at least one new theme
        //
        if ((count($newEntry, COUNT_RECURSIVE) > 1) || $resetDefault) {
            update_option($this->prefix.'-theme_array',$themeArray);
        }  
                            
        $settingsObj->add_item(
            __('Display Settings',WPCSL__slplus__VERSION), 
            __('Select A Theme',WPCSL__slplus__VERSION),   
            'theme',    
            'list', 
            false, 
            __('How should the plugin UI elements look?  Check the <a href="'.
                $this->support_url.
                '" target="CSA">documentation</a> for more info.',
                WPCSL__slplus__VERSION),
            $themeArray,
            'default'
        );        
    }    
    

    /**
     * Extract the label & key from a CSS file header.
     *
     * @param string $filename - a fully qualified path to a CSS file
     * @return mixed - a named array of the data.
     */
    function GetThemeInfo ($filename) {
        $dataBack = array();
        if ($filename != '') {
           $default_headers = array(
                'columns' => 'columns',
                'description' => 'description',
                'file' => 'file',
                'label' => 'label',
               );

           $dataBack = get_file_data($filename,$default_headers,'');
           $dataBack['file'] = preg_replace('/.css$/','',$dataBack['file']);
        }

        return $dataBack;
     }

     /**
      * Configure the plugin theme drivers based on the theme file meta data.
      *
      * @param string $themeFile - the base name of the theme file (xyz.css)
      */
     function configure_theme($themeFile) {
        $newEntry = $this->GetThemeInfo($this->css_dir.$themeFile);
        if (isset($this->products)) {
            $this->products->columns = (isset($newEntry['columns'])?$newEntry['columns']:0);
        }
     }
     

    /**************************************
     ** function: assign_user_stylesheet
     **
     ** Set the user stylesheet to what we selected.
     **
     ** For this to work with shortcode testing you MUST call it
     ** via the WordPress wp_footer action hook.
     **
     ** Parameters:
     **     themeFile    string  - if set use this theme v. the database setting
     **
     **/
    function assign_user_stylesheet($themeFile = '',$preRendering = false) {
        // If themefile not passed, fetch from db
        //
        if ($themeFile == '') {
            $themeFile = get_option($this->prefix.'-theme','default') . '.css';

        } else {
            // append .css if left off
            if ((strlen($themeFile) < 4) || substr_compare($themeFile, '.css', -strlen('.css'), strlen('.css')) != 0) {
                $themeFile .= '.css';
            }
        }

        // go to default if theme file is missing
        //
        if ( !file_exists($this->css_dir.$themeFile)) {
            $themeFile = 'default.css';
        }

        // If the theme file exists (after forcing default if necessary)
        // queue it up
        //
        if ( file_exists($this->css_dir.$themeFile)) {
            wp_deregister_style($this->prefix.'_user_header_css');
            wp_dequeue_style($this->prefix.'_user_header_css');
            if ($this->parent->shortcode_was_rendered || $preRendering) {
                wp_enqueue_style($this->prefix.'_user_header_css', $this->css_url .$themeFile);
            }
            $this->configure_theme($themeFile);
        }
    }  
}
