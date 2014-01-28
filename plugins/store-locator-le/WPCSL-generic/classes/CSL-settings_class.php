<?php

/**
 * The wpCSL Settings Class
 *
 * @package wpCSL\Settings
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 *
 */
class wpCSL_settings__slplus {

    //-----------------------------
    // Properties
    //-----------------------------

    /**
     * The main WPCSL object.
     * 
     * @var wpCSL_plugin__slplus
     */
    private $parent;

    /**
     * The settings page "containers" for settings.
     * 
     * @var \wpCSL_settings_section__slplus $sections
     */
    private $sections;


    /**------------------------------------
     ** method: __construct
     **
     ** Overload of the default class instantiation.
     **
     **/
    function __construct($params) {
        // Default Params
        //
        $this->render_csl_blocks = true;        // Display the CSL info blocks
        $this->form_action = 'options.php';     // The form action for this page
        $this->save_text =__('Save Changes',WPCSL__slplus__VERSION);
        $this->css_prefix = '';
        $this->has_packages = false;
        
        // Passed Params
        //
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }

        // Only do this if we are on admin panel
        //
        if (isset($this->parent) && (is_admin() && $this->parent->isOurAdminPage)) {
            add_action('admin_init',array($this,'create_InfoSection'));
        }
    }

    /**
     * Create the plugin news section.
     *
     */
    function create_InfoSection() {
        $this->add_section(array(
                'name'          => 'Plugin News',
                'div_id'        => 'plugin_news',
                'description'   => $this->get_broadcast(),
                'auto'          => true,
                'innerdiv'      => true,
            )
        );
    }

    /**
     * Create the Environment Panel
     *
     * @global type $wpdb
     */
    function create_EnvironmentPanel() {
        if (!isset($this->parent))          { return; }
        if (!is_admin())                    { return; }
        if (!$this->parent->isOurAdminPage) { return; }
        if (!$this->render_csl_blocks)      { return; }

        global $wpdb;
        $this->csl_php_modules = get_loaded_extensions();
        natcasesort($this->csl_php_modules);
        $this->parent->metadata = get_plugin_data($this->parent->fqfile, false, false);
        $this->add_section(
            array(
                'name' => 'Plugin Environment',
                'description' =>
                    $this->create_EnvDiv($this->parent->metadata['Name'] . ' Version' ,$this->parent->metadata['Version'] ).
                    $this->create_EnvDiv('CSA IP Addresses'                         ,
                            gethostbyname('charlestonsw.com') .' and ' .gethostbyname('license.charlestonsw.com')       ).
                    '<br/><br/>' .
                    $this->create_EnvDiv('WPCSL Version'                            ,WPCSL__slplus__VERSION             ).
                    $this->create_EnvDiv('Active WPCSL'                             ,plugin_dir_path(__FILE__)          ).
                    '<br/><br/>' .
                    $this->create_EnvDiv('WordPress Version'                        ,$GLOBALS['wp_version']             ).
                    $this->create_EnvDiv('Site URL'                                 ,get_option('siteurl')              ).
                    '<br/><br/>' .
                    $this->create_EnvDiv('MySQL Version'                            ,$wpdb->db_version()                ).
                    '<br/><br/>' .
                    $this->create_EnvDiv('PHP Version'                              ,phpversion()                       ).
                    $this->create_EnvDiv('PHP Peak RAM'                             ,
                            sprintf('%0.2d MB',memory_get_peak_usage(true)/1024/1024)                                   ).
                    $this->create_EnvDiv('PHP Modules'                              ,
                            '<pre>'.print_r($this->csl_php_modules,true).'</pre>'                                       )
                    ,
                'auto'              => true,
                'innerdiv'          => true,
                'start_collapsed'   => false
            )
        );
    }

    /**
     * Create a plugin environment div.
     *
     * @param string $label
     * @param string $content
     * @return string
     */
    function create_EnvDiv($label,$content) {
        return "<p class='envinfo'><span class='label'>{$label}:</span>{$content}</p>";
    }

    /**
     * Get the news broadcast from the remote server.
     *
     * @return string the HTML for the news panel
     */
     function get_broadcast() {
         $content = '';
         
        // HTTP Handler is not set fail the license check
        //
        if (isset($this->http_handler)) { 
            if ($this->broadcast_url != '') {
                $result = $this->http_handler->request( 
                                $this->broadcast_url, 
                                array('timeout' => 3) 
                                ); 
                if ($this->parent->http_result_is_ok($result) ) {
                    return $result['body'];
                }
            }                
        }         
        
        // Return default content
        //
        if ($content == '') {
            return $this->default_broadcast();
        }
     }
     
   /**
    * Call parent DebugMP only if parent has been set.
    * 
    *
    * @param string $panel - panel name
    * @param string $type - what type of debugging (msg = simple string, pr = print_r of variable)
    * @param string $header - the header
    * @param string $message - what you want to say
    * @param string $file - file of the call (__FILE__)
    * @param int $line - line number of the call (__LINE__)
    * @param boolean $notime - show time? default true = yes.
    * @return null
    */
    function debugMP($panel='main', $type='msg', $header='wpCSL DMP',$message='',$file=null,$line=null,$notime=false) {
         if (is_object($this->parent)) {
             $this->parent->debugMP($panel,$type,$header,$message,$file,$line,$notime);
         }
     }

     /**
      * Set the default HTML string if the server if offline.
      *
      * @return string
      */
     function default_broadcast() {
         return
                        '
                        <div class="csa-infobox">
                         <h4>This plugin has been brought to you by <a href="http://www.charlestonsw.com"
                                target="_new">Charleston Software Associates</a></h4>
                         <p>If there is anything I can do to improve my work or if you wish to hire me to customize
                            this plugin please
                            <a href="http://www.charlestonsw.com/mindset/contact-us/" target="csa">email me</a>
                            and let me know.
                         </p>
                         </div>
                         ' ;
     }

     /**
      * Create a settings page panel.
      *
      * Does not render the panel, it simply creates the container to add stuff to for later rendering.
      *
      * @param array $params named array of the section properties, name is required.
      */
    function add_section($params) {
        if (!isset($this->sections[$params['name']])) {
            $this->sections[$params['name']] = new wpCSL_settings_section__slplus(
                array_merge(
                    $params,
                    array('plugin_url'  => $this->plugin_url,
                          'css_prefix'  => $this->css_prefix,
                          'settingsObj' => $this            ,
                            )
                )
            );
        }            
    }


    /**
     * Add a simple on/off slider to the settings array.
     *
     * @param string $section - slug for the parent section
     * @param string $label - text to appear before the setting
     * @param string $fieldID - the option value field
     * @param string $description - the help text under the more icon expansion
     * @param string $value - the default value to use, overrides get-option(name)
     * @param boolean $disabled - true if the field is disabled
     */
    function add_slider($section,$label,$fieldID,$description=null,$value=null,$disabled=false) {
        $this->add_item(
                $section,
                $label,
                $fieldID,
                'slider',
                false,
                $description,
                null,
                $value,
                $disabled
                );
    }

    /**------------------------------------
     ** method: get_item
     **
     ** Return the value of a WordPress option that was saved via the settings interface.
     **/
    function get_item($name, $default = null, $separator='-', $forceReload = false) {
        $option_name = $this->prefix . $separator . $name;
        if (!isset($this->$option_name) || $forceReload) {
            $this->$option_name =
                ($default == null) ?
                    get_option($option_name) :
                    get_option($option_name,$default)
                    ;
        }
        return $this->$option_name;
    }
    
    /**
     * Add a setting to a panel.
     *
     * @param string $section section slug
     * @param string $display_name the label that shows before the input field
     * @param string $name the database key for the setting
     * @param string $type input style (default: text, list, checkbox, textarea)
     * @param boolean $required required setting? (default: false, true)
     * @param string $description this is what shows via the expand/collapse setting
     * @param mixed[] $custom  (default: null, name/value pair if list)
     * @param mixed $value (default: null), the value to use if not using get_option
     * @param boolean $disabled (default: false), show the input but keep it disabled
     * @param string $onChange onChange JavaScript trigger
     * @return null
     */
    function add_item($section, $display_name, $name, $type = 'text',
            $required = false, $description = null, $custom = null,
            $value = null, $disabled = false, $onChange = ''
            ) {

        $name = $this->prefix .'-'.$name;

        //** Need to check the section exists first. **/
        if (!isset($this->sections[$section])) {
            if (isset($this->notifications)) {
                $this->notifications->add_notice(
                    3,
                    sprintf(
                       __('Program Error: section <em>%s</em> not defined.',WPCSL__slplus__VERSION),
                       $section
                       )
                );
            }
            return;
        }
        $this->sections[$section]->add_item(
            array(
                'prefix' => $this->prefix,
                'css_prefix' => $this->css_prefix,
                'display_name' => $display_name,
                'name' => $name,
                'type' => $type,
                'required' => $required,
                'description' => $description,
                'custom' => $custom,
                'value' => $value,
                'disabled' => $disabled,
                'onChange' => $onChange
            )
        );

        if ($required) {
            if (get_option($name) == '') {
                if (isset($this->notifications)) {
                    $this->notifications->add_notice(
                        1,
                        "Please provide a value for <em>$display_name</em>",
                        "options-general.php?page={$this->prefix}-options#".
                            strtolower(strtr($display_name,' ', '_'))
                    );
                }
            }
        }
    }

    /**
     * Add a simple checkbox to the settings array.
     *
     * @param string $section - slug for the parent section
     * @param string $label - text to appear before the setting
     * @param string $fieldID - the option value field
     * @param string $description - the help text under the more icon expansion
     * @param string $value - the default value to use, overrides get-option(name)
     * @param boolean $disabled - true if the field is disabled
     */
    function add_checkbox($section,$label,$fieldID,$description=null,$value=null,$disabled=false) {
        $this->add_item(
                $section,
                $label,
                $fieldID,
                'checkbox',
                false,
                $description,
                null,
                $value,
                $disabled
                );
    }

    /**
     * Add a simple text input to the settings array.
     *
     * @param string $section - slug for the parent section
     * @param string $label - text to appear before the setting
     * @param string $fieldID - the option value field
     * @param string $description - the help text under the more icon expansion
     * @param string $value - the default value to use, overrides get-option(name)
     * @param boolean $disabled - true if the field is disabled
     */
    function add_input($section,$label,$fieldID,$description=null,$value=null,$disabled=false) {
        $this->add_item(
                $section,
                $label,
                $fieldID,
                'text',
                false,
                $description,
                null,
                $value,
                $disabled
                );
    }

    /**
     * Add a simple text input to the settings array.
     *
     * @param string $section - slug for the parent section
     * @param string $label - text to appear before the setting
     * @param string $fieldID - the option value field
     * @param string $description - the help text under the more icon expansion
     * @param string $value - the default value to use, overrides get-option(name)
     * @param boolean $disabled - true if the field is disabled
     */
    function add_textbox($section,$label,$fieldID,$description=null,$value=null,$disabled=false) {
        $this->add_item(
                $section,
                $label,
                $fieldID,
                'textarea',
                false,
                $description,
                null,
                $value,
                $disabled
                );
    }

    /**------------------------------------
     ** Method: register
     ** 
     ** This function should be used via an admin_init action 
     **
     **/
    function register() {
        if (isset($this->license)) {
            $this->license->initialize_options();
        }
        if (isset($this->cache)) {
            $this->cache->initialize_options();
        }

        if (isset($this->sections)) {
            foreach ($this->sections as $section) {
                $section->register($this->prefix);
            }
        }            
    }

    /**
     * Create the HTML for the plugin settings page on the admin panel.
     *
     * @var $section \wpCSL_settings_section__slplus
     */
    function render_settings_page() {
        
        // Will add debug environment panel at end of general settings panel only.
        //
        $this->create_EnvironmentPanel();

        $this->header();

        // Render all top menus first.
        //
        foreach ($this->sections as $section) {
            $this->debugMP('wpcsl.settings','msg',
                    "{$section->name} first:{$section->first} is_topmenu:{$section->is_topmenu}",
                    '',
                    NULL,NULL,true);
            if (isset($section->is_topmenu) && ($section->is_topmenu)) {
                $section->display();
            }
        }

        // Main area with left sidebar
        //
        print '<div id="main">';

        // Menu Area
        //
        $selectedNav = isset($_REQUEST['selected_nav_element'])?
                $_REQUEST['selected_nav_element']:
                ''
                ;
        $firstOne = true;
        print '<div id="wpcsl-nav" style="display: block;">';
        print '<ul>';
        foreach ($this->sections as $section) {
            if ($section->auto) {
                $friendlyName = strtolower(strtr($section->name, ' ', '_'));
                $friendlyDiv  = (isset($section->div_id) ?  $section->div_id : $friendlyName);
                $firstClass   = (
                                 ("#wpcsl-option-{$friendlyDiv}" == $selectedNav) ||
                                 ($firstOne && ($selectedNav == ''))
                                )?
                                ' first current open' :
                                '';
                $firstOne = false;

                print "<li class='top-level general {$firstClass}'>"       .
                      '<div class="arrow"><div></div></div>'            .
                      '<span class="icon"></span>'                      .
                      "<a href='#wpcsl-option-{$friendlyDiv}' "     .
                            "title='{$section->name}'>"                 .
                      $section->name                                    .
                      '</a>'                                            .
                      '</li>'
                    ;
            }
        }
        print '</ul>';
        print '<div class="navsave">'.$this->generate_save_button_string().'</div>';
        print '</div>';


        // Content Area
        //
        print '<div id="content">';

        // Show the plugin environment and info section on every plugin
        //
        if ($this->render_csl_blocks && isset($this->sections['Plugin News'])) {
            $this->sections['Plugin News']->display();
        }

        // Only render license section if plugin settings
        // asks for it
        if (isset($this->license_section_title) && (isset($this->sections[$this->license_section_title]))) {
            if ($this->has_packages || !$this->no_license) {
                $this->sections[$this->license_section_title]->header();
                $this->show_plugin_settings();
                $this->sections[$this->license_section_title]->footer();
            }
        }

        // Draw each settings section as defined in the plugin config file
        //
        $firstClass = true;
        foreach ($this->sections as $section) {
            if ($section->auto) {
                if ($firstClass) {
                    $section->first = true;
                    $firstClass = false;
                }
                $section->display();
            }
        }

        // Show the plugin environment and info section on every plugin
        //
        if ($this->render_csl_blocks && isset($this->sections['Plugin Environment'])) {
            $this->sections['Plugin Environment']->display();
        }

        // Close Content
        //
        print '</div>';

        // Close Main
        //
        print '</div>';

        $this->footer();
    }

    /**------------------------------------
     ** method: show_plugin_settings
     **
     ** This is a function specifically for showing the licensing stuff,
     ** should probably be moved over to the licensing submodule
     **/
    function show_plugin_settings() {
       $theLicenseKey = get_option($this->prefix.'-license_key');

       $license_ok =(  (get_option($this->prefix.'-purchased') == '1')   &&
                      ($theLicenseKey != '')
                          );

        // If has_packages is true that means we have an unlicensed product
        // so we don't want to show the license box
        //
        if (!$this->has_packages) {
            $content = "<tr valign=\"top\">\n";
            $content .= "  <th  class=\"input_label\" scope=\"row\">License Key *</th>";
            $content .= "    <td>";
            $content .= "<input type=\"text\"".
                ((!$license_ok) ?
                    "name=\"{$this->prefix}-license_key\"" :
                    '') .
                " value=\"". $theLicenseKey .
                "\"". ($license_ok?'disabled' :'') .
                " />";

            if ($license_ok) {
                $content .=
                    '<p class="slp_license_info">'.$theLicenseKey.'</p>'        .
                    '<input type="hidden" name="'.$this->prefix.'-license_key" '.
                        'value="'.$theLicenseKey.'"/>'                          .
                    '<span><img src="'. $this->plugin_url                       .
                              '/images/check_green.png" border="0" '            .
                              'style="padding-left: 5px;" '                     .
                              'alt="License validated!" '                       .
                              'title="License validated!"></span>'              ;
            }

            $content .= (!$license_ok) ?
                ('<span><font color="red"><br/>Without a license key, this plugin will ' .
                    'only function for Admins</font></span>') :
                '';
            $content .= (!(get_option($this->prefix.'-license_key') == '') &&
                        !get_option($this->prefix.'-purchased')) ?
                ('<span><font color="red">Your license key could not be verified</font></span>') :
                '';

            if (!$license_ok) {
                $content .= $this->MakePayPalButton($this->paypal_button_id);
            }

            $content .= '<div id="prodsku">sku: ';
            if (isset($this->sku) && ($this->sku != '')) {
                $content .= $this->sku;
            } else {
                $content .= 'not set';
            }
            $content .= '</div>';



        // If we are using has_packages we need to seed our content string
        //
        } else {
            $content ='';
        }

        // List Packages
        //
        $content .= $this->ListThePackages($license_ok);

        // If the main product or packages show the license box
        // Then show a save button here
        //
       $license_ok =(  (get_option($this->prefix.'-purchased') == '1')   &&
                      (get_option($this->prefix.'-license_key') != '')
                          );
        if (!$license_ok) {
            $content .= '<tr><td colspan="2">' .
                $this->generate_save_button_string().
                '</td></tr>';
        }

        echo $content;
    }


    /**
     * Create the package license otuput for the admin interface.
     */
    function ListThePackages($license_ok = false) {
        $content = '';
        if (isset($this->parent->license->packages) && ($this->parent->license->packages > 0)) {
            $content .= '<tr valign="top"><td class="optionpack" colspan="2">';
            foreach ($this->parent->license->packages as $package) {
                $content .= '<div class="optionpack_box" id="pack_'.$package->sku.'">';
                $content .= '<div class="optionpack_name">'.$package->name.'</div>';
                $content .= '<div class="optionpack_info">'.$this->EnabledOrBuymeString($license_ok,$package).'</div>';
                $content .= '</div>';
            }
            $content .= '</td></tr>';
        }
        return $content;
    }
    
    /**------------------------------------
     ** method: EnabledOrBuymeString
     **
     **/
    function EnabledOrBuymeString($mainlicenseOK, $package) {
        $content = '';

        // If the main product is licensed or we want to force
        // the packages list, show the checkbox or buy/validate button.
        //
        if ($mainlicenseOK || $this->has_packages) {

            // Check if package is licensed now.
            //

            $package->isenabled = (

                    $package->force_enabled ||

                    $package->parent->check_license_key(
                        $package->sku,
                        true,
                        ($this->has_packages ? $package->license_key : ''),
                        true // Force a server check
                    )
                );

            $installed_version = (isset($package->force_version)?
                        $package->force_version :
                        get_option($this->prefix.'-'.$package->sku.'-version')
                        );
            $latest_version = get_option($this->prefix.'-'.$package->sku.'-latest-version');

            // Upgrade is available if the current package version < the latest available
            // -AND- the current package version is has been set
            $upgrade_available = (
                        ($installed_version != '') &&
                        (   get_option($this->prefix.'-'.$package->sku.'-version-numeric') <
                            get_option($this->prefix.'-'.$package->sku.'-latest-version-numeric')
                        )
                    );

            // Package is enabled, just show that
            //
            if ($package->isenabled && ($package->license_key != '')) {
                $packString = $package->name . ' is enabled!';

                $content .=
                    '<div class="csl_info_package_license">'.
                    (($package->sku!='')?'SKU: '.$package->sku.'<br/>':'').
                    (($package->license_key!='')?'License Key: '.$package->license_key.'<br/>':'').
                    '<img src="'. $this->plugin_url .
                    '/images/check_green.png" border="0" style="padding-left: 5px;" ' .
                    'alt="'.$packString.'" title="'.$packString.'">' .
                    (($installed_version != '')?'Version: ' . $installed_version : '') .
                    '</div>'.
                    '<input type="hidden" '.
                            'name="'.$package->lk_option_name.'" '.
                            ' value="'.$package->license_key.'" '.
                            ' />';
                    ;

                // OK - the license was verified, this package is valid
                // but the mainlicense was not set...
                // go set it.
                if (!$mainlicenseOK && ($package->license_key != '')) {
                    update_option($this->prefix.'-purchased',true);
                    update_option($this->prefix.'-license_key',$package->license_key);
                }

            // Package not enabled, show buy button
            //
            }

            if (!$package->isenabled || $upgrade_available || ($package->license_key == '')) {
                if ($package->isenabled && $upgrade_available) {
                    $content .= '<b>There is a new version available: ' . $latest_version . '</b><br>';
                    $content .= $this->MakePayPalButton($package->paypal_upgrade_button_id, $package->help_text);
                    $content .= "Once you've made your purchase, the plugin will automatically re-validate with the latest version.";
                } else {
                    $content .= $this->MakePayPalButton($package->paypal_button_id, $package->help_text);
                }

                // Show license entry box if we need to
                //
                if (
                        ($this->has_packages && !$upgrade_available) ||
                        ($package->license_key == '')
                    ){
                    $content .= "{$package->sku} Activation Key: <input type='text' ".
                            "name='{$package->lk_option_name}'" .
                            " value='' ".
                            " />";
                    if ($package->license_key != '') {
                        $content .=
                            "<br/><span class='csl_info'>".
                            "The key {$package->license_key} could not be validated.".
                            "</span>";
                    }
                }
            }

        // Main product not licensed, tell them.
        //
        } else {
            $content .= '<span>You must license the product before you can purchase add-on packages.</span>';
        }

        return $content;
    }

    /**------------------------------------
     ** method: MakePayPalButton
     **
     **/
    function MakePayPalButton($buttonID, $helptext = '') {
        
        // Set default help text
        //
        if ($helptext == '') {
            $helptext = 'Your license key is emailed within minutes of your purchase.<br/>'. 
                  'If you do not receive your license check your spam '.
                     'folder then <a href="http://www.charlestonsw.com/mindsetcontact-us/" '.
                     'target="csa">Contact us</a>.';
        }
        
        // PayPal Form String
        $ppFormString = 
                    "<form action='https://www.paypal.com/cgi-bin/webscr' target='_blank' method='post'>".
                    "<input type='hidden' name='cmd' value='_s-xclick'>".
                    "<input type='hidden' name='hosted_button_id' value='$buttonID'>".
                    "<input type='hidden' name='on0' value='Main License Key'>".
                    "<input type='hidden' name='os0' value='" . get_option($this->prefix.'-license_key') . "'>".                    "<input type='image' src='https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif' border='0' name='submit' alt='Lobby says buy more sprockets!'>".
                    "<img alt='' border='0' src='https://www.paypalobjects.com/en_US/i/scr/pixel.gif' width='1' height='1'>".                    
                    "</form>"
                ;
        
        // Modal Form Helpers
        //
        // 
        //
        $modalFormSetup = '
            <script>
            jQuery(function() {
                jQuery( "#ppform_iframe_'.$buttonID.'" ).contents().find("body").html("'.$ppFormString.'");                                
            });
            </script>        
            ';
            
        // Build paypal form and send it back
        //
        return $modalFormSetup .
        '<div><iframe height="70" scrolling="no" id="ppform_iframe_'.$buttonID.'" name="ppform_iframe_'.$buttonID.'" src=""></iframe></div>'.                
                '<div>'.
                  '<p>'.$helptext.'</p>'.
                '</div>';
    }
    
    /**
     * Output the settings page header HTML
     */
    function header() {
        $selectedNav = isset($_REQUEST['selected_nav_element'])?$_REQUEST['selected_nav_element']:'';
        echo '<div id="wpcsl_container" class="wrap">';
        screen_icon(preg_replace('/\W/','_',$this->name));
        echo "<h2>{$this->name}</h2><form method='post' action='{$this->form_action}'>";
        echo "<input type='hidden' id='selected_nav_element' name='selected_nav_element' value='{$selectedNav}'/>";
        echo settings_fields($this->prefix.'-settings');
    }

    /**------------------------------------
     ** method: footer
     **
     **/
    function footer() {
        print 
              $this->generate_save_button_string() .
             '</form></div>';
    }
        
    /**------------------------------------
     ** method: generate_save_button_string
     **
     **/
    function generate_save_button_string() {
        return sprintf('<input type="submit" class="button-primary" value="%s" />',
         $this->save_text
         );                    
    }

    /**------------------------------------
     ** method: check_required
     **
     **/
    function check_required($section = null) {
        if ($section == null) {
            foreach ($this->sections as $section) {
                foreach ($section->items as $item) {
                    if ($item->required && get_option($item->name) == '') return false;
                }
            }
        } else {
            
            // The requested section does not exist yet.
            if (!isset($this->sections[$section])) { return false; }
            
            // Check the required items
            //
            foreach ($this->sections[$section]->items as $item) {
                if ($item->required && get_option($item->name) == '') return false;
            }
        }

        return true;
    }

}

