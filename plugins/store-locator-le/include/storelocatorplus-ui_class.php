<?php

/**
 * Store Locator Plus basic user interface.
 *
 * @package StoreLocatorPlus\UI
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 */
class SLPlus_UI {

    //-------------------------------------
    // Properties
    //-------------------------------------

    /**
     * The custom string format used in JS printf for under-the-map results.
     * 
     * @var string $resultsString
     */
    public  $resultsString = '';

    //----------------------------------
    // Methods
    //----------------------------------

    /**
     * Instantiate the UI Class.
     *
     * @param mixed[] $params
     */
    function __construct($params = null) {

        // Do the setting override or initial settings.
        //
        if ($params != null) {
            foreach ($params as $name => $sl_value) {
                $this->$name = $sl_value;
            }
        }
    }

    /**
     * Set the plugin property to point to the primary plugin object.
     *
     * Returns false if we can't get to the main plugin object.
     *
     * @global SLPlus $slplus_plugin
     * @return boolean true if plugin property is valid
     */
    function setPlugin() {
        if (!isset($this->plugin) || ($this->plugin == null)) {
            global $slplus_plugin;
            $this->plugin = $slplus_plugin;
        }
        return (isset($this->plugin) && ($this->plugin != null));
    }

    /**
     * Return the HTML for a slider button.
     *
     * The setting parameter will be used for several things:
     * the div ID will be "settingid_div"
     * the assumed matching label option will be "settingid_label" for WP get_option()
     * the a href ID will be "settingid_toggle"
     *
     * @param string $setting the ID for the setting
     * @param string $label the default label to show
     * @param boolean $isChecked default on/off state of checkbox
     * @param string $onClick the onClick javascript
     * @return string the slider HTML
     */
    function CreateSliderButton($setting=null, $label='', $isChecked = true, $onClick='') {
        if ($setting === null) { return ''; }
        if (!$this->setPlugin()) { return ''; }

        $label   = $this->plugin->settings->get_item($setting.'_label',$label);
        $checked = ($isChecked ? 'checked' : '');
        $onClick = (($onClick === '') ? '' : ' onClick="'.$onClick.'"');

        $content =
            "<div id='{$setting}_div' class='onoffswitch-block'>" .
            "<span class='onoffswitch-pretext'>$label</span>" .
            "<div class='onoffswitch'>" .
            "<input type='checkbox' name='onoffswitch' class='onoffswitch-checkbox' id='{$setting}-checkbox' $checked>" .
            "<label class='onoffswitch-label' for='{$setting}-checkbox'  $onClick>" .
            '<div class="onoffswitch-inner"></div>'.
            "<div class='onoffswitch-switch'></div>".
            '</label>'.
            '</div>' .
            '</div>';
         return $content;
    }


    /**
     * Returns true if the shortcode attribute='true' or settings is set to 1 (checkbox enabled)
     *
     * If...
     *
     * The shortcode attribute is set.
     *
     * AND EITHER...
     *    The shortcode attribute = true.
     *    OR
     *    attribute is not set (null)
     *       AND the setting is checked
     *
     *
     * @param string $attribute - the key for the shortcode attribute
     * @param string $setting - the key for the admin panel setting
     * @return boolean
     */
    function ShortcodeOrSettingEnabled($attribute,$setting) {
        if (!$this->setPlugin()) { return false; }

       // If the data attribute is set
       //
       // return TRUE if the value is 'true' (this is for shortcode atts)
       if (isset($this->plugin->data[$attribute])) {
            if (strcasecmp($this->plugin->data[$attribute],'true')==0) { return true; }

       // If the data attribute is NOT set or it is set and is null (isset = false if value is null)
       // return the value of the database setting
       } else {
            return ($this->plugin->settings->get_item($setting,0) == 1);
       }
    }

