<?php
/**
 * Helper, non-critical methods to make WordPress plugins easier to manage.
 *
 * Mostly does things like execute and output PHP files as strings or direct
 * output to the "screen" to facilitate PHP template files.  More will come
 * over time.
 *
 * @author Lance Cleveland <lance@lancecleveland.com>
 * @copyright (c) 2013, Lance Cleveland
 *
 * @since 2.0.0
 * @version 2.0.13
 *
 * @package wpCSL\wpCSL_helper
 */


class wpCSL_helper__slplus {
    private $bugoutDivCount;

    /**
     *
     * @param type $params
     */
    function __construct($params=null) {

        // Defaults
        //
        $this->bugoutDivCount = 0;

        // Set by incoming parameters
        //
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }

        // Override incoming parameters

    }


    /**
     * Executes the included php (or html) file and returns the output as a string.
     *
     * Parameters:
     * @param string $file - required fully qualified file name
     */
    function get_string_from_phpexec($file) {
        if (file_exists($file)) {
            ob_start();
            include($file);
            return ob_get_clean();
        }
    }
    
    
     
    /**
     *
     * Executes the a php file in ./templates/ file and prints out the results.
     *
     * Makes for easy include templates that depend on processing logic to be
     * dumped mid-stream into a WordPress page. 
     *
     * @param string $file - required file name in the ./templates directory
     * @param type $dir - optional directory path, defaults to plugin_dir_path
     */
    function execute_and_output_template($file,$dir=null) {
        if ($dir === null) {
            $dir = $this->parent->plugin_path;
        }
        print $this->get_string_from_phpexec($dir.'templates/'.$file);
    }

    /**
     * Render a debugging div.
     *
     * @param string $message - what you want to say
     * @param string $instructions - additonal notes, like how to turn off debugging
     */
    /**
     * Render a debugging div.
     *
     * @param string $message - what you want to say
     * @param string $instructions - additonal notes, like how to turn off debugging
     */
    function bugout($message='',$instructions='', $title='', $file='',$line='') {
        if (($message != '') && ($this->parent->debugging)) {
            print "<div class='debugging_wrap' ".
                    'onClick="jQuery(\'#bugout_'.$this->bugoutDivCount.'\').toggle(\'slow\');" ' .
                    '>' .
                    (($title!='')?"$title - ":'').
                    __('Click me to see the debugging goodness...', 'wpcsl') . '<br/>'
                    ;
            print "<div class='debugging' id='bugout_".$this->bugoutDivCount."' name='bugout_".$this->bugoutDivCount."' style='display:none;'>";
            if ($instructions == '') {
                $instructions = $this->parent->debug_instructions;
            }
            if ($instructions != '') {
                print $instructions . "<br/>\n";
            }
            if (($file != '') && ($line != '')) {
                print "From $file at $line<br/>\n";
            }
            print $message . '</div></div>';
            $this->bugoutDivCount++;
        }
    }
    

    /**
     * Convert text in the WP readme file format (wiki markup) to basic HTML
     *
     * Parameters:
     * @param string $file - optional name of the file in the plugin dir defaults to readme.txt
     * @param type $dir - optional directory path, defaults to plugin_dir_path
     */
    function convert_text_to_html($file='readme.txt',$dir=null) {
        if ($dir === null) {
            $dir = $this->parent->plugin_path;
        }
        ob_start();
        include($dir.$file);
        $content=ob_get_contents();
        ob_end_clean();
        $content=preg_replace('#\=\=\= #', "<h2>", $content);
        $content=preg_replace('# \=\=\=#', "</h2>", $content);
        $content=preg_replace('#\=\= #', "<div id='wphead' style='color:white'><h1 id='site-heading'><span id='site-title'>", $content);
        $content=preg_replace('# \=\=#', "</h1></span></div>", $content);
        $content=preg_replace('#\= #', "<b><u>", $content);
        $content=preg_replace('# \=#', "</u></b>", $content);
        $content=do_hyperlink($content);
        return nl2br($content);
    }
 

        /**
         * Create a help div next to a settings entry.
         *
         * @param string $divname - name of the div
         * @param string $msg - the message to dislpay
         * @return string - the HTML
         */
        function CreateHelpDiv($divname,$msg) {
            $jqDivName = str_replace(']','\\\\]',str_replace('[','\\\\[',$divname));
            return "<a class='moreinfo_clicker' onclick=\"jQuery('div#".$this->parent->css_prefix."-help$jqDivName').toggle('slow');\" href=\"javascript:;\">".
                '<div class="'.$this->parent->css_prefix.'-moreicon" title="click for more info"><br/></div>'.
                "</a>".
                "<div id='".$this->parent->css_prefix."-help$divname' class='input_note' style='display: none;'>".
                    $msg.
                "</div>"
                ;

            }

        /**
         * Generate the HTML for a sub-heading label in a settings panel.
         *
         * @param string $label
         * @return string HTML
         */
        function create_SubheadingLabel($label) {
            return "<p class='slp_admin_info'><strong>$label</strong></p>";
        }

        /**
         * Generate the HTML for a checkbox settings interface element.
         *
         * @param string $boxname - the name of the checkbox (db option name)
         * @param string $label - default '', the label to go in front of the checkbox
         * @param string $msg - default '', the help message
         * @param string $prefix - defaults to SLPLUS_PREFIX, can be ''
         * @param boolean $disabled - defaults to false
         * @param mixed $default
         * @return type
         */
        function CreateCheckboxDiv($boxname,$label='',$msg='',$prefix=null, $disabled=false, $default=0) {
            if ($prefix === null) { $prefix = $this->parent->prefix; }
            $whichbox = $prefix.$boxname;
            return
                "<div class='form_entry'>".
                    "<div class='".$this->parent->css_prefix."-input'>" .
                    "<label  for='$whichbox' ".
                        ($disabled?"class='disabled '":' ').
                        ">$label:</label>".
                    "<input name='$whichbox' value='1' ".
                        "type='checkbox' ".
                        ((get_option($whichbox,$default) ==1)?' checked ':' ').
                        ($disabled?"disabled='disabled'":' ') .
                    ">".
                    "</div>".
                    $this->CreateHelpDiv($boxname,$msg) .
                "</div>"
                ;
            }

    /**
     * Generate a consistent HTML wrapper for a message in the admin panels.
     */
    function create_SimpleMessage($message) {
        return '<p class="message">'.$message.'</p>';
    }

    /**
     * function: SavePostToOptionsTable
     */
    function SavePostToOptionsTable($optionname,$default=null) {
        if ($default != null) {
            if (!isset($_POST[$optionname])) {
                $_POST[$optionname] = $default;
            }
        }
        if (isset($_POST[$optionname])) {
            $_POST[$optionname] = stripslashes_deep($_POST[$optionname]);
            update_option($optionname,$_POST[$optionname]);
        }
    }

    /**************************************
     ** function: SaveCheckboxToDB
     **
     ** Update the checkbox setting in the database.
     **
     ** Parameters:
     **  $boxname (string, required) - the name of the checkbox (db option name)
     **  $prefix (string, optional) - defaults to SLPLUS_PREFIX, can be ''
     **/
    function SaveCheckboxToDB($boxname,$prefix = null, $separator='-') {
        if ($prefix === null) { $prefix = $this->parent->prefix; }
        $whichbox = $prefix.$separator.$boxname;
        $_POST[$whichbox] = (isset($_POST[$whichbox])&&!empty($_POST[$whichbox]))?1:0;
        $this->SavePostToOptionsTable($whichbox,0);
    }

    /**
     * Saves a textbox from an option input form to the options table.
     *
     * @param string $boxname - base name of the option
     * @param string $prefix - the plugin prefix
     * @param string $separator - the separator char
     */
    function SaveTextboxToDB($boxname,$prefix = null, $separator='-') {
        if ($prefix === null) { $prefix = $this->parent->prefix; }
        $whichbox = $prefix.$separator.$boxname;
        $this->SavePostToOptionsTable($whichbox);
    }

    /**
     * Check if an item exists out there in the "ether".
     *
     * @param string $url - preferably a fully qualified URL
     * @return boolean - true if it is out there somewhere
     */
    function webItemExists($url) {
        if (($url == '') || ($url === null)) { return false; }
        $response = wp_remote_head( $url, array( 'timeout' => 5 ) );
        $accepted_status_codes = array( 200, 301, 302 );
        if ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), $accepted_status_codes ) ) {
            return true;
        }
        return false;
    }

    /**
     * Set an extended data attribute if it is not already set.
     *
     * Puts info in the data[] named array for the object base on
     * the results returned by the passed function.
     *
     * $function must be:
     *    'get_option'  - where the element is the data name, params[0] or params = FULL database option name
     *    'get_item'    - where the element is the data name, params[0] or params = base option name (prefix & hyphen are prepended)
     *    anon function - anon function returns a value which goes into data[]
     *
     * If $params is null and function is get_item the param will fetch the option = to the element name.
     *
     * @param string $element - the key for the data named array
     * @param mixed $function - the string 'get_option','get_item' or a pointer to anon function
     * @param mixed $params - an array of parameters to pass to get_option or the anon, note: get_option can receive an array of option_name, default value
     * @param mixed $default - default value for 'get_item' calls
     * @param boolean $forceReload - if set, reload the data element from the options table
     * @param boolean $cantBeEmpty - if set and the data is empty, set it to default
     * @return the value
     */
    function getData($element = null, $function = null, $params=null, $default=null, $forceReload = false, $cantBeEmpty = false) {
        if ($element  === null) { return; }
        if ($function === null) { return; }
        if (!isset($this->parent->data[$element] ) || $forceReload) {

           // get_option shortcut, fetch the option named by params
           //
           if ($function === 'get_option') {
               if (is_array($params)) {
                    $this->parent->data[$element] = get_option($params[0],$params[1]);
                } else {
                    if ($params === null) { $params = $element; }
                    $this->parent->data[$element] =
                        ($default == null) ?
                            get_option($params) :
                            get_option($params,$default);
                }

           // get_item shortcut
           //
           } else if ($function === 'get_item') {
               if (is_array($params)) {
                    $this->parent->data[$element] = $this->parent->settings->get_item($params[0],$params[1],'-',$forceReload);
                } else {
                    if ($params === null) { $params = $element; }
                    $this->parent->data[$element] = $this->parent->settings->get_item($params,$default,'-',$forceReload);
                }


           // If not using get_option, assume $function is an anon and run it
           //
           } else {
                $this->parent->data[$element] = $function($params);
           }
       }

       // Cant Be Empty?
       //
       if (($cantBeEmpty) && empty($this->parent->data[$element])) {
           $this->parent->data[$element] = $default;
       }

       return esc_html($this->parent->data[$element]);
    }

    /**
     * Initialize the plugin data.
     *
     * Loop through the getData() method passing in each element of the plugin dataElements array.
     * Each entry of dataElements() must contain 3 parts:
     *    [0] = key name for the plugin data element
     *    [1] = function type 'get_option' or 'get_item'
     *    [2] = the name of the option/item as a single string
     *            OR
     *          an array with the name of the option/item first, the default value second
     *
     */
    function loadPluginData() {
        if (!isset($this->parent->dataElements)) {
            $this->parent->dataElements = array();
        }
        $this->parent->dataElements = apply_filters('wpcsl_loadplugindata__slplus',$this->parent->dataElements);
        if (count($this->parent->dataElements) > 0) {
            foreach ($this->parent->dataElements as $element) {
                $this->getData($element[0],$element[1],$element[2]);
            }
        }
    }
}