/**
 * Manage sections of admin settings pages.
 *
 * @package wpCSL\Settings\Section
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2013 Charleston Software Associates, LLC
 */
class wpCSL_settings_section__slplus {

    //-----------------------------
    // Properties
    //-----------------------------

    /**
     *
     * @var boolean auto
     */
    public $auto = true;

    /**
     * The ID to go in the div.
     * 
     * @var string div_id
     */
    public $div_id;

    /**
     * True if the first rendered section on the panel.
     * 
     * @var boolean first
     */
    public $first = false;

    /**
     *
     * @var boolean headerbar
     */
    private $headerbar = true;

    /**
     *
     * @var boolean innerdiv
     */
    private $innerdiv = true;

    /**
     * True if this is a top-of-page menu.
     * 
     * @var boolean is_topmenu
     */
    public $is_topmenu = false;

    /**
     * The collection of section items that are in this section.
     * 
     * @var \wpCSL_settings_item__slplus[] $items
     */
    private $items;

    /**
     * The title of the section.
     *
     * @var string name
     */
    public $name;

    /**
     * The settings object this section is attached to.
     *
     * @var \wpCSL_settings__PLUGIN_NAME_
     */
    private $settingsObj;

    /**
     * Start "open" or collapsed.
     *
     * @var boolean start_collapsed
     */
    private $start_collapsed = false;