    /**
     * Create a search form input div.
     */
    function create_input_div($fldID=null,$label='',$placeholder='',$hidden=false,$divID=null,$default='') {
        if ($fldID === null) { return; }
        if ($divID === null) { $divID = $fldID; }

        // Escape output for special char friendliness
        //
        if ($default     !==''){ $default     = esc_html($default);     }
        if ($placeholder !==''){ $placeholder = esc_html($placeholder); }

        $content =
            ($hidden?'':"<div id='$divID' class='search_item'>") .
                (($hidden || ($label === '')) ? '' : "<label for='$fldID'>$label</label>") .
                "<input type='".($hidden?'hidden':'text')."' id='$fldID' name='$fldID' placeholder='$placeholder' size='50' value='$default' />" .
            ($hidden?'':"</div>")
            ;
        return $content;
    }

    /**
     * Render the SLP map
     *
     */
    function create_DefaultMap() {
        if(!$this->setPlugin()) { return; }
        $this->loadPluginData();

        // Start the map table
        //
        $content =
            '<table id="map_table" width="100%" cellspacing="0px" cellpadding="0px">' .
            '<tbody id="map_table_body">' .
            '<tr id="map_table_row">'.
            '<td id="map_table_cell" width="100%" valign="top">'
            ;

        // If starting image is set, create the overlay div.
        //
        $startingImage=get_option('sl_starting_image','');
        if ($startingImage != '') {
            $startingImage =
                ((preg_match('/^http/',$startingImage) <= 0) ?SLPLUS_PLUGINURL:'').
                $startingImage
                ;

            $content .=
                '<div id="map_box_image" ' .
                    'style="'.
                        "width:". $this->plugin->data['sl_map_width'].
                                  $this->plugin->data['sl_map_width_units'] .
                                  ';'.
                        "height:".$this->plugin->data['sl_map_height'].
                                  $this->plugin->data['sl_map_height_units'].
                                  ';'.
                    '"'.
                '>'.
                "<img src='$startingImage'>".
                '</div>' .
                '<div id="map_box_map">'
                ;
        }

        // The Map Div
        //
        $content .=
            '<div id="map" ' .
                'style="'.
                    "width:". $this->plugin->data['sl_map_width'].
                              $this->plugin->data['sl_map_width_units'] .
                              ';'.
                    "height:".$this->plugin->data['sl_map_height'].
                              $this->plugin->data['sl_map_height_units'].
                              ';'.
                '"'.
            '>'.
            '</div>'
            ;

        // Credits Line
        if (!(get_option('sl_remove_credits',0)==1)) {
            $content .=
                '<div id="slp_tagline" ' .
                    'style="'.
                        "width:". $this->plugin->data['sl_map_width'].
                                  $this->plugin->data['sl_map_width_units'] .
                                  ';'.
                    '"'.
                '>'.
                __('search provided by', 'csa-slplus') .
                ' ' .
                "<a href='". $this->plugin->url."' target='_blank'>".
                     $this->plugin->name.
                "</a>".
                '</div>'
                ;
        }

        // If starting image is set, close the overlay div.
        //
        if ($startingImage != '') {
            $content .= '</div>';
        }

        // Close the table
        //
        $content .= '</td></tr></tbody></table>';

        // Render
        //
        echo apply_filters('slp_map_html',$content);
    }

    /**
     * Create the default search address div.
     *
     * FILTER: slp_search_default_address
     *
     * @global string $slp_thishtml_60
     * @global SLPlus_UI_DivManager $slp_SearchDivs
     */
    function create_DefaultSearchDiv_Address() {
        global $slp_thishtml_60;
        global $slp_SearchDivs;

        $slp_thishtml_60 = $this->plugin->UI->create_input_div(
            'addressInput',
            get_option('sl_search_label',__('Address','csa-slplus')),
            '',
            (get_option(SLPLUS_PREFIX.'_hide_address_entry',0) == 1),
            'addy_in_address',
            apply_filters('slp_search_default_address','')
            );

        add_filter('slp_search_form_divs',array($slp_SearchDivs,'buildDiv60'),60);
    }

    /**
     * Create the default search radius div.
     *
     * @global string $slp_thishtml_70
     * @global SLPlus_UI_DivManager $slp_SearchDivs
     */
    function create_DefaultSearchDiv_Radius() {
        global $slp_thishtml_70;
        global $slp_SearchDivs;
        
        if (get_option(SLPLUS_PREFIX.'_hide_radius_selections',0) == 0) {
            $slp_thishtml_70 =
                "<div id='addy_in_radius'>".
                "<label for='radiusSelect'>".
                get_option('sl_radius_label','Within').
                '</label>'.
                "<select id='radiusSelect'>".$this->plugin->data['radius_options'].'</select>'.
                "</div>"
                ;
        } else {
            $slp_thishtml_70 =$this->plugin->data['radius_options'];
        }
        add_filter('slp_search_form_divs',array($slp_SearchDivs,'buildDiv70'),70);
    }

