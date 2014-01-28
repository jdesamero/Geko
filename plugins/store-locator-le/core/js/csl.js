/*****************************************************************
 * file: csl.js
 *
 *****************************************************************/

/***************************
  * CSA Labs Namespace
  */
var csl = {

   /***************************
  	* Animation enum technically
  	* usage:
  	* 		Animation enumeration
  	*/
  	Animation: { Bounce: 1, Drop: 2, None: 0 },


    /***************************************************************************
     *
     * MOUSE ANIMATION SUBCLASS
     *
     */
	MouseAnimation: function()
	{
		this.anim2 = function(imgObj, url) {
			imgObj.src=url;
		};

		this.anim = function(name, type) {
			if (type===0)
				document.images[name].src="/core/images/"+name+".gif";
			if (type===1)
				document.images[name].src="/core/images/"+name+"_over.gif";
			if (type===2)
				document.images[name].src="/core/images/"+name+"_down.gif";
		};
	},

    /***************************************************************************
     *
     * LOCATION SERVICES SUBCLASS
     *
     ***************************
     * Location services
     * usage:
     * 		gets the users current location
     */
    LocationServices: function() {
        this.theService = null;
        this.LocationSupport = true;
        this.Initialized = false;
        this.location_timeout = null;
        this.lat = 0.00;
        this.lng = 0.00;

        this.__init = function() {
            this.Initialized = true;
            try {
                if (typeof navigator.geolocation === 'undefined') {
                    if (google.gears) {
                        this.theService = google.gears.factory.create('beta.geolocation');
                    } else {
                        this.LocationSupport = false;
                    }
                }
                else {
                    this.theService = navigator.geolocation;
                }
            } catch (e) {
            }
        };

        this.currentLocation = function(callback, errorCallback) {

            // If location services are not setup, do it
            //
            if (!this.Initialized) {
                this.__init();
            }

            // If this browser supports location services, use them
            //
            if (this.LocationSupport) {
                if (this.theService) {
                        this.location_timeout = setTimeout(errorCallback, 5000);
                        this.theService.getCurrentPosition(callback, errorCallback, {maximumAge:60000, timeout:5000, enableHighAccuracy:true});
                }

            // Otherwise throw an exception
            //
            } else {
                errorCallback(null);
            }

        };
    },

    /***************************************************************************
     *
     * AJAX SUBCLASS
     *
	 ***************************
  	 * Class: Ajax
  	 * usage:
	 * 		Sends an ajax request (use Ajax.Send())
  	 * parameters:
  	 * 		action: A usable action { action: 'csl_ajax_search', lat: 'start lat', long: 'start long', dist:'distance to search' }
  	 *		callback: will be of the form: { success: true, response: {marker list}}
  	 * returns: none
  	 */
	Ajax: function() {
		/***************************
		 * function: Ajax.send
		 * usage:
		 * 		Sends an ajax request
		 * parameters:
		 * 		action: A usable action { action: 'csl_ajax_search', lat: 'start lat', long: 'start long', dist:'distance to search' }
		 *		callback: will be of the form: { success: true, response: {marker list}}
		 * returns: none
		 */
	    this.send = function (action, callback) {
	        if (window.location.protocol !== csl_ajax.ajaxurl.substring(0, csl_ajax.ajaxurl.indexOf(':') + 1)) {
	            csl_ajax.ajaxurl = csl_ajax.ajaxurl.replace(csl_ajax.ajaxurl.substring(0, csl_ajax.ajaxurl.indexOf(':') + 1), window.location.protocol);
	        }

			jQuery.post(csl_ajax.ajaxurl, action,
			function (response) {
			    try {
			        response = JSON.parse(response);
			    }
			    catch (ex) {
			    }
				callback(response);
			});
		};
	},

    /***************************************************************************
     *
     * MARKERS SUBCLASS
     *
  	 * Marker for google maps
  	 * usage:
  	 * create a google maps marker
  	 * parameters:
  	 * 	animationType: The Animation type to do the animation
	 *		map: the csl.Map type to put it on
	 *		title: the title of the marker for mouse over
	 *		iconUrl: todo: load a custom icon, null for default
	 *		position: the lat/long to put the marker at
  	 */
  	Marker: function (animationType, map, title, position, iconUrl, iconSizeW, iconSizeH) {
		this.__animationType = animationType;
  	  	this.__map = map;
  	  	this.__title = title;
  	  	this.__iconUrl = iconUrl;
  	  	this.__position = position;
  	  	this.__gmarker = null;
		this.__iconHeight = iconSizeH;
		this.__iconWidth = iconSizeW;
		this.__iconImage = null;
		this.__shadowImage = null;

        /*------------------------
         * MARKERS Init
         */
  	  	this.__init = function() {

			if (this.__iconUrl !== null) {
				this.__iconImage = this.__iconUrl;
			}

            // No icon image
            //
			if (this.__iconImage === null) {
				this.__gmarker = new google.maps.Marker(
                    {
                        position: this.__position,
                        map: this.__map.gmap,
                        animation: this.__animationType,
                        title: this.__title
                    });

			// Use specified icon
            //
			} else {
                var shadowKey = this.__iconUrl;
                if (typeof cslmap.shadows[shadowKey] === 'undefined') {
                    var shadow = this.__iconUrl.replace('/_(.*?)\.png/', '_shadow.png');
                    jQuery.ajax(
                        {
                            url: shadow,
                            type: 'HEAD',
                            async: false,
                            error: function() { cslmap.shadows[shadowKey] = slplus.plugin_url+'/images/icons/blank.png'; },
                            success: function() { cslmap.shadows[shadowKey] = shadow; }
                        }
                    );
                }
                this.__shadowImage = cslmap.shadows[shadowKey];
                this.buildMarker();
            }
  	  	};

        /*------------------------
         * MARKERS buildMarker
         */
		this.buildMarker = function() {
			this.__gmarker = new google.maps.Marker(
  	  	  	{
				position: this.__position,
  	  	  	  	map: this.__map.gmap,
  	  	  	  	animation: this.__animationType,
				shadow: this.__shadowImage,
				icon: this.__iconImage,
				zIndex: 0,
  	  	  	  	title: this.__title
  	  	  	});
		};

  	  	this.__init();
  	},


    /***************************************************************************
     *
     * UTILITIES SUBCLASS
     *
     */
	Utils: function() {

		/***********************************
         *
		 * Create the lightbox email form.
		 *
		 */
		this.show_email_form = function(to) {
			emailWin=window.open("about:blank","",
				"height=220,width=310,scrollbars=no,top=50,left=50,status=0,toolbar=0,location=0,menubar=0,directories=0,resizable=0");
			with (emailWin.document) {
				writeln("<html><head><title>Send Email To " + to + "</title></head>");

				writeln("<body scroll='no' onload='self.focus()' onblur='close()'>");

				writeln("<style>");
				writeln(".form_entry{ width: 300px; clear: both;} ");
				writeln(".form_submit{ width: 300px; text-align: center; padding: 12px;} ");
				writeln(".to{ float: left; font-size: 12px; color: #444444; } ");
				writeln("LABEL{ float: left; width: 75px;  text-align:right; ");
				writeln(      " font-size: 11px; color: #888888; margin: 3px 3px 0px 0px;} ");
				writeln("INPUT type=['text']{ float: left; width: 225px; text-align:left; } ");
				writeln("INPUT type=['submit']{ padding-left: 120px; } ");
				writeln("TEXTAREA { width: 185px; clear: both; padding-left: 120px; } ");
				writeln("</style>");

				writeln("<form id='emailForm' method='GET'");
				writeln(    " action='"+slplus.core_url+"send-email.php'>");

				writeln("    <div id='email_form_content'>");

				writeln("        <div class='form_entry'>");
				writeln("            <label for='email_to'>To:</label>");
				writeln("            <input type='hidden' name='email_to' value='" + to + "'/>");

				writeln("            <div class='to'>"+to+"</div>");
				writeln("        </div>");


				writeln("        <div class='form_entry'>");
				writeln("            <label for='email_name'>Your Name:</label>");
				writeln("            <input name='email_name' value='' />");
				writeln("        </div>");

				writeln("        <div class='form_entry'>");
				writeln("            <label for='email_from'>Your Email:</label>");
				writeln("            <input name='email_from' value='' />");
				writeln("        </div>");

				writeln("        <div class='form_entry'>");
				writeln("            <label for='email_subject'>Subject:</label>");
				writeln("            <input name='email_subject'  value='' />");
				writeln("        </div>");

				writeln("        <div class='form_entry'>");
				writeln("            <label for='email_message'>Message:</label>");
				writeln("            <textarea name='email_message'></textarea>");
				writeln("        </div>");
				writeln("    </div>");

				writeln("    <div class='form_submit'>");
				writeln("        <input type='submit' value='Send Message'>");
				writeln("    </div>");
                writeln("            <input type='hidden' name='valid' value=csl_ajax.nonce/>");
				writeln("</form>");
				writeln("</body></html>");
				close();
			}
		};

		/**************************************
		 * function: escapeExtended()
		 *
		 * Escape any extended characters, such as � in f�r.
		 * Standard US ASCII characters (< char #128) are unchanged
		 *
		 */
		this.escapeExtended = function(string)
		{
			return string;
		};
	},

    /***************************************************************************
     *
     * INFO SUBCLASS
     *
	 ***************************
  	 * Popup info window Object
  	 * usage:
  	 * create a google info window
  	 * parameters:
  	 * 	content: the content to show by default
  	 */
  	Info: function (content) {
		this.__content = content;
  	  	this.__position = position;

  	  	this.__anchor = null;
  	  	this.__gwindow = null;
  	  	this.__gmap = null;

  	  	this.openWithNewContent = function(map, object, content) {
			this.__content = content;
  	  		this.__gwindow = setContent = this.__content;
  	  	  	this.open(map, object);
  	  	};

  	  	this.open = function(map, object) {
			this.__gmap = map.gmap;
  	  	  	this.__anchor = object;
  	  	  	this.__gwindow.open(this.__gmap, this.__anchor);
  	  	};

  	  	this.close = function() {
			this.__gwindow.close();
  	  	};

  	  	this.__init = function() {
			this.__gwindow = new google.maps.InfoWindow(
  	  	  	{
				content: this.__content
  	  	  	});
  	  	};

  	  	this.__init();
  	},

    /***************************************************************************
     *
     * MAP SUBCLASS
     *
  	 ***************************
  	 * Map Object
  	 * usage:
  	 * create a google maps object linked to a map/canvas id
  	 * parameters:
  	 * 	aMapNumber: the id/canvas of the map object to load from php side
  	 */
  	Map: function(aMapCanvas) {
		//private: map number to look up at init
  	  	this.__mapCanvas = aMapCanvas;

		//function callbacks
		this.tilesLoaded = null;

  	  	//php passed vars set in init
  	  	this.address = null;
  	  	this.canvasID = null;
  	  	this.draggable = true;
  	  	this.tilt = 45; //n
  	  	this.zoomStyle = 0; // 0 = default, 1 = small, 2 = large
		this.markers = null;

		//slplus options
        this.usingSensor = false;
		this.disableScroll = null;
		this.disableDir = null;
		this.distanceUnit = null;
		this.map3dControl = null;
		this.mapDomain = null;
		this.mapHomeIconUrl = null;
		this.mapHomeIconWidth = null;
		this.mapHomeIconHeight = null;
		this.mapEndIconUrl = null;
		this.mapEndIconWidth = null;
		this.mapEndIconHeight = null;
		this.mapScaleControl = null;
		this.mapType = null;
		this.mapTypeControl = null;
		this.showTags = null;
		this.overviewControl = null;
		this.useEmailForm = null;
		this.websiteLabel = null;

  	  	//gmap set variables
  	  	this.options = null;
  	  	this.gmap = null;
  	  	this.centerMarker = null;
		this.marker = null;
		this.infowindow = new google.maps.InfoWindow();
		this.bounds = null;
		this.homeAddress = null;
		this.homePoint = null;
		this.lastCenter = null;
		this.lastRadius = null;
		this.loadedOnce = false;
        this.centerLoad = false;

        // missing shadows
        //
        this.shadows = new Object;

		/***************************
  	  	 * function: __init()
  	  	 * usage:
  	  	 * Called at the end of the 'class' due to some browser's quirks
  	  	 * parameters: none
  	  	 * returns: none
  	  	 */
  	  	this.__init = function() {

            if (typeof slplus !== 'undefined') {
                this.mapType = slplus.map_type;
                this.disableScroll = !!slplus.disable_scroll;
                this.distanceUnit = slplus.distance_unit;
                this.mapDomain = slplus.map_domain;
                this.mapHomeIconUrl = slplus.map_home_icon;
                this.mapHomeIconWidth = slplus.map_home_icon_sizew;
                this.mapHomeIconHeight = slplus.map_home_icon_sizeh;
                this.mapEndIconUrl = slplus.map_end_icon;
                this.mapEndIconWidth = slplus.map_end_sizew;
                this.mapEndIconHeight = slplus.map_end_sizeh;
                this.mapScaleControl = !!slplus.map_scalectrl;
                this.mapTypeControl = !!slplus.map_typectrl;
                this.showTags = slplus.show_tags;
                this.overviewControl = !!(parseInt(slplus.overview_ctrl));
                this.useEmailForm = !!slplus.use_email_form;
                this.websiteLabel = slplus.website_label;
                this.disableDefaultUI = false;

                if (!slplus.disable_dir) {
                    this.loadedOnce = true;
                }

                // Setup address
                // Use the entry form value if set, otherwise use the country
                //
                var addressInput = this.getSearchAddress();
                if (typeof addressInput === 'undefined') {
                    this.address = slplus.map_country;
                } else {
                    this.address = addressInput;
                }

            } else {
                alert('Store Locator Plus script not loaded properly.');
            }
  	  	};

        /***************************
  	  	 * function: __buildMap
  	  	 * usage:
  	  	 * 		Builds the map with the specified center
  	  	 * parameters:
  	  	 * 		center:
		 *			the specified center or homepoint
  	  	 * returns: none
  	  	 */
        this.__buildMap = function(center) {
            if (this.gmap === null)
            {
                this.options = {
                    mapTypeControl: this.mapTypeControl,
                    mapTypeId: this.mapType,
                    overviewMapControl: this.overviewControl,
                    scrollwheel: !this.disableScroll,
                    center: center,
                    zoom: parseInt(slplus.zoom_level),
                    scaleControl: this.mapScaleControl,
                    overviewMapControlOptions: { opened: this.overviewControl }
                };
                this.debugSearch(this.options);
                slpMapDiv = document.getElementById('map');
                this.gmap = new google.maps.Map(slpMapDiv, this.options);
                this.debugSearch(this.gmap);
                //this forces any bad css from themes to fix the "gray bar" issue by setting the css max-width to none
                var _this = this;
                google.maps.event.addListener(this.gmap, 'bounds_changed', function() {
                    _this.__waitForTileLoad.call(_this);
                });

                this.debugSearch(this.usingSensor);
                if (this.usingSensor) {
                    this.homePoint = center;
                    this.addMarkerAtCenter();
                }

                // If immediately show locations is enabled.
                //
                if (slplus.load_locations === '1') {
                        if (slplus.options.no_homeicon_at_start !== '1') {
                        this.homePoint = center;
                        this.addMarkerAtCenter();
                        }
                        var tag_to_search_for = this.saneValue('tag_to_search_for', '');

                        // Default radius for immediately show locations
                        // uses setting from admin panel first,
                        // then the default from the drop down menu,
                        // then 10000 if neither are working.
                        //
                        var radius = 10000;
                        slplus.options.initial_radius = slplus.options.initial_radius.replace(/\D/g,'');
                        if (/^[0-9]+$/.test(slplus.options.initial_radius)) {
                            radius = slplus.options.initial_radius;
                        } else {
                            radius = this.saneValue('radiusSelect');
                        }

                        this.loadMarkers(center, radius, tag_to_search_for);
                }
            }
        };

		/***************************
  	  	 * function: __waitForTileLoad
  	  	 * usage:
		 * Notifies as the map changes that we'd like to be nofified when the tiles are completely loaded
  	  	 * parameters:
  	  	 * 	none
  	  	 * returns: none
  	  	 */
		this.__waitForTileLoad = function() {
			var _this = this;
			if (this.__tilesLoaded === null)
			{
				this.__tilesLoaded = google.maps.event.addListener(this.gmap, 'tilesloaded', function() {
					_this.__tilesAreLoaded.call(_this);
				});
			}
		};

		/***************************
  	  	 * function: __tilesAreLoaded
  	  	 * usage:
		 * All the tiles are loaded, so fix their css
  	  	 * parameters:
  	  	 * 	none
  	  	 * returns: none
  	  	 */
		this.__tilesAreLoaded = function() {
			jQuery('#map').find('img').css({'max-width': 'none'});
			google.maps.event.removeListener(this.__tilesLoaded);
			this.__tilesLoaded = null;
		};

  	  	/***************************
  	  	 * function: addMarkerAtCenter
  	  	 * usage:
  	  	 * Puts a pretty marker right smack in the middle
  	  	 * parameters:
  	  	 * 	none
  	  	 * returns: none
  	  	 */
  	  	this.addMarkerAtCenter = function() {
			if (this.centerMarker) {
				this.centerMarker.__gmarker.setMap(null);
			}
			if (this.homePoint) {
				this.centerMarker = new csl.Marker(csl.Animation.None, this, "", this.homePoint, this.mapHomeIconUrl, this.mapHomeIconWidth, this.mapHomeIconHeight);
			}
  	  	};

		/***************************
  	  	 * function: clearMarkers
  	  	 * usage:
  	  	 * 		Clears all the markers from the map and releases it for GC
  	  	 * parameters:
  	  	 * 	none
  	  	 * returns: none
  	  	 */
		this.clearMarkers = function() {
                    if (this.markers) {
                        for (markerNumber in this.markers) {
                            if (typeof this.markers[markerNumber] !== 'undefined') {
                                if (typeof this.markers[markerNumber].__gmarker !== 'undefined') {
                                    this.markers[markerNumber].__gmarker.setMap(null);
                                }
                            }
                        }
                        this.markers.length = 0;
                    }
		};

		/***************************
  	  	 * function: putMarkers
  	  	 * usage:
  	  	 * 		Puts an array of markers on the map with the given animation set
  	  	 * parameters:
  	  	 * 		markerList:
		 *			a list of csl.Markers
		 *		animation:
		 *			the csl.Animation type
  	  	 * returns: none
  	  	 */
		this.putMarkers = function(markerList, animation) {

			this.markers = [];
			if (this.loadedOnce) {
				var sidebar = document.getElementById('map_sidebar');
				sidebar.innerHTML = '';
			}

			//don't animate for a large set of results
            var markerCount = markerList.length;
			if (markerCount > 25) animation = csl.Animation.None;

			var bounds;
            var locationIcon;
            for (var markerNumber = 0 ; markerNumber < markerCount; ++markerNumber) {
				var position = new google.maps.LatLng(markerList[markerNumber].lat, markerList[markerNumber].lng);

				if (markerNumber === 0) {
					bounds = new google.maps.LatLngBounds();
					if (this.homePoint) {
                        bounds.extend(this.homePoint);
                    } else {
                        if (this.centerLoad) {
                            bounds.extend(this.gmap.getCenter());
                        }
                        else {
                            this.centerLoad = true;
                        }
                    }
					bounds.extend(position);
				} else {
					bounds.extend(position);
				}

                locationIcon =
                        (
                          (markerList[markerNumber].icon !== null)               &&
                          (typeof markerList[markerNumber].icon !== 'undefined') &&
                          (markerList[markerNumber].icon.length > 4)                    ?
                            markerList[markerNumber].icon :
                            this.mapEndIconUrl
                        );
				this.markers.push(new csl.Marker(animation, this, "", position, locationIcon, this.mapEndIconWidth, this.mapEndIconHeight ));
				_this = this;

				//create a sidebar entry
				if (this.loadedOnce) {
					var sidebarEntry = this.createSidebar(markerList[markerNumber]);
					sidebar.appendChild(sidebarEntry);
                    jQuery('div#map_sidebar span:empty').hide();
				}

				//create info windows
                //
				google.maps.event.addListener(this.markers[markerNumber].__gmarker, 'click',
				(function (infoData, marker) {
					return function() {
						_this.__handleInfoClicks.call(_this, infoData, marker);
					}
				})(markerList[markerNumber], this.markers[markerNumber]));

				if (this.loadedOnce) {
                    
                    // Whenever the location result entry is <clicked> do this...
                    //
					google.maps.event.addDomListener(sidebarEntry, 'click',
					(function(infoData, marker) {
						return function() {
							_this.__handleInfoClicks.call(_this, infoData, marker);
						};
					})(markerList[markerNumber], this.markers[markerNumber]));
				}
			}

			this.loadedOnce = true;

			//check for results
			if (markerList.length === 0) {
                if ( (typeof this.homePoint !== 'undefined') &&
                     (this.homePoint !== null)
                   ) {
                    this.gmap.panTo(this.homePoint);
                }
                var sidebar = document.getElementById('map_sidebar');
                sidebar.innerHTML = '<div class="no_results_found"><h2>'+slplus.msg_noresults+'</h2></div>';
                jQuery('#map_sidebar').trigger('contentchanged');
            } else {
                jQuery('#map_sidebar').trigger('contentchanged');
            }

			if ((bounds !== null) && (typeof bounds !== 'undefined')) {
				this.bounds = bounds;
				this.gmap.fitBounds(this.bounds);

                // Searches, use Google Bounds - and adjust by the tweak.
                // Initial Load Only - Use "Zoom Level"
                //
                var newZoom =
                    Math.max(Math.min(
                        (
                         (
                          (slplus.options.no_autozoom !== "1") &&
                          (this.loadedOnce || (markerList.length >1))
                          ) ?
                          this.gmap.getZoom() - parseInt(slplus.zoom_tweak) :
                          parseInt(slplus.zoom_level)
                        )
                    ,20),1)
                    ;
                this.gmap.setZoom(newZoom);
			}
		};

		/***************************
  	  	 * function: bounceMarkers
  	  	 * usage:
  	  	 * 		Puts a list of markers on the screen and makes them bounce
  	  	 * parameters:
  	  	 * 		markerlist:
		 *			the list of csl.Markers to add to the map
  	  	 * returns: none
  	  	 */
		this.bounceMarkers = function(markerList) {
			this.clearMarkers();
			this.putMarkers(markerList, csl.Animation.None);
		};

		/***************************
  	  	 * function: dropMarkers
  	  	 * usage:
  	  	 * 		drops a list of csl.Markers on the map
  	  	 * parameters:
  	  	 * 		markerList:
		 *			the list of csl.Markers to drop
  	  	 * returns: none
  	  	 */
		this.dropMarkers = function(markerList) {
			this.clearMarkers();
			this.debugSearch('dropping');
			this.putMarkers(markerList, csl.Animation.Drop);
		};

		/***************************
  	  	 * function: private handleInfoClicks
  	  	 * usage:
  	  	 * 		Sets the content to the info window and builds the sidebar when a user clicks a marker
  	  	 * parameters:
  	  	 * 		infoData:
		 *			the information to build the info window from (ajax result)
		 *		marker:
		 *			the csl.Marker to add the information to
  	  	 * returns: none
  	  	 */
		this.__handleInfoClicks = function(infoData, marker) {
			this.debugSearch(infoData);
			this.debugSearch(marker);
			this.debugSearch(this);
			this.infowindow.setContent(this.createMarkerContent(infoData));
			//this.infowindow.setContent('hi');
			this.infowindow.open(this.gmap, marker.__gmarker);
		};

        /**
         * Geocode an address on the search input field and display on map.
         * 
         * @return {undefined}
         */
  	  	this.doGeocode = function() {
			var geocoder = new google.maps.Geocoder();
  	  	  	var _this = this;

  	  	  	geocoder.geocode(
				{
					'address': this.address
  	  	  	  	},
  	  	  	  	function (results, status) {
                    if (status === 'OK' && results.length > 0)
                    {
                        // if the map hasn't been created, then create one
                        if (_this.gmap === null)
                        {
                            _this.__buildMap(results[0].geometry.location);
                        }
                        //the map has been created so shift the center of the map
                        else {
                            //move the center of the map
                            _this.homePoint = results[0].geometry.location;
                            _this.homeAdress = results[0].formatted_address;

                            _this.addMarkerAtCenter();
                            var tag_to_search_for = _this.saneValue('tag_to_search_for', '');
                            //do a search based on settings
                            var radius = _this.saneValue('radiusSelect');
                            _this.loadMarkers(results[0].geometry.location, radius, tag_to_search_for);
                        }
                        //if the user entered an address, replace it with a formatted one
                        var addressInput = _this.saneValue('addressInput','');
                        if (addressInput !== '') {
                            addressInput = results[0].formatted_address;
                        }
                    } else {
                        //address couldn't be processed, so use the center of the map
                        var tag_to_search_for = _this.saneValue('tag_to_search_for', '');
                        var radius = _this.saneValue('radiusSelect');
                        _this.loadMarkers(null, radius, tag_to_search_for);
                    }

                }

  	  	  	);
  	  	};

        /***************************
  	  	 * function: __getMarkerUrl
  	  	 * usage:
  	  	 * 		Builds the url for store pages
  	  	 * parameters:
  	  	 * 		aMarker:
		 *			the ajax result to build the information from
  	  	 * returns: an url
        */
        this.__getMarkerUrl = function(aMarker) {
            var url = '';

            if (typeof aMarker === "object") {
                //add an http to the url
                if ((slplus.use_pages_links === "on") && (aMarker.sl_pages_url !== '')) {
                    url = aMarker.sl_pages_url;
                } else if (aMarker.url !== '') {
                    if ((aMarker.url.indexOf("http://" ) === -1)  &&
                        (aMarker.url.indexOf("https://") === -1)
                       ){
                        aMarker.url = "http://" + aMarker.url;
                    }
                    if (aMarker.url.indexOf(".") !== -1) {
                        url = aMarker.url;
                    }
                }
            }

            return url;
        };

        /***************************
  	  	 * function: __createAddress
  	  	 * usage:
  	  	 * 		Build a formatted address string
  	  	 * parameters:
  	  	 * 		aMarker:
		 *			the ajax result to build the information from
  	  	 * returns: a formatted address string
        */
        this.__createAddress = function(aMarker) {

            var address = '';
            if (aMarker.address !== '') {
                address += aMarker.address;
            }

            if (aMarker.address2 !== '') { address += ", " + aMarker.address2; }

            if (aMarker.city !== '') { address += ", " + aMarker.city; }

            if (aMarker.state !== '') { address += ", " + aMarker.state; }

            if (aMarker.zip !== '') { address += ", " + aMarker.zip; }

            if (aMarker.country !== '') { address += ", " + aMarker.country; }

            return address;
        };

		/***************************
  	  	 * function: createMarkerContent
  	  	 * usage:
  	  	 * 		Builds the html div for the info window
  	  	 * parameters:
  	  	 * 		aMarker:
					the ajax result to build the information from
  	  	 * returns: an html <div>
  	  	 */
		this.createMarkerContent = function(aMarker) {
			var html = '';

            var url = this.__getMarkerUrl(aMarker);

			if (url !== '') {
				html += "| <a href='"+url+"' target='"+((slplus.use_same_window==="on")?'_self':'_blank')+"' id='slp_marker_website' class='storelocatorlink'><nobr>" + slplus.website_label +" </nobr></a>";
			}

			if (aMarker.email.indexOf("@") !== -1 && aMarker.email.indexOf(".") !== -1) {
				if (!this.useEmailForm) {
					html += "| <a href='mailto:"+aMarker.email+"' target='_blank' id='slp_marker_email' class='storelocatorlink'><nobr>" + aMarker.email +"</nobr></a>";
				} else {
					html += "| <a href='javascript:cslutils.show_email_form("+'"'+aMarker.email+'"'+");' id='slp_marker_email' class='storelocatorlink'><nobr>" + aMarker.email +"</nobr></a><br/>";
				}
			}

			if (aMarker.image.indexOf(".") !== -1) {
				html+="<br/><img src='"+aMarker.image+"' class='sl_info_bubble_main_image'>";
			} else {
				aMarker.image = "";
			}

			if (aMarker.description !== '') {
				html+="<br/>"+aMarker.description+"";
			} else {
				aMarker.description = '';
			}

			if (aMarker.hours !== '') {
                var decoded = jQuery("<div/>").html(aMarker.hours).text();
				html+="<br/><span class='location_detail_label'>"+slplus.label_hours+"</span> "+ decoded;
			} else {
				aMarker.hours = "";
			}

			if (aMarker.phone !== '') {
				html+="<br/><span class='location_detail_label'>"+slplus.label_phone+"</span> "+aMarker.phone;
			}
			if (aMarker.fax !== '') {
				html+="<br/><span class='location_detail_label'>"+slplus.label_fax+"</span> "+aMarker.fax;
			}

			var address = this.__createAddress(aMarker);

			if (slplus.show_tags) {
				if (jQuery.trim(aMarker.tags) !== '') {
					var tagclass = 'bubble_'+aMarker.tags.replace(/\W/g,'_');
					html += '<br/><div class="'+tagclass+'"><span class="slp_info_bubble_tags">'+aMarker.tags + '</span></div>';
				}
			}


			var complete_html = '<div id="sl_info_bubble"><!--tr><td--><strong>' + 
                    aMarker.name + '</strong><br>' +
                    address + '<br/> ' +
                    '<a href="http://' + slplus.map_domain +
                        '/maps?saddr=' + encodeURIComponent(this.getSearchAddress(this.address)) +
                        '&daddr=' + encodeURIComponent(address) +
                        '" target="_blank" class="storelocatorlink">'+
                        slplus.label_directions+
                        '</a> ' + html +
                        '<br/><!--/td></tr--></div>';

			return complete_html;
		};

        /**
         * Return a proper search address for directions.
         * Use the address entered if provided.
         * Use the GPS coordinates if not and use location is on and coords available.
         * Otherwise use the center of the country.
         */
        this.getSearchAddress = function (defaultAddress) {
            var searchAddress = jQuery('#addressInput').val();
            if (!searchAddress) {
                if ((slplus.use_sensor) && (sensor.lat !== 0.00) && (sensor.lng !== 0.00)) {
                    searchAddress = sensor.lat + ',' + sensor.lng;
                } else {
                    searchAddress = defaultAddress;
                }
            }
            return searchAddress;
        };

        /**
         * debug the search mechanism
         */
		this.debugSearch = function(toLog) {
		    if (slplus.debug_mode === 1) {
                try {
                    if (console) {
				        console.log(toLog);
                    }
                }
                catch (ex)
                {
                }
			}
		};

        /**
         * Get a sane value from the HTML document.
         *
         * @param {string} id of control to look at
         * @param {string} default value to return
         * @return {undef}
         */
		this.saneValue = function(id, defaultValue) {
			var name = document.getElementById(id);
			if (name === null) {
				name = defaultValue;
			}
			else {
				name = name.value;
			}
			return name;
		};

		/***************************
  	  	 * function: loadMarkers
  	  	 * usage:
  	  	 * 		Sends an ajax request and drops the markers on the map
  	  	 * parameters:
  	  	 * 		center:
		 *			the center of the map (where to center to)
  	  	 * returns: none
  	  	 */
		this.loadMarkers = function(center, radius, tags) {

            //determines if we need to invent real variables (usually only done at the beginning)
            if (center === null) { center = this.gmap.getCenter(); }
            if (radius === null) { radius = 40000; }
            this.lastCenter = center;
            this.lastRadius = radius;
            if (tags === null) { tags = ''; }

            var _this = this;
            var ajax = new csl.Ajax();

            // Setup our variables sent to the AJAX listener.
            //
            var action = {
                address : this.saneValue('addressInput', 'no address entered'),
                formdata: jQuery('#searchForm').serialize(),
                lat     : center.lat(),
                lng     : center.lng(),
                name    : this.saneValue('nameSearch', ''),
                radius  : radius,
                tags    : tags
             };

            // On Load
            if (slplus.load_locations === '1') {
                action.action = 'csl_ajax_onload';
                slplus.load_locations = '0';

            // Search
            } else {
                action.action = 'csl_ajax_search';
            }

            // Send AJAX call
            //
            ajax.send(action, function (response) {
                    if (typeof response.response !== 'undefined') {
                        _this.bounceMarkers.call(_this, response.response);
                    } else {
                        if (window.console) { console.log('SLP server did not send back a valid JSONP response for ' + action.action + '.'); }
                    }
                });
		};

		/***************************
  	  	 * function: tagFilter
  	  	 * usage:
  	  	 * 		Sends an ajax request to only get the tags in the current search results
  	  	 * parameters:
		 *		none
  	  	 * returns: none
  	  	 */
		 this.tagFilter = function() {

			//repeat last search passing tags
			var tag_to_search_for = this.saneValue('tag_to_search_for', '');
			this.loadMarkers(this.lastCenter, this.lastRadius, tag_to_search_for);
			jQuery('#map_box_image').hide();
			jQuery('#map_box_map').show();
		 };

		/***************************
  	  	 * function: searchLocations
  	  	 * usage:
  	  	 * 		begins the process of returning search results
  	  	 * parameters:
  	  	 * 		none
  	  	 * returns: none
  	  	 */
		this.searchLocations = function() {
            var address = this.saneValue('addressInput', '');
            jQuery('#map_box_image').hide();
		    jQuery('#map_box_map').show();
            google.maps.event.trigger(this.gmap, 'resize');

			// Address was given, use it...
			//
			if (address !== '') {
				this.address = cslutils.escapeExtended(address);
				this.doGeocode();

			}
			else {
				var tag_to_search_for = this.saneValue('tag_to_search_for', '');
				var radius = this.saneValue('radiusSelect');
				this.loadMarkers(this.gmap.getCenter(), radius, tag_to_search_for);
			}
		};

		/**
  	  	 * Render a marker in the results section
         *
         * @param {object} aMarker marker data for a single location
  	  	 * @returns {string} a html div with the data properly displayed
  	  	 */
		this.createSidebar = function(aMarker) {
			var div = document.createElement('div');
			var link = '';
			var street = aMarker.address;
			var street2 = aMarker.address2;
			var city = aMarker.city;
			var state = aMarker.state;
			var zip = aMarker.zip;

            var url = this.__getMarkerUrl(aMarker);

			if (url !== '') {
				link = link = "<a href='"+url+"' target='"+((slplus.use_same_window==="on")?'_self':'_blank')+"' class='storelocatorlink'><nobr>" + slplus.website_label +"</nobr></a><br/>";
			}

			var elink = '';
			if (aMarker.email.indexOf('@') !== -1 && aMarker.email.indexOf('.') !== -1) {
				if (!slplus.use_email_form) {
					elink = "<a href='mailto:"+aMarker.email+"' target='_blank'  id='slp_marker_email' class='storelocatorlink'><nobr>" + aMarker.email +"</nobr></a><br/>";
				}
				else {
					elink = "<a href='javascript:cslutils.show_email_form("+'"'+aMarker.email+'"'+");'  id='slp_marker_email' class='storelocatorlink'><nobr>" + aMarker.email +"</nobr></a><br/>";
				}
			}

			//if we are showing tags in the table
			//
			var tagInfo = '';
			if (slplus.show_tags) {
				if (jQuery.trim(aMarker.tags) !== '') {
					var tagclass = aMarker.tags.replace(/\W/g,'_');
					tagInfo = '<br/><div class="'+tagclass+' slp_result_table_tags"><span class="tagtext">'+aMarker.tags+'</span></div>';
				}
			}

			//keep empty data lines out of the final result
			//
            var city_state_zip = '';
            if (jQuery.trim(city) !== '') {
                city_state_zip += city;
                if (jQuery.trim(state) !== '' || jQuery.trim(zip) !== '') {
                    city_state_zip += ', ';
                }
            }
            if (jQuery.trim(state) !== '') {
                city_state_zip += state;
                if (jQuery.trim(zip) !== '') {
                    city_state_zip += ', ';
                }
            }
            if (jQuery.trim(zip) !== '') {
                city_state_zip += zip;
            }
            if (jQuery.trim(aMarker.phone) !== '') {
                thePhone = slplus.label_phone+ aMarker.phone;
            } else {
                thePhone = '';
            }
            if (jQuery.trim(aMarker.fax) !== '') {
                theFax = slplus.label_fax + aMarker.fax;
            } else {
                theFax = '';
            }

            var address = this.__createAddress(aMarker);

            // JavaScript version of sprintf
            //
            String.prototype.format = function() {
             var args = arguments;
             return this.replace(/{(\d+)(\.(\w+)\.?(\w+)?)?}/g, function(match, number, dotsubname, subname,subsubname) {
               return typeof args[number] !== 'undefined'
                 ? typeof args[number] !== 'object'
                     ? args[number]
                     : typeof args[number][subname] !== 'object'
                         ? args[number][subname]
                         : (args[number][subname] !== null)
                            ? args[number][subname][subsubname]
                            : ''
                 : match
               ;
             });
           };

         /** Create the results table
          *
          * use {0} to {17} to place in the output
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
          *              {18} aMarker
          */
         var decodedHours = jQuery("<div/>").html(aMarker.hours).text();
 		 div.innerHTML = slplus.results_string.format(
                        aMarker.name,
                        parseFloat(aMarker.distance).toFixed(1),
                        slplus.distance_unit,
                        street,
                        street2,
                        city_state_zip,
                        thePhone,
                        theFax,
                        link,
                        elink,
                        slplus.map_domain,
                        encodeURIComponent(this.getSearchAddress(this.address)),
                        encodeURIComponent(address),
                        slplus.label_directions,
                        tagInfo,
                        aMarker.id,
                        aMarker.country,
                        decodedHours,
                        aMarker
                      )
                      ;
			div.className = 'results_entry';
            div.id = 'slp_results_entry_'+aMarker.id;

			return div;
		};

  	  	//dumb browser quirk trick ... wasted two hours on that one
  	  	this.__init();
	}
};