    //-----------------------------
    // Methods
    //-----------------------------

    /**
     * Instantiate a section panel.
     * 
     * @param mixed[] $params
     */
    function __construct($params) {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    /**------------------------------------
     ** Class: wpCSL_settings_section
     ** Method: add_item
     **
     **/
    function add_item($params) {
        $this->items[] = new wpCSL_settings_item__slplus(
            array_merge(
                $params,
                array('plugin_url' => $this->plugin_url,
                      'css_prefix' => $this->css_prefix,
                      )
            )
        );
    }

    /**------------------------------------
     **/
    function register($prefix) {
        if (!isset($this->items)) return false;
        foreach ($this->items as $item) {
            $item->register($prefix);
        }
    }

    /**
     * Render a section panel.
     *
     * Panels are rendered in the order they are put in the stack, FIFO.
     */
    function display() {
        $this->header();

        if (isset($this->items)) {
            foreach ($this->items as $item) {
                $item->display();
            }
        }

        $this->footer();
    }

    /**
     * Render a section header.
     */
    function header() {
        $friendlyName = strtolower(strtr($this->name, ' ', '_'));
        $friendlyDiv  = (isset($this->div_id) ?  $this->div_id : $friendlyName);
        $groupClass   = $this->is_topmenu?'':'group';

        echo '<div '                                        .
            "id='wpcsl-option-{$friendlyDiv}' "                          .
            "class='{$groupClass}' "  .
            "style='display: block;' "             .
            ">";
        
        if ($this->headerbar) {
            echo "<h1 class='subtitle'>{$this->name}</h1>";
        }             

        if ($this->innerdiv) {
            echo "<div class='inside section' " .
                    (isset($this->start_collapsed) && $this->start_collapsed ? 'style="display:none;"' : '') .
                    ">".
                 "<div class='section_description'>"
                 ;
         }
         
         echo $this->description;

         if ($this->innerdiv) {         
            echo '</div>'.
                 '<table class="form-table" style="margin-top: 0pt;">'
                 ;
         }
    }

    /**
     * Should the section be show (display:block) now?
     * 
     * @return boolean
     */
    function show_now() {
        return ($this->first || $this->is_topmenu);
    }

    /**
     * Render a section footer.
     */
    function footer() {
        if ($this->innerdiv) {
            echo '</table></div>';
        }
        echo '</div>';
    }

}

/**
 * This class manages individual settings on the admin panel settings page.
 *
 * @package wpCSL\Settings\Item
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2013 Charleston Software Associates, LLC
 */
class wpCSL_settings_item__slplus {