    /**
     * Create the default search submit div.
     *
     * If we are not hiding the submit button.
     *
     * @global string $slp_thishtml_80
     * @global SLPlus_UI_DivManager $slp_SearchDivs
     */
    function create_DefaultSearchDiv_Submit() {
        global $slp_thishtml_80;        
        global $slp_SearchDivs;
        if (get_option(SLPLUS_PREFIX.'_disable_search') == 0) {

            // Find Button Is An Image
            //
            if ($this->plugin->settings->get_item('disable_find_image','0','_') === '0') {
                $sl_theme_base=SLPLUS_UPLOADURL."/images";
                $sl_theme_path=SLPLUS_UPLOADDIR."/images";

                if (!file_exists($sl_theme_path."/search_button.png")) {
                    $sl_theme_base=SLPLUS_PLUGINURL."/images";
                    $sl_theme_path=SLPLUS_COREDIR."/images";
                }

                $sub_img=$sl_theme_base."/search_button.png";
                $mousedown=(file_exists($sl_theme_path."/search_button_down.png"))?
                    "onmousedown=\"this.src='$sl_theme_base/search_button_down.png'\" onmouseup=\"this.src='$sl_theme_base/search_button.png'\"" :
                    "";
                $mouseover=(file_exists($sl_theme_path."/search_button_over.png"))?
                    "onmouseover=\"this.src='$sl_theme_base/search_button_over.png'\" onmouseout=\"this.src='$sl_theme_base/search_button.png'\"" :
                    "";
                $button_style=(file_exists($sl_theme_path."/search_button.png"))?
                    "type='image' class='slp_ui_image_button' src='$sub_img' $mousedown $mouseover" :
                    "type='submit'  class='slp_ui_button'";

            // Find Button Image Is Disabled
            //
            } else {
                $button_style = 'type="submit" class="slp_ui_button"';
            }

            $slp_thishtml_80 =
                "<div id='radius_in_submit'>".
                    "<input $button_style " .
                        "value='".get_option(SLPLUS_PREFIX.'_find_button_label','Find Locations')."' ".
                        "id='addressSubmit'/>".
                "</div>"
                ;
            add_filter('slp_search_form_divs',array($slp_SearchDivs,'buildDiv80'),80);
        }
    }

    /**
     * Render the search form for the map.
     *
     * SLP Action: slp_render_search_form
     *
     * FILTER: slp_search_form_divs
     * FILTER: slp_search_form_html
     *
     * @global SLPlus_UI_DivManager $slp_SearchDivs
     */
    function create_DefaultSearchForm() {
        if(!$this->setPlugin()) { return; }

        global $slp_SearchDivs;
        $slp_SearchDivs = new SLPlus_UI_DivManager();

        // Create our search form elements.
        //
        $this->create_DefaultSearchDiv_Address();
        $this->create_DefaultSearchDiv_Radius();
        $this->create_DefaultSearchDiv_Submit();

        // The search_form template sets up a bunch of DIV filters for the search form.
        //
        // apply_filters actually builds the output HTML from those div filters.
        //
        $HTML =
            "<form onsubmit='cslmap.searchLocations(); return false;' id='searchForm' action=''>".
            "<table  id='search_table' border='0' cellpadding='3px' class='sl_header'>".
                "<tbody id='search_table_body'>".
                    "<tr id='search_form_table_row'>".
                        "<td id='search_form_table_cell' valign='top'>".
                            "<div id='address_search'>".
            apply_filters('slp_search_form_divs','') .
            '</div></td></tr></tbody></table></form>'
            ;
        
        echo apply_filters('slp_search_form_html',$HTML);
    }

    /**
     * Create the HTML for the map.
     *
     * HOOK: slp_render_map
     */
    function create_Map() {
        ob_start();
        do_action('slp_render_map');
        return $this->rawDeal(ob_get_clean());
    }