/***************************************************************************
 *
 * CSL Main Execution
 *
 */
var cslmap;
var cslutils;

/**
 * Setup the map settings and get it rendered.
 *
 * @returns {undefined}
 */
function InitializeTheMap() {

    // Initialize Utilities
    //
	cslutils = new csl.Utils();

    // Initialize the map based on sensor activity
    //
    // There are 4 possibilities, and we set the cslmap object as
    // late as possible for each...
    //
    // 1) Sensor Active, Location Service OK
    // 2) Sensor Active, Location Service FAIL
    // 3) Sensor Active, But No Location Support
    // 4) Sensor Inactive
    //
    if (slplus.use_sensor) {
        sensor = new csl.LocationServices();
        if (sensor.LocationSupport) {
            sensor.currentLocation(

                // 1) Success on Location
                //
                function(loc) {
                	cslmap = new csl.Map();
                    cslmap.usingSensor = true;
                    clearTimeout(sensor.location_timeout);
                    sensor.lat = loc.coords.latitude;
                    sensor.lng = loc.coords.longitude;
                    cslmap.__buildMap(new google.maps.LatLng(loc.coords.latitude, loc.coords.longitude));
                },

                // 2) Failed on location
                //
                function(error) {
                    clearTimeout(sensor.location_timeout);
                	cslmap = new csl.Map();
                    cslmap.doGeocode();
                }
            );
            
        // 3) GPS Sensor Not Working (like IE8)
        //
        } else {
            slplus.use_sensor = false;
        	cslmap = new csl.Map();
            cslmap.doGeocode();            
        }

    // 4) No Sensor
    //
    } else {
    	cslmap = new csl.Map();
        cslmap.doGeocode();
    }
}