    /**
     * Constructor.
     *
     * @param mixed[] $params
     */
    function __construct($params) {
        foreach ($params as $name => $value) {
            $this->$name = $value;
        }
    }

    /**------------------------------------
     **/
    function register($prefix) {
        register_setting( $prefix.'-settings', $this->name );
    }

    /**
     * Render the item to the page.
     */
    function display() {
        $this->header();
        if (isset($this->value)) {
            $showThis = $this->value;
        } else {
            $showThis = get_option($this->name);
        }
        $showThis = htmlspecialchars($showThis);
        
        echo '<div class="'.$this->css_prefix.'-input'.($this->disabled?'-disabled':'').'">';
        
        switch ($this->type) {
            case 'textarea':
                echo '<textarea name="'.$this->name.'" '.
                    'cols="50" '.
                    'rows="5" '.
                    ($this->disabled?'disabled="disabled" ':'').
                    '>'.$showThis .'</textarea>';
                break;

            case 'text':
                echo '<input type="text" name="'.$this->name.'" '.
                    ($this->disabled?'disabled="disabled" ':'').                
                    'value="'. $showThis .'" />';
                break;

            case "checkbox":
                echo '<input type="checkbox" name="'.$this->name.'" '.
                    ($this->disabled?'disabled="disabled" ':'').                
                    ($showThis?' checked' : '').'>';
                break;

            case "slider":
                $setting = $this->name;
                $label   = '';
                $checked = ($showThis ? 'checked' : '');
                $onClick = 'onClick="'.
                    "jQuery('input[id={$setting}]').prop('checked',".
                        "!jQuery('input[id={$setting}]').prop('checked')" .
                        ");".
                    '" ';

                echo
                    "<input type='checkbox' id='$setting' name='$setting' style='display:none;' $checked>" .
                    "<div id='{$setting}_div' class='onoffswitch-block'>" .
                    "<span class='onoffswitch-pretext'>$label</span>" .
                    "<div class='onoffswitch'>" .
                    "<input type='checkbox' name='onoffswitch' class='onoffswitch-checkbox' id='{$setting}-checkbox' $checked>" .
                    "<label class='onoffswitch-label' for='{$setting}-checkbox'  $onClick>" .
                    '<div class="onoffswitch-inner"></div>'.
                    "<div class='onoffswitch-switch'></div>".
                    '</label>'.
                    '</div>' .
                    '</div>'
                    ;
                break;

            case "list":
                echo $this->create_option_list();
                break;
                
            case "submit_button":
                echo '<input class="button-primary" type="submit" value="'.$showThis.'">';
                break;                

            default:
                echo $this->custom;
                break;

        }
        echo '</div>';

        if ($this->description != null) {
            $this->display_description_icon();
        }

        if ($this->required) {
            echo ((get_option($this->name) == '') ?
                '<div class="'.$this->css_prefix.'-reqbox">'.
                    '<div class="'.$this->css_prefix.'-reqicon"></div>'.
                    '<div class="'.$this->css_prefix.'-reqtext">This field is required.</div>'.
                '</div>'
                : ''
                );
        }
        
        if ($this->description != null) {
            $this->display_description_text();
        }
        
        $this->footer();
    }