    /**
     * Create the HTML for the search results.
     */
    function create_Results() {
        $HTML =  
            "<table id='results_table'>".
                "<tr id='cm_mapTR' class='slp_map_search_results'>".
                    "<td width='' valign='top' id='map_sidebar_td'>".
                        "<div id='map_sidebar' ".
                            "style='width:{$this->plugin->data['sl_map_width']}{$this->plugin->data['sl_map_width_units']};'" .
                            ">".
                            "<div class='text_below_map'>".
                                get_option('sl_instruction_message',__('Enter Your Address or Zip Code Above.','csa-slplus')) .
                            "</div>".
                        "</div>".
                    "</td>".
                "</tr>".
            "</table>"
            ;
        return $this->rawDeal($HTML);
    }

    /**
     * Create the HTML for the search form.
     *
     * HOOK: slp_render_search_form
     */
    function create_Search() {
        ob_start();
        do_action('slp_render_search_form');
        return $this->rawDeal(ob_get_clean());
    }

    /**
     * Do not texturize our shortcodes.
     * 
     * @param array $shortcodes
     * @return array
     */
    static function no_texturize_shortcodes($shortcodes) {
       return array_merge($shortcodes,
                array(
                 'STORE-LOCATOR',
                 'SLPLUS',
                 'slplus',
                )
               );
    }

    /**
     * Process the store locator plus shortcode.
     *
     * Variables this function uses and passes to the template
     * we need a better way to pass vars to the template parser so we don't
     * carry around the weight of these global definitions.
     * the other option is to unset($GLOBAL['<varname>']) at then end of this
     * function call.
     *
     * We now use $this->plugin->data to hold attribute data.
     *
     *
     * @param type $attributes
     * @param type $content
     * @return string HTML the shortcode will render
     */
     function render_shortcode($attributes, $content = null) {
         if (!$this->setPlugin()) {
             return sprintf(__('%s is not ready','csa-slplus'),__('Store Locator Plus','csa-slplus'));
        }

        // Force some plugin data properties
        //
        $this->plugin->data['radius_options'] =
                (isset($this->plugin->data['radius_options'])?$this->plugin->data['radius_options']:'');

        // Load from plugin object data table first,
        // attributes trump options
        //
        $this->loadPluginData();

        // Then load the attributes
        //
        add_filter('slp_shortcode_atts',array($this,'filter_SetAllowedShortcodes'));
        $attributes =
            shortcode_atts(
                apply_filters('slp_shortcode_atts',array()),
                $attributes
               );
        $this->plugin->data =
            array_merge(
                $this->plugin->data,
                (array) $attributes
            );

        // Now set options to attributes
        //
        $this->plugin->options = array_merge($this->plugin->options, (array) $attributes);

        // Localize the CSL Script
        // .. this is called too late..
        $this->plugin->debugMP('slp.main','pr','render_shortcode()',$this->plugin->data,__FILE__,__LINE__);
        $this->localizeCSLScript();

        // Radius Options
        //
        $radiusSelections = get_option('sl_map_radii','1,5,10,(25),50,100,200,500');

        // Hide Radius, set the only (or default) radius
        if (get_option(SLPLUS_PREFIX.'_hide_radius_selections', 0) == 1) {
            preg_match('/\((.*?)\)/', $radiusSelections, $selectedRadius);
            $selectedRadius = preg_replace('/[^0-9]/', '', (isset($selectedRadius[1])?$selectedRadius[1]:$radiusSelections));
            if (empty($selectedRadius) || ($selectedRadius <= 0)) { $selectedRadius = '2500'; }
            $this->plugin->data['radius_options'] =
                    "<input type='hidden' id='radiusSelect' name='radiusSelect' value='$selectedRadius'>";

        // Build Pulldown
        } else {
            $radiusSelectionArray  = explode(",",$radiusSelections);
            foreach ($radiusSelectionArray as $radius) {
                $selected=(preg_match('/\(.*\)/', $radius))? " selected='selected' " : "" ;
                $radius=preg_replace('/[^0-9]/', '', $radius);
                $this->plugin->data['radius_options'].=
                        "<option value='$radius' $selected>$radius ".get_option('sl_distance_unit','mi')."</option>";
            }
        }

        // Set our flag for later processing
        // of JavaScript files
        //
        if (!defined('SLPLUS_SHORTCODE_RENDERED')) {
            define('SLPLUS_SHORTCODE_RENDERED',true);
        }
        $this->parent->shortcode_was_rendered = true;

        // Setup the style sheets
        //
        $this->setup_stylesheet_for_slplus();

        // Search / Map Actions
        //
        add_action('slp_render_search_form' ,array($this,'create_DefaultSearchForm'));
        add_action('slp_render_map'         ,array($this,'create_DefaultMap'));

        return
            '<div id="sl_div">' .
                $this->create_Search() .
                $this->create_Map().
                $this->create_Results().
            '</div>'
            ;
    }