/*
 * When the document has been loaded...
 *
 */
jQuery(document).ready(
    function() {
                /*---------------------------------
                 * formparams minified js
                 */
                var radioCheck = /radio|checkbox/i,
                    keyBreaker = /[^\[\]]+/g,
                    numberMatcher = /^[\-+]?[0-9]*\.?[0-9]+([eE][\-+]?[0-9]+)?$/;

                var isNumber = function( value ) {
                    if ( typeof value === 'number' ) {
                        return true;
                    }

                    if ( typeof value !== 'string' ) {
                        return false;
                    }

                    return value.match(numberMatcher);
                };
                jQuery.fn.extend({
                        /**
                         * @parent dom
                         * @download http://jmvcsite.heroku.com/pluginify?plugins[]=jquery/dom/form_params/form_params.js
                         * @plugin jquery/dom/form_params
                         * @test jquery/dom/form_params/qunit.html
                         * <p>Returns an object of name-value pairs that represents values in a form.
                         * It is able to nest values whose element's name has square brackets. </p>
                         * Example html:
                         * @codestart html
                         * &lt;form>
                         *   &lt;input name="foo[bar]" value='2'/>
                         *   &lt;input name="foo[ced]" value='4'/>
                         * &lt;form/>
                         * @codeend
                         * Example code:
                         * @codestart
                         * $('form').formParams() //-> { foo:{bar:2, ced: 4} }
                         * @codeend
                         *
                         * @demo jquery/dom/form_params/form_params.html
                         *
                         * @param {Boolean} [convert] True if strings that look like numbers and booleans should be converted.  Defaults to true.
                         * @return {Object} An object of name-value pairs.
                         */
                        formParams: function( convert ) {
                            if ( this[0].nodeName.toLowerCase() == 'form' && this[0].elements ) {

                                return jQuery(jQuery.makeArray(this[0].elements)).getParams(convert);
                            }
                            return jQuery("input[name], textarea[name], select[name]", this[0]).getParams(convert);
                        },
                        getParams: function( convert ) {
                            var data = {},
                                current;

                            convert = convert === undefined ? true : convert;

                            this.each(function() {
                                var el = this,
                                    type = el.type && el.type.toLowerCase();
                                //if we are submit, ignore
                                if ((type == 'submit') || !el.name ) {
                                    return;
                                }

                                var key = el.name,
                                    value = jQuery.data(el, "value") || jQuery.fn.val.call([el]),
                                    isRadioCheck = radioCheck.test(el.type),
                                    parts = key.match(keyBreaker),
                                    write = !isRadioCheck || !! el.checked,
                                    //make an array of values
                                    lastPart;

                                if ( convert ) {
                                    if ( isNumber(value) ) {
                                        value = parseFloat(value);
                                    } else if ( value === 'true' || value === 'false' ) {
                                        value = Boolean(value);
                                    }

                                }

                                // go through and create nested objects
                                current = data;
                                for ( var i = 0; i < parts.length - 1; i++ ) {
                                    if (!current[parts[i]] ) {
                                        current[parts[i]] = {};
                                    }
                                    current = current[parts[i]];
                                }
                                lastPart = parts[parts.length - 1];

                                //now we are on the last part, set the value
                                if ( lastPart in current && type === "checkbox" ) {
                                    if (!jQuery.isArray(current[lastPart]) ) {
                                        current[lastPart] = current[lastPart] === undefined ? [] : [current[lastPart]];
                                    }
                                    if ( write ) {
                                        current[lastPart].push(value);
                                    }
                                } else if ( write || !current[lastPart] ) {
                                    current[lastPart] = write ? value : undefined;
                                }

                            });
                            return data;
                        }
                    }
                );

                // Our map initialization
                //
                if (jQuery('div#sl_div'          ).is(":visible")) {
                    InitializeTheMap();
                }
    }
);