    /**------------------------------------
     * If $type is 'list' then $custom is a hash used to make a <select>
     * drop-down representing the setting.  This function returns a
     * string with the markup for that list.
     *
     * The selected value will use the get_option() on the name of the drop down,
     * with a default being allowed in the $value parameter.
     */
    function create_option_list() {
        $content =
            "<select class='csl_select' ".
                "name='".$this->name."' ".
                "onChange='".$this->onChange."' ".
                "/>"
                ;

        foreach ($this->custom as $key => $value) {
            if (get_option($this->name, $this->value) === $value) {
                $content .= "<option class='csl_option' value=\"$value\" " .
                    "selected=\"selected\">$key</option>\n";
            }
            else {
                $content .= "<option class='csl_option'  value=\"$value\">$key</option>\n";
            }
        }

        $content .= "</select>\n";

        return $content;
    }

    /**------------------------------------
     **/
    function header() {
        echo "<tr><th class='input_label".($this->disabled?'-disabled':'')."' scope='row'>" .
        "<a name='" .
        strtolower(strtr($this->display_name, ' ', '_')).
            "'></a>{$this->display_name}".
            (($this->required) ? ' *' : '').
            '</th><td>';

    }

    /**------------------------------------
     **/
    function footer() {
        echo '</td></tr>';
    }

    /**------------------------------------
     **/
    function display_description_icon() {
        echo '<div class="'.$this->css_prefix.'-moreicon" title="click for more info"><br/></div>';        
    }    
    
    /**------------------------------------
     **/
    function display_description_text() {
        echo 
            '<div class="'.$this->css_prefix.'-moretext" id="'.$this->name.'-moretext">' .
                $this->description .
            '</div>'
            ;
    }
}