    /**
     * Set the allowed shortcode attributes
     * 
     * @param mixed[] $atts
     */
    function filter_SetAllowedShortcodes($atts) {
        return array_merge(
                array(
                    'initial_radius'     => $this->plugin->options['initial_radius'],
                    ),
                $atts
            );
    }

    /**
     * Load Plugin Data once.
     * 
     * Call $this->plugin->helper->loadPluginData(); to force a reload.
     */
    function loadPluginData() {
        if (!$this->plugin->pluginDataLoaded) {
            $this->plugin->helper->loadPluginData();
            $this->plugin->pluginDataLoaded = true;
        }
    }

    /**
     * Localize the CSL Script
     *
     */
    function localizeCSLScript() {
        if (!$this->setPlugin()) { return false; }
        $this->loadPluginData();

        $slplus_home_icon_file = str_replace(SLPLUS_ICONURL,SLPLUS_ICONDIR,$this->plugin->data['sl_map_home_icon']);
        $slplus_end_icon_file  = str_replace(SLPLUS_ICONURL,SLPLUS_ICONDIR,$this->plugin->data['sl_map_end_icon']);
        $this->plugin->data['home_size'] =(function_exists('getimagesize') && file_exists($slplus_home_icon_file))?
            getimagesize($slplus_home_icon_file) :
            array(0 => 20, 1 => 34);
        $this->plugin->data['end_size']  =(function_exists('getimagesize') && file_exists($slplus_end_icon_file)) ?
            getimagesize($slplus_end_icon_file)  :
            array(0 => 20, 1 => 34);

        $this->setResultsString();


        // Lets get some variables into our script
        //
        $scriptData = array(
            'plugin_url'        => SLPLUS_PLUGINURL,
            'core_url'          => SLPLUS_COREURL,
            'debug_mode'        => (get_option(SLPLUS_PREFIX.'-debugging') == 'on'),
            'disable_scroll'    => (get_option(SLPLUS_PREFIX.'_disable_scrollwheel')==1),
            'disable_dir'       => (get_option(SLPLUS_PREFIX.'_disable_initialdirectory' )==1),
            'distance_unit'     => esc_attr(get_option('sl_distance_unit'),'miles'),
            'load_locations'    => (get_option('sl_load_locations_default','1')==1),
            'label_directions'  => esc_attr(get_option(SLPLUS_PREFIX.'_label_directions',   'Directions')  ),
            'label_fax'         => esc_attr(get_option(SLPLUS_PREFIX.'_label_fax',          'Fax: ')         ),
            'label_hours'       => esc_attr(get_option(SLPLUS_PREFIX.'_label_hours',        'Hours: ')       ),
            'label_phone'       => esc_attr(get_option(SLPLUS_PREFIX.'_label_phone',        'Phone: ')       ),
            'map_3dcontrol'     => (get_option(SLPLUS_PREFIX.'_disable_largemapcontrol3d')==0),
            'map_country'       => $this->set_MapCenter(),
            'map_domain'        => get_option('sl_google_map_domain','maps.google.com'),
            'map_home_icon'     => $this->plugin->data['sl_map_home_icon'],
            'map_home_sizew'    => $this->plugin->data['home_size'][0],
            'map_home_sizeh'    => $this->plugin->data['home_size'][1],
            'map_end_icon'      => $this->plugin->data['sl_map_end_icon'],
            'map_end_sizew'     => $this->plugin->data['end_size'][0],
            'map_end_sizeh'     => $this->plugin->data['end_size'][1],
            'use_sensor'        => (get_option(SLPLUS_PREFIX."_use_location_sensor",0)==1),
            'map_scalectrl'     => (get_option(SLPLUS_PREFIX.'_disable_scalecontrol')==0),
            'map_type'          => get_option('sl_map_type','roadmap'),
            'map_typectrl'      => (get_option(SLPLUS_PREFIX.'_disable_maptypecontrol')==0),
            'msg_noresults'     => $this->plugin->settings->get_item('message_noresultsfound','No results found.','_'),
            'results_string'    => apply_filters('slp_javascript_results_string',$this->resultsString),
            'show_tags'         => (get_option(SLPLUS_PREFIX.'_show_tags')==1),
            'options'           => $this->plugin->options,
            'overview_ctrl'     => get_option('sl_map_overview_control',0),
            'use_email_form'    => (get_option(SLPLUS_PREFIX.'_use_email_form',0)==1),
            'website_label'     => esc_attr(get_option('sl_website_label','Website')),
            'zoom_level'        => get_option('sl_zoom_level',12),
            'zoom_tweak'        => get_option('sl_zoom_tweak',1)
            );

        $this->plugin->debugMP('slp.main','pr','UI.localizeCSLScript() scriptData',$scriptData,__FILE__,__LINE__);

        wp_localize_script('csl_script','slplus',apply_filters('slp_script_data',$scriptData));
        wp_localize_script('csl_script','csl_ajax',array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('em')));
    }

    /**
     * Set the starting point for the center of the map.
     *
     * Uses country by default.
     */
    function set_MapCenter() {
        return apply_filters('slp_map_center',esc_attr(get_option('sl_google_map_country','United States')));
    }


    /**
     * Set the default results string for stuff under the map.
     *
     * Results Output String In JavaScript Format
     *
     *              {0} aMarker.name,
     *              {1} parseFloat(aMarker.distance).toFixed(1),
     *              {2} slplus.distance_unit,
     *              {3} street,
     *              {4} street2,
     *              {5} city_state_zip,
     *              {6} thePhone,
     *              {7} theFax,
     *              {8} link,
     *              {9} elink,
     *              {10} slplus.map_domain,
     *              {11} encodeURIComponent(this.address),
     *              {12} encodeURIComponent(address),
     *              {13} slplus.label_directions,
     *              {14} tagInfo,
     *              {15} aMarker.id
     *              {16} aMarker.country
     *              {17} aMarker.hours
     *
     */
    function setResultsString() {
        if ($this->resultsString === '') {
            $this->resultsString =
                '<center>' .
                    '<table width="96%" cellpadding="4px" cellspacing="0" class="searchResultsTable" id="slp_results_table_{15}">'  .
                        '<tr class="slp_results_row" id="slp_location_{15}">'  .
                            '<td class="results_row_left_column" id="slp_left_cell_{15}">'.
                                '<span class="location_name">{0}</span>'.
                                '<span class="location_distance"><br/>{1} {2}</span>'.
                            '</td>'  .
                            '<td class="results_row_center_column" id="slp_center_cell_{15}">' .
                                '<span class="slp_result_address slp_result_street">{3}</span>'.
                                '<span class="slp_result_address slp_result_street2">{4}</span>' .
                                '<span class="slp_result_address slp_result_citystatezip">{5}</span>' .
                                '<span class="slp_result_address slp_result_country">{16}</span>'.
                                '<span class="slp_result_address slp_result_phone">{6}</span>' .
                                '<span class="slp_result_address slp_result_fax">{7}</span>' .
                            '</td>'   .
                            '<td class="results_row_right_column" id="slp_right_cell_{15}">' .
                                '<span class="slp_result_contact slp_result_website">{8}</span>' .
                                '<span class="slp_result_contact slp_result_email">{9}</span>' .
                                '<span class="slp_result_contact slp_result_directions"><a href="http://{10}' .
                                '/maps?saddr={11}'  .
                                '&daddr={12}'  .
                                '" target="_blank" class="storelocatorlink">{13}</a></span>'.
                                '<span class="slp_result_contact slp_result_tags">{14}</span>'.
                            '</td>'  .
                        '</tr>'  .
                    '</table>'  .
                '</center>';
        }
    }

    /**
     * Setup the CSS for the product pages.
     */
    function setup_stylesheet_for_slplus() {
        if (!$this->setPlugin()) { return false; }
        $this->parent->helper->loadPluginData();
        if (!isset($this->parent->data['theme']) || empty($this->parent->data['theme'])) {
            $this->parent->data['theme'] = 'default';
        }
        $this->parent->themes->assign_user_stylesheet($this->parent->data['theme'],true);
    }


    /**
     * Strip all \r\n from the template to try to "unbreak" Theme Forest themes.
     *
     * This is VERY ugly, but a lot of people use Theme Forest.  They have a known bug
     * that MANY Theme Forest authors have introduced which will change this:
     * <table
     *    style="display:none"
     *    >
     *
     * To this:
     * <table<br/>
     *    style="display:none"<br/>
     *    >
     *
     * Which really fucks things up.
     *
     * Envato response?  "Oh well, we will tell the authors but can't really fix anything."
     *
     * Now our plugin has this ugly slow formatting function which sucks balls.   But we need it
     * if we are going to not alienate a bunch of Envato users that will never tell us they had an
     * issue. :/
     *
     * @param string $inStr
     * @return string
     */
    function rawDeal($inStr) {
        return str_replace(array("\r","\n"),'',$inStr);
    }

    /**
     * Puts the tag list on the search form for users to select tags.
     *
     * @param string[] $tags tags as an array of strings
     * @param boolean $showany show the any pulldown entry if true
     */
    static function slp_render_search_form_tag_list($tags,$showany = false) {
        print "<select id='tag_to_search_for' >";

        // Show Any Option (blank value)
        //
        if ($showany) {
            print "<option value=''>".
                get_option(SLPLUS_PREFIX.'_tag_pulldown_first',__('Any','csa-slplus')).
                '</option>';
        }

        foreach ($tags as $selection) {
            $clean_selection = preg_replace('/\((.*)\)/','$1',$selection);
            print "<option value='$clean_selection' ";
            print (preg_match('#\(.*\)#', $selection))? " selected='selected' " : '';
            print ">$clean_selection</option>";
        }
        print "</select>";
    }
}

/**
 * Store Locator Plus map page div output manager.
 *
 * @package StoreLocatorPlus\UI\DivManager
 * @author Lance Cleveland <lance@charlestonsw.com>
 * @copyright 2012-2013 Charleston Software Associates, LLC
 */
class SLPlus_UI_DivManager {

    function DivStr($str1, $str2) {
        if ($str2 == '') {
            return $str1;
        } else {
            return $str1.$str2;
        }
    }

    function buildDiv10($blank) {
        global $slp_thishtml_10;
        $content = $this->DivStr($blank,$slp_thishtml_10);
        $slp_thishtml_10 = '';
        return $content;
    }

    function buildDiv20($blank) {
        global $slp_thishtml_20;
        $content = $this->DivStr($blank,$slp_thishtml_20);
        $slp_thishtml_20 = '';
        return $content;
    }

    function buildDiv30($blank) {
        global $slp_thishtml_30;
        $content = $this->DivStr($blank,$slp_thishtml_30);
        $slp_thishtml_30 = '';
        return $content;
    }

    function buildDiv60($blank) {
        global $slp_thishtml_60;
        $content = $this->DivStr($blank,$slp_thishtml_60);
        $slp_thishtml_60 = '';
        return $content;
    }

    function buildDiv70($blank) {
        global $slp_thishtml_70;
        $content = $this->DivStr($blank,$slp_thishtml_70);
        $slp_thishtml_70 = '';
        return $content;
    }

    function buildDiv80($blank) {
        global $slp_thishtml_80;
        $content = $this->DivStr($blank,$slp_thishtml_80);
        $slp_thishtml_80 = '';
        return $content;
    }

    function buildDiv90($blank) {
        global $slp_thishtml_90;
        $content = $this->DivStr($blank,$slp_thishtml_90);
        $slp_thishtml_90 = '';
        return $content;
    }
}
