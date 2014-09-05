/**
  * Panthera main class
  *
  * @author Damian Kęska
  */

function panthera(){}

panthera.debug = true;
panthera.ajaxSlide = false;
panthera.defaultMessageHandler = '';
originalPageTitle = '';

$(document).ready( function () {
    originalPageTitle = $('title').html();
});

/**
  * Make a POST request with any response (default: json)
  *
  * @param json data {url, data, success, messageBox, dataType, mce, before, spinner, async, progress}
  * @return mixed 
  * @author Damian Kęska
  */

panthera.jsonPOST = function (input) {
    if (panthera.debug == true)
    {
        try {
            console.log("panthera.jsonPOST::"+JSON.stringify(input));
        } catch (e) {
            console.log("panthera.jsonPOST::Cannot print object files, skipped");
        }
    }
    
    var formID = "";
    var messageBox = input.messageBox;
    
    // if we dont want to send any data, just open page using POST method
    if (input.data == undefined)
        input.data = ""
        
    if (input.dataType == undefined)
        input.dataType = "json"; 
        
    // prevent operating on undefined data
    if (input.success == undefined)
        input.success = ""; 
        
    if (input.method != "GET" && input.method != "POST")
        input.method = "POST"
        
    if (input.processData == undefined)
        input.processData = true;
        
    if (input.spinner == undefined && typeof panthera.defaultSpinner !== "undefined")
        input.spinner = panthera.defaultSpinner;
    
    if (!input.messageBox && input.messageBox !== false)
    {
        if (panthera.defaultMessageHandler)
            input.messageBox = panthera.defaultMessageHandler;
    }
        
    // support for mce editors including TinyMCE
    if (typeof input.mce == "string" && typeof mceSave == "function" && input.mce != "tinymce_all")
    {
        if (panthera.debug == true)
            console.log("callback::mce: mceSave("+input.mce+")");
            
        mceSave(input.mce); // save mce editor before sending it's data
        
    // save all found tinyMCE editors
    } else if (input.mce == "tinymce_all") {
        if (typeof tinyMCE == "object")
        {
            var i, t = tinyMCE.editors;
            
            // iterate through all instances
            for (i in t)
            {
                if (t.hasOwnProperty(i))
                {
                    if (panthera.debug == true)
                        console.log("Saving tinyMCE editor instance: "+t[i].id);
                        
                    t[i].save(); // save back to textarea
                }
            }
            
        }
    }
    
    // default value for async value
    if (input.async == undefined)
        input.async = true;
    
    if (input.contentType == undefined)
        input.contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    
    // run panthera built-in ajaxLoaderInit spinner
    if (input.spinner != undefined)
    {
        if (getConstructorName(input.spinner) == "ajaxLoaderInit")
        {
            if (panthera.debug == true)
                console.log("Using ajaxLoaderInit spinner");
        
            input.spinner.ajaxLoaderInit();
        } else if (input.spinner == "nprogress") {
            NProgress.set(0.8);
        }
    }
    
    
    // make a callback before posting data
    if (typeof input.before == "function")
    {
        if (panthera.debug == true)
            console.log("callback::before: "+input.before);
            
        input.before();
    }
       
    // check if data means a form id, if yes we can serialize its inputs
    if (typeof input.data === "string")
    {
        if (input.data.substring(0, 1) == '#')
        {
            if ($(input.data).length > 0)
            {
                formID = input.data;
                input.data = $(formID).serialize();
                
                if (input.isUploading == true || $(formID).attr('enctype') == 'multipart/form-data')
                {
                    input.data = new FormData(document.getElementById(formID.substring(1, formID.length)));
                    input.processData = false;
                    input.contentType = false;
                    input.method = "POST"
                    
                    if (panthera.debug == true)
                        console.log('Going to upload a file with multipart/form-data form');
                }
                    
            } else
                console.log("panthera.jsonPOST::Form with ID="+input.data+" does not exists in current DOM document");
        }
    }
    
    // the url cannot be empty
    if (input.url == undefined || input.url == "")
    {
        // get url address from <form action
        if (formID != "")
            input.url = $(formID).attr('action');
        else {
            console.log("panthera.jsonPOST::Empty url argument - "+input.url);
            return false;
        }
    }
    
    if (panthera.debug == true)
        console.log(input.method+" "+input.url);
        console.log(input.data);
    
    if (input.spinner == "nprogress") 
        NProgress.inc();
    
    // so, lets just use jQuery with our data to make a request
    options = {
    
      // custom progressbar
      xhr: function() {
            var xhr = jQuery.ajaxSettings.xhr();
            
            if (input.spinner == "nprogress") 
                NProgress.inc();
            
            // Tracking progress
            if (xhr.upload)
            {
                xhr.upload.addEventListener("progress", function(evt){
                
                    if (evt.lengthComputable) {  
                        var percentComplete = evt.loaded / evt.total;
                        
                        // process callback
                        if (input.progress != undefined)
                            input.progress((percentComplete*100), evt.loaded, evt.total);
                    }
                }, false); 
            }
            
            return xhr;
      },
    
      type: input.method, // POST by default
      url: input.url,
      cache: false,
      data: input.data,
      async: input.async,
      processData: input.processData,
      contentType: input.contentType,
      
      success: function (response) {
            if (panthera.debug == true)
                console.log("Response: success"); 
                
            if (response == null)
            {
                panthera.logging.output('Got null as response, will not trigger success() callback');
                return false;
            }
            
            try {
                if (input.dataType == 'html')
                {
                    foundTitle = originalPageTitle;
                
                    $(response).each(function (i) { 
                        if($(this).get(0).tagName == 'TITLE') { 
                            foundTitle = $(this).html();
                        } 
                    });
                    
                    $('title').html(foundTitle);
                }
            } catch (err) {
                panthera.tryParseJsonResponse(response);
            }
            
            if (input.messageBox == 'w2ui')
            {
                if (response.message != undefined)
                {
                    w2alert(response.message);
                }
            } else {
                // insert response to message boxes if enabled
                if (input.messageBox != undefined && input.messageBox != false && input.dataType == 'json')
                {
                    if (panthera.debug == true)
                    {
                        console.log("messageBox="+input.messageBox);
                        console.log("#"+input.messageBox+"_success = "+$('#'+input.messageBox+'_success'));
                        console.log("#"+input.messageBox+"_failed = "+$('#'+input.messageBox+'_failed'));
                        console.log(response);
                    }
                    
                    if (typeof defaultMessageHandler == 'undefined')
                        defaultMessageHandler = '';
                    
                    if (!document.getElementById(defaultMessageHandler) && defaultMessageHandler)
                    {
                        defaultMessageHandler(response);
                    } else {
                        // show response status message
                        messageBoxShow(input.messageBox, response);
                    }
                }
            }

            // use callback if defined      
            if (typeof input.success === "function")
            {
                if (panthera.debug == true)
                    console.log("Calling back "+input.success);
                    
                input.success(response);
            } else if (input.success.substring(0, 1) == "#") {
            
                if (input.dataType == 'json')
                    setHTMLValue(input.success, response.message);
                else
                    setHTMLValue(input.success, response);
            }
            
            // stop built-in ajaxLoaderInit spinner
            if (input.spinner != undefined)
            {
                if (getConstructorName(input.spinner) == "ajaxLoaderInit")
                {
                    if (panthera.debug == true)
                        console.log("Removing ajaxLoaderInit spinner");
                
                    if (input.dataType == 'json')
                    {
                        if (response.status == "success")
                            input.spinner.stop();
                        else
                            input.spinner.error();
                    } else {
                        input.spinner.stop();
                    }
                } else if (input.spinner == "nprogress") {
                    NProgress.done();
                }
            }
            
            if (response.status != "success" && input.dataType == "json")
            {
                // untoggle checkbox
                if (input.checkbox != undefined)
                {
                    panthera.toggleCheckbox(input.checkbox);
                }
            }
            
            if (input.dataType == 'json')
            {
                if (response.appendHTML)
                {
                    if ($('#_panthera_appendHTML').length)
                    {
                        $('#_panthera_appendHTML').html(response.appendHTML);
                    } else {
                        $('html').append('<div id="_panthera_appendHTML">' +response.appendHTML+'</div>');
                    }
                }
            }
            
            return response;
      },
      
    error: function () {
        // stop built-in ajaxLoaderInit spinner
        if (input.spinner != undefined)
        {
            if (getConstructorName(input.spinner) == "ajaxLoaderInit")
            {
                if (panthera.debug == true)
                    console.log("Removing ajaxLoaderInit spinner");
                
                input.spinner.error();
            }
        }
        
        // untoggle checkbox
        if (input.checkbox != undefined)
            panthera.toggleCheckbox(input.checkbox);
      
    },
      
      dataType: input.dataType
    };
    
    if (panthera.debug == true)
    {
        console.log('jQuery.ajax options:');
        console.log(options);
    }
    
    try {
        $.ajax(options);
    } catch (err) {
        panthera.logging.output('Error reading ajax response: '+err.message);
        console.log(err);
    }
}

/**
 * Make a POST request with HTML response
 *
 * @param json data {url, data, success, messageBox}
 * @see http://stackoverflow.com/questions/3710204/how-to-check-if-a-string-is-a-valid-json-string-in-javascript-without-using-try
 * @return bool 
 * @author Gumbo
 */

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

/**
 * Try to parse json response, if its json try to display message
 *
 * @param json data {url, data, success, messageBox}
 * @see http://stackoverflow.com/questions/3710204/how-to-check-if-a-string-is-a-valid-json-string-in-javascript-without-using-try
 * @return bool 
 * @author Gumbo
 */

panthera.tryParseJsonResponse = function (response) {
    if (IsJsonString(response))
    {
        response = JSON.parse(response);
        
        if (response.message)
        {
            panthera.logging.output(response.message);
            w2alert(response.message);
        }
    }
    
}

/**
  * Make a POST request with HTML response
  *
  * @param json data {url, data, success, messageBox}
  * @return mixed 
  * @author Damian Kęska
  */

panthera.htmlPOST = function (input) {
    input.dataType = "html";
    return panthera.jsonPOST(input);
}

/**
  * Make GET request with json response
  *
  * @param json data {url, data, success, messageBox}
  * @return mixed 
  * @author Damian Kęska
  */

panthera.jsonGET = function (input) {
    input.method = "GET";
    return panthera.jsonPOST(input);
}

/**
  * Make GET request with html response
  *
  * @param json data {url, data, success, messageBox}
  * @return mixed 
  * @author Damian Kęska
  */

panthera.htmlGET = function (input) {
    input.method = "GET";
    input.dataType = "html";
    return panthera.jsonPOST(input);
}

panthera.toggleCheckbox = function (fn) {

    if (typeof fn == "string")
        fn = $(fn);

    fn.attr('checked', !fn.is(':checked'));
    
    return true;
}

panthera.logging = function () {}
panthera.logging.output = function (msg, msgType) {

    if (panthera.debug == true)
    {
        console.log(msg);
    }

}

/**
  * Simple spinner overlay
  *
  * @param object el Container
  * @param array options Options
  * @see http://www.aplusdesign.com.au/blog/jquery-ajax-loader-spinner/
  * @return object 
  * @author Simon
  */

panthera.ajaxLoader = function (el, options) {
	// Becomes this.options
	var defaults = {
		bgColor 		: 'rgb(253, 254, 255)',
		duration		: 400,
		opacity			: 0.7,
		classOveride 	: false
	}
	
	this.options 	= jQuery.extend(defaults, options);
	this.container 	= $(el);
	this.finished = false;
	
	/*this.container.resize(function () {
		    $('.ajax_overlay').css({'width':container.width(), 'height':container.height()});
    });*/
	
	/**
	  * Constructor
	  *
	  * @return object 
	  * @author Simon
	  */
	
	this.ajaxLoaderInit = function() {
		var container = this.container;
		
		// Delete any other loaders
		this.stop();
		// Create the overlay
		var overlay = $('<div></div>').css({
				'background-color': this.options.bgColor,
				'opacity':this.options.opacity,
				'width':container.width(),
				'height':container.height(),
				'position':'absolute',
				'top':'0px',
				'left':'0px',
				'z-index':99999
		}).addClass('ajax_overlay');
		// add an overiding class name to set new loader style
		if (this.options.classOveride) {
			overlay.addClass(this.options.classOveride);
		}
		// insert overlay and loader into DOM
		container.append(
			overlay.append(
				$('<div></div>').addClass('ajax_loader')
			).fadeIn(this.options.duration, function () { $('.ajax_overlay').css({'width':container.width(), 'height':container.height()}); })
		);
    };
    
    this.update = function () {
        if (this.finished == false)
            $('.ajax_overlay').css({'width':this.container.width(), 'height':this.container.height()});
        
        return !this.finished;
    }
    
    /**
      * Stop the spinner
      *
      * @param bool error Set to true if any error occured
      * @return mixed 
      * @author Damian Kęska, Simon
      */
    
	this.stop = function(error){
		var overlay = this.container.children(".ajax_overlay");
		if (overlay.length) {
		     //$('.ajax_overlay').css({'width':container.width(), 'height':container.height()});
		
		    if (error != undefined)
		        overlay.css({'background-color': 'rgb(255, 247, 247)'});
		    /*else
		        overlay.css({'background-color': 'rgb(247, 255, 251)'});*/
		
			overlay.fadeOut(this.options.classOveride, function() {
				overlay.remove();
				this.finished = true;
			});
		}
	}
	
	/**
	  * Simple shortcut to method stop(True) - will stop spinner with error
	  *
	  * @return void 
	  * @author Damian Kęska
	  */
	
	this.error = function(){
		this.stop(true);
	}
}

/**
  * File multiupload area
  *
  * @param json input {id, callback, start}
  * @return mixed 
  * @author Damian Kęska
  */

panthera.multiuploadArea = function (input) {
    jQuery.event.props.push('dataTransfer');
    
    d = function(e) {
        var files = e.dataTransfer.files;
        
        // start event
        if (input.start != undefined)
            input.start();
        
        $.each(files, function(index, file) {
            var fileReader = new FileReader();
            var fileName = file;
            
            fileReader.onload = (function(file) {
                // upload a single file
                input.callback(this.result, fileName.name, (index+1), files.length, files);
            });
            
            fileReader.readAsDataURL(file);
            
            if (panthera.debug == true)
                console.log("Uploading: "+(index+1)+" of "+files.length);
            
            // finished
            //if (index == (files.length-1))
                //getUploadsPage('page=0');
        });
        
        // prevent default action
        return false;
    }
    
    splitted = input.id.split(",");
    
    console.log(splitted);
    
    for (k in splitted)
    {
        if (!$(splitted[k].trim()).length)
        {
            console.log('multiuploadArea: Skipping non-existent drop area');
            continue;
        }
        
        if (panthera.debug == true)
            console.log("multiuploadArea: Adding drop event to "+splitted[k].trim())
            
        $(splitted[k].trim()).bind('drop', d);
    }
}


/**
  * Make an interactive input with timeout on no activity
  *
  * @param json input {element, callback, interval}
  * @return mixed 
  * @author Damian Kęska
  */

panthera.inputTimeout = function (input) {
    var typingTimer; 

    // a little default
    if (input.interval == undefined)
        input.interval = 1200;
    
    // make a jQuery object from string    
    if (typeof input.element === "string")
        input.element = $(input.element);
        
    // select elements
    if (input.element.prop('tagName') == 'SELECT')
    {
        input.element.change(function () {
            typingTimer = setTimeout(input.callback, input.interval);
        });
        
        input.element.mouseover(function () {
            clearTimeout(typingTimer);
        });
    } else {
        // input text, textarea elements
        input.element.keyup(function(){
            typingTimer = setTimeout(input.callback, input.interval);
        });
        
        input.element.keydown(function(){
            clearTimeout(typingTimer);
        });
    }
}

/**
  * Panthera forms - useful set of input/forms functions
  * @author Damian Kęska
  */

panthera.forms = function () { }

/**
  * Show or hide layer on checkbox change
  *
  * @param string checkbox id or class
  * @param string layer id or class
  * @return void
  * @author Damian Kęska
  */

panthera.forms.checkboxToggleLayer = function (input) {
    $(input.input).change(function () {
    
        if (input.reversed == true)
        {
            a = "hide";
            b = "show";

        } else {
            a = "show";
            b = "hide";
        }
        
         if ($(input.input).is(':checked'))
            eval('$(input.layer).'+a+'();');
         else
            eval('$(input.layer).'+b+'();');
    });

}

/**
  * Bind ajax event to form submit
  *
  * @param json input Params passed to panthera.jsonPOST + destination (eg. #testForm)
  * @return bool 
  * @author Damian Kęska
  */

panthera.forms.ajaxSubmit = function (input) {
    if (input.destination == undefined)
    {
        panthera.logging.output('Invalid destination selected in panthera.forms.ajaxSubmit item');
        return false;
    }
    
    input.data = input.destination;
    
    
    $(input.destination).submit(function () {
        panthera.jsonPOST(input);
        return false;
    });
    
    return true;
}


/**
  * Get object's class name
  *
  * @param object obj
  * @return string 
  * @see http://stackoverflow.com/questions/1249531/how-to-get-a-javascript-objects-class
  * @author http://stackoverflow.com/users/325418/
  */

function getConstructorName(obj) {
    if (obj && obj.constructor && obj.constructor.toString) {
        var arr = obj.constructor.toString().match(
            /function\s*(\w+)/);

        if (arr && arr.length == 2) {
            return arr[1];
        }
        
        var arr = obj.constructor.toString().match(
            /\s*(\w+) = function/);
            
        if (arr && arr.length == 2) {
            return arr[1];
        }
    }

    return undefined;
}

/**
  * Convert rgb to hex
  *
  * @param int r Red
  * @param int g Green
  * @param int b Blue
  * @return string 
  * @author cwolves <http://stackoverflow.com/questions/5623838/rgb-to-hex-and-hex-to-rgb>
  */

function rgbToHex(r, g, b) {
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

/**
  * Show messagebox
  *
  * @param string messageBox Messagebox id's prefix
  * @param json response Response from server
  * @return mixed 
  * @author Damian Kęska
  */

function messageBoxShow(messageBox, response)
{
    if (response.status == 'success')
    {
        if ($('#'+messageBox+'_failed') != undefined)    
            $('#'+messageBox+'_failed').hide();
                    
        if ($('#'+messageBox+'_success') != undefined && response.message != undefined)
        {
            if (response.message != undefined)
                $('#'+messageBox+'_success').html(response.message);
                        
            $('#'+messageBox+'_success').slideDown();
        }
    } else {
        if ($('#'+messageBox+'_success') != undefined)
            $('#'+messageBox+'_success').slideUp();
                    
        if ($('#'+messageBox+'_failed') != undefined)
        {
            $('#'+messageBox+'_failed').html(response.message);
            $('#'+messageBox+'_failed').slideDown();
        }
    }
}

/**
 * Include a javascript file
 *
 * @param string jsFile Path to javascript file
 * @param string callback to load
 * @return bool 
 * @author hagenburger <https://gist.github.com/hagenburger/500716>
 */

panthera.include = function (src, callback, async, id) {
    if (panthera.debug == true)
        console.log('include '+src);
        
    var script = document.createElement('script'), loaded;
        
    script.type = 'text/javascript';
    script.src = src;
    script.async = false;
    
    if (id != undefined && id != '')
        script.id = id;
    
    if (callback && callback != '') {
      script.onreadystatechange = script.onload = function() {
        if (!loaded) {
          callback();
        }
        loaded = true;
      };
    }
    
    return document.getElementsByTagName('head').item(0).appendChild(script);
}

/**
 * Wrapper for Google maps
 *
 * @author Damian Kęska
 */

panthera.googleMap = function (init, key) {
    this.markers = [];
    this.circles = {};
    
    params = "";

    if (key != undefined && key)
        params = "&key="+key;

    // include Google maps API library if not included
    if (typeof google === 'undefined')
    {
        panthera.include('https://maps.googleapis.com/maps/api/js?sensor=false'+params+'&callback='+init);
        return false;
    }
       
    if (typeof google.maps === 'undefined')
    {
        panthera.include('https://maps.googleapis.com/maps/api/js?sensor=false'+params+'&callback='+init);
        return false;
    }
    
    this.initialize();
}

/**
 * Place marker on map
 * 
 * @param float x Latitude
 * @param float y Logitude
 * @param string icon Marker icon
 * @param bool visible Hide or show marker by default?
 * @param array data (Optional) Additional data
 * @param callable clickEvent Function that will be called on marker click
 * @return int Marker array index in this.markers array
 */

panthera.googleMap.prototype.addMarker = function (x, y, icon, visible, data, clickEvent) {
    
    mapObject = this.map;
    
    if (visible !== undefined && !visible)
        mapObject = null;
        
    options = {
        map: mapObject,
        position: new google.maps.LatLng(x, y)
    }
    
    if (icon)
        options.icon = icon;
    
    marker = {
        'object': new google.maps.Marker(options),
    }
    
    if (data)
        marker = array_merge(marker, data);
        
    panthera.logging.output('Marker created:');
    panthera.logging.output(marker);
    this.markers.push(marker);
    
    if (clickEvent)
    {
        panthera.logging.output('-> Adding click callback:');
        panthera.logging.output(clickEvent);
        google.maps.event.addListener(this.markers[(this.markers.length-1)].object, 'click', clickEvent);
    }
    
    return (this.markers.length-1);
}

/**
 * Filter markers by distance (in meters)
 * 
 * @param float distance Distance in meters
 * @param float x Latitude (optional, will be map center if not specified)
 * @param float y Logitude (optional, will be map center if not specified)
 * @return int Count of affected markers
 */

panthera.googleMap.prototype.filterMarkersByDistance = function (distance, x, y)
{
    if (!x){x = this.map.getCenter().k}
    if (!y){y = this.map.getCenter().B}
    
    var t = this;
    
    // disable all markers
    //this.toggleMarkers(false);
    
    // enable only matching markers
    return this.toggleMarkers(true, function (marker) {
        pos = marker.object.getPosition()

        //panthera.logging.output('Diffirence: '+t.calculateDistanceOnMap(pos.k, pos.B, x, y)+', distance: '+distance);
        
        if (t.calculateDistanceOnMap(pos.k, pos.B, x, y) > distance)
            return false;
        
        return true;
    }, true);
}

/*panthera.googleMap.prototype.drawCircle = function (name, x, y, color, meters) {
    if (!color) {color = '#AA0000';}
    
    new google.maps.Circle({
        map: this.map,
        radius: meters,    // 10 miles in metres
        fillColor: color
    });
}*/

/**
 * Toogle markers (show/hide). Optional callback can help filtering markers by it's fields.
 * 
 * @param bool enable Enable or disable markers
 * @param callable callback Filtering function that gets marker array as argument and returning bool
 * @return int Matched items
 */

panthera.googleMap.prototype.toggleMarkers = function (enable, callback, invertOthers, searchOnlyInActiveMarkers)
{
    if (enable)
        enable = this.map;
    else
        enable = null;
        
    var i=0;
    
    for (marker in this.markers)
    {
        if (searchOnlyInActiveMarkers && !this.markers[marker].object.getMap())
            continue;
        
        if (callback)
        {
            if (callback(this.markers[marker]))
            {
                this.markers[marker].object.setMap(enable);
                i++;
                continue;
            }
        } else {
            this.markers[marker].object.setMap(enable);
            i++;
            continue;
        }
        
        if (invertOthers)
        {
            if (enable)
                this.markers[marker].object.setMap(null);
            else
                this.markers[marker].object.setMap(this.map);
        }
    }
    
    return i;
}

/**
 * Calculate distance between x1, y1 and x2, y2 and return result in meters
 * 
 * @param int x1 Latitude 1 (object #1)
 * @param int y1 Longitude 1 (object #1)
 * @param int x2 Latitude 2 (object #2)
 * @param int y2 Longitude 2 (object #2)
 * @return int
 */

panthera.googleMap.prototype.calculateDistanceOnMap = function (x1, y1, x2, y2) 
{
    var R = 6378137;
    var dLat = rad(x2 - x1);
    var dLong = rad(y2 - y1);
    var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(rad(x1)) * Math.cos(rad(x2)) *
            Math.sin(dLong / 2) * Math.sin(dLong / 2);
            
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

/**
 * Initialize a Google Map
 * 
 * @param float x Latitude
 * @param float y Logitude
 * @param int zoom Zoom
 * @param const mapType Map type (default: google.maps.MapTypeId.ROADMAP)
 * @return array
 */

panthera.googleMap.prototype.initialize = function (x, y, zoom, mapType, customOptions) {
    this.currentBounds = '';
    this.map = '';
    
    // defaults
    if (!x) { x=1; }
    if (!y) { y=1; }
    if (!zoom) {zoom=10;}
    if (!mapType) {mapType=google.maps.MapTypeId.ROADMAP;}
    
    this.mapOptions = {
           zoom: zoom,
           mapTypeId: mapType,
           center: new google.maps.LatLng(x, y)
    };
    
    if (customOptions)
        this.mapOptions = array_merge(this.mapOptions, customOptions);
        
    panthera.logging.output('Set Google Map options:');
    panthera.logging.output(this.mapOptions);
    
    return this.mapOptions;
}

/**
  * Place a map
  *
  * @param string id HTML container to place map to
  * @return void 
  * @author Damian Kęska
  */

panthera.googleMap.prototype.createMap = function (id, mapOptions) {
    if (mapOptions != undefined)
        this.mapOptions = mapOptions
        
    this.clickToPoint = true;
    this.clickMarker = '';
        
    panthera.logging.output('Placing map on object id='+id);
    panthera.logging.output(this.mapOptions);

    this.map = new google.maps.Map(document.getElementById(id),this.mapOptions);
    
    panthera.logging.output('Creating Google Maps object');
    panthera.logging.output(this.map);
    
    if (this.clickToPoint)
        this.enableClickToPoint();
}

/**
 * Enable click to point on map
 * 
 * @return null
 * @author Damian Kęska
 */

panthera.googleMap.prototype.enableClickToPoint = function () {
    window.gmap = this;
    google.maps.event.addListener(this.map, 'click', this.clickToPointEvent);
}

/**
 * Handle click to point event
 * 
 * @param event Event
 * @return null
 * @author Damian Kęska
 */

panthera.googleMap.prototype.clickToPointEvent = function (location) {
    if (window.gmap.clickMarker)
    {
        gmap.clickMarker.setMap(null);
        delete gmap.clickMarker;
    }
    
    window.gmap.clickMarker = new google.maps.Marker({
        position: location.latLng, 
        map: window.gmap.map
    });
    
    window.gmap.map.panTo(window.gmap.clickMarker.getPosition());
}

/**
 * Search a location by name
 *
 * @param string location name
 * @return void 
 * @author Damian Kęska
 */
  
panthera.googleMap.prototype.getLocation = function (location)
{
    panthera.logging.output('Location query: '+location);
    
    if (geocoder == undefined)
    {
        try {
            var geocoder = new google.maps.Geocoder();
        } catch (Exception) { 
            panthera.logging.output('Cannot initialize geocoder, propably Google Maps were not initialized correctly. Check if your domain is registered in Google APIs.');
            return false;
        }
    }
    
    // this is neccesary to work, because "this" variable will be replaced in above code with geocode event
    var _map = this.map;
    
    // get places localisations
    geocoder.geocode( {'address': location}, function(results, status) {
           if (status == google.maps.GeocoderStatus.OK) {
                  var searchLoc = results[0].geometry.location;
                  var lat = results[0].geometry.location.lat();
                  var lng = results[0].geometry.location.lng();
                  var latlng = new google.maps.LatLng(lat, lng);
                  var bounds = results[0].geometry.bounds;
                  currentBounds = bounds;
                  
                  try {
                    _map.fitBounds(bounds);
                    return true;
                  } catch (Exception) {
                    console.log('Cannot set location to bounds:');
                    console.log(bounds);
                    return false;
                  }
            }
    });
}

/**
 * Get HTML response
 *
 * @param string name
 * @return mixed 
 * @author Damian Kęska
 */

/*panthera.htmlGET = function (input) {

    if (input.url == undefined)
    {
        console.log('panthera.htmlGET::URL cannot be empty');
        return false;
    }
    
    // by default the async will be off
    if (typeof input.async !== "boolean")
        input.async = false;
    
    if (input.data == undefined)
        input.data = "";
        
    if (input.dataType == undefined)
        input.dataType = "html";
        
    if (input.success == undefined)
        input.success = "";
        
    if (panthera.debug == true)
    {
        console.log("GET "+input.url);
        console.log(input.data);
        console.log("On success: "+input.success);
    }
    
    $.ajax( {
        url: input.url,
        data: input.data,
        async: input.async,
        success: function (response) { 
            // if the callback is a function
            if (typeof input.success === "function") {
                input.success(response);
            // or if its a HTML element
            } else if (input.success.substring(0, 1) == "#") {
                setHTMLValue(input.success, response);
            } else {
                // just return the response
                return response;
            }
        },
        dataType: input.dataType
        }
    );
}*/

/**
 * Check if object is in drop range of other
 *
 * @param string object id or class
 * @param object|string dragging object
 * @return bool 
 * @author Damian Kęska
 */

panthera.inDropRange = function (destination, object, event) {

    position = $(destination).position();

    if (object == 'cursor')
    {
        // before y range
        if (event.pageY < position.top)
            return false;
            
        // out y range
        if (event.pageY > position.top+$(destination).height())
            return false;
            
        // before x range
        if (event.pageX < position.left)
            return false;
            
        // out of x range
        if (event.pageX > position.left+$(destination).width())
            return false;
            
        // in drop range
        return true;
        
    } else {
        if (typeof object === "object")
            object = $(object);

        objectPosition = object.position();
        
        // before y range
        if (objectPosition.top < position.top && (objectPosition.top+object.height()) < position.top)
            return false;
            
        // out y range
        if (objectPosition.top > position.top+$(destination).height() && (objectPosition.top+object.height()) > position.top+$(destination).height())
            return false;
            
        // before x range
        if (objectPosition.left < position.left && (objectPosition.left+object.width()) < position.left)
            return false;
            
        // out of x range
        if (objectPosition.left > position.left+$(destination).width() && (objectPosition.left+object.width()) > position.left+$(destination).width())
            return false;
            
        // in drop range
        return true;
    }
}

/**
  * Simple hooking class
  *
  * @author Damian Kęska
  */

panthera.hooks = function () {};
panthera.hooks.list = {};

/**
  * Define a hook and place and assign a function
  *
  * @param string name
  * @param function f
  * @author Damian Kęska
  */

panthera.hooks.add = function (name, f) {
    if (!panthera.hooks.list[name])
    {
        panthera.hooks.list[name] = new Array();
    }
    
    panthera.hooks.list[name].push(f);
}

/**
  * Execute list of assigned hooks
  *
  * @param string name
  * @param mixed data
  * @return mixed 
  * @author Damian Kęska
  */

panthera.hooks.execute = function (name, data) {

    if (!panthera.hooks.list[name])
    {
        return data;
    }

    for (hook in panthera.hooks.list[name])
    {
        data = panthera.hooks.list[name][hook](data);
    }
    
    return data;
} 

/**
  * Cookies support for Panthera Framework Javascript library
  *
  * @author Damian Kęska
  */

panthera.cookies = function () {}

/**
  * Set a cookie
  *
  * @param string cookieName
  * @param string cookieValue 
  * @param int nDays
  * @author Damian Kęska
  */

panthera.cookies.set = function (cookieName,cookieValue,nDays) {
    var today = new Date();
    var expire = new Date();
    if (nDays==null || nDays==0) nDays=1;
    expire.setTime(today.getTime() + 3600000*24*nDays);
    document.cookie = cookieName+"="+escape(cookieValue)+ ";expires="+expire.toGMTString();
    panthera.logging.output("Setting cookie: "+cookieName+"="+escape(cookieValue)+ ";expires="+expire.toGMTString());
}

/**
  * Get a cookie
  *
  * @param string name
  * @author Damian Kęska
  */

panthera.cookies.get = function (name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	
	return null;
}

/**
  * Remove a cookie
  *
  * @param string name
  * @author Damian Kęska
  */

panthera.cookies.remove = function (name) {
    return panthera.cookies.set(name,"",-1);
}

panthera.cookies.policy = function () {};

/**
  * Display a cookies policy noticement
  *
  * @param string force display the popup even if cookie with decision was already set
  * @author Damian Kęska
  */

panthera.cookies.policy.display = function (force) {
    if (!panthera.cookies.get('pantheraCookiesPolicy') && force == undefined)
        $('#pantheraCookiePolicyWindow').show();
}

/**
  * Close a cookies policy information window
  *
  * @param int cookieTime Optional expiration time on cookie that handles user decision
  * @author Damian Kęska
  */

panthera.cookies.policy.close = function (cookieTime) {
    if (cookieTime == undefined)
        cookieTime = 365; // 365 days

    panthera.cookies.set('pantheraCookiesPolicy', true, cookieTime);
    $('#pantheraCookiePolicyWindow').hide();
}

panthera.locale = function () {};
panthera.locale.data = new Array();

/**
  * Add new translations to array
  *
  * @author Damian Kęska
  */

panthera.locale.add = function (input) {
	if (typeof input == "string")
	{
		panthera.logging.output("Got base64 encoded locales list");
		input = JSON.parse(Base64.decode(input));
	}
	
    panthera.locale.data = $.merge(input, panthera.locale.data);
}

/**
  * Get translated string
  *
  * @author Damian Kęska
  */

panthera.locale.get = function (string) {
    if (panthera.locale.data[string])
        return panthera.locale.data[string];
        
    if (panthera.debug)
		panthera.logging.output('[locale] Missing: "'+string+'"');
    
    return string;
}

/**
  * Check if input is a number (In PHP it would be is_numeric)
  *
  * @param mixed n
  * @return bool 
  * @author Damian Kęska
  */

function is_numeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

/**
  * Make first letter uppercase
  *
  * @param string inputString
  * @author meagar
  * @see http://stackoverflow.com/questions/1026069/capitalize-the-first-letter-of-string-in-javascript
  * @return string 
  * @author Damian Kęska
  */

function ucfirst(inputString)
{
    return inputString.charAt(0).toUpperCase() + inputString.slice(1);
}

/**
  * Determinate the input type and set value
  *
  * @param string id Element id
  * @param string value Value to set
  * @return void 
  * @author Damian Kęska
  */

function setHTMLValue(id, value)
{
    if (typeof $(id).attr('value') !== 'undefined' && attr !== false)
        $(id).val(value);
    else
        $(id).html(value);
}

/**
  * hackish implementation of the php 'var_dump()' in javascript
  *
  * @param object obj
  * @return string
  * @author dzone <http://www.dzone.com/snippets/vardump-javascript>
  */

function var_dump(obj) {
   if(typeof obj == "object") {
      return "Type: "+typeof(obj)+((obj.constructor) ? "\nConstructor: "+obj.constructor : "")+"\nValue: " + obj;
   } else {
      return "Type: "+typeof(obj)+"\nValue: "+obj;
   }
}//end function var_dump


function htmlspecialchars_decode (string, quote_style) {
  // http://kevin.vanzonneveld.net
  // +   original by: Mirek Slugen
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Mateusz "loonquawl" Zalega
  // +      input by: ReverseSyntax
  // +      input by: Slawomir Kaniecki
  // +      input by: Scott Cariss
  // +      input by: Francois
  // +   bugfixed by: Onno Marsman
  // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Ratheous
  // +      input by: Mailfaker (http://www.weedem.fr/)
  // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
  // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
  // *     returns 1: '<p>this -> &quot;</p>'
  // *     example 2: htmlspecialchars_decode("&amp;quot;");
  // *     returns 2: '&quot;'
  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  return string;
}

/**
  * Managing popups
  *
  * @author Damian Kęska
  */
  
var lastPopupParams = new Array();
var popupOpen = false;
  
panthera.popup = function () { }

/**
  * Create a popup
  *
  * @param string link
  * @param int|string width Use "last" value to keep last settings
  * @param int height
  * @return bool 
  * @author Damian Kęska
  */

panthera.popup.create = function (link, width, height) {

    if (lastPopupParams.length > 0 && (width == 'last' || width == '' || width == undefined))
    {
        width = lastPopupParams[0];
        height = lastPopupParams[1];
    }

    if(isNaN(width))
        width = 960;
    
    if(isNaN(height))
        height = 450;

    lastPopupParams = new Array();
    lastPopupParams.push(width);
    lastPopupParams.push(height);

    panthera.htmlGET({ url: link, success: function(data) {
			$().w2popup({ body: data, width: width, height: height });
		}
	});

    return true;
}

/**
  * Navigate to page keeping last popup settings
  *
  * @param string link
  * @return bool 
  * @author Damian Kęska
  */

panthera.popup.navigate = function (link) {
    return panthera.popup.create(link, 'last');
}

panthera.popup.close = function () {
    $().w2popup('close');
}

Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
}
 
Storage.prototype.getObject = function(key) {
    return JSON.parse(this.getItem(key));
}

function createPopup(link, width, height)
{
    return panthera.popup.create(link, width, height);
}

function closePopup()
{
    panthera.popup.close();
}

function parseUrl(url) {
	var a = document.createElement('a');
	a.href = url;
	return a;
}

if ( typeof console === "undefined" || typeof console.log === "undefined") {
	console = {};
	console.log = function() {
	};
}

jQuery.expr[':'].regex = function(elem, index, match) {
	var matchParams = match[3].split(','), validLabels = /^(data|css):/, attr = {
		method : matchParams[0].match(validLabels) ? matchParams[0].split(':')[0] : 'attr',
		property : matchParams.shift().replace(validLabels, '')
	}, regexFlags = 'ig', regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g, ''), regexFlags);
	return regex.test(jQuery(elem)[attr.method](attr.property));
}
function updateAdressBar(link) {
	window.history.pushState("", "", link);
}

function noticeMsg(type, message)
{
    if (type == "success")
    {
        jQuery('#msg_error').hide();
        jQuery('#msg_success').html(message);
        jQuery('#msg_success').slideDown();
        setTimeout('jQuery("#msg_success").slideUp();', 5000);
    } else {
        jQuery('#msg_success').hide();
        jQuery('#msg_error').html(message);
        jQuery('#msg_error').slideDown();
    }
}

var currentTab = "#content";
var tabsData = new Array();

function unloadMCE() {
	var editorArr = tinymce.editors, l = editorArr.length, i;

	if (l) {
		for ( i = l - 1; i >= 0; i--) {
			if (editorArr[i] !== undefined) {
				editorArr[i].remove();
			}
		}
	}
}

function tabPrepareContent(tab, link) {
    if (panthera.ajaxSlide == true)
        $("#container-main").animate({ marginTop: "-=10000px",}, 600 );
        
    if (window.tinymce)
	    unloadMCE();
	
	if(typeof onAjaxUnload == 'function')
	    onAjaxUnload();
	    
	panthera.hooks.execute('tabPrepareContent', link);
	//if(currentTab != tab)
	//{
	//    if(
	//}

	updateAdressBar(link);

	/*if (currentTab != tab)
	{
	if(tabsData[tab] != undefined)
	return false;
	}*/

	//url = parseUrl(link)
	panthera.htmlGET({ url: link, success: function(data) {
			$(tab).html(data);
			$('#menuLayer').height($('#container-main').height());
			
			if (panthera.ajaxSlide == true)
			    window.setTimeout('$("#container-main").animate({ marginTop: "+=60px",}, 1000 );', 1000);
		}
	});
}

function navigateTo(link) {
	currentTab = '#ajax_content';
	console.log(link);
	panthera.popup.close();
	return tabPrepareContent(currentTab, link);
}

function selectTab(tab, a) {
	/*tabs = jQuery('div:regex(id, atab-.*)')

	 for (var i=0;i<tabs.length;i++)
	 {
	 if(tabs[i].id == tab)
	 {
	 jQuery('#'+tabs[i].id).show()
	 tabPrepareContent(tab, a.href);
	 } else
	 jQuery('#'+tabs[i].id).hide()
	 }*/
	tabPrepareContent(tab, a.href);
	currentTab = tab;
}

(function($){
	$.fn.styleddropdown = function(callback){
		return this.each(function(){
			var obj = $(this)
			obj.find('.field').click(function() { //onclick event, 'list' fadein
			obj.find('.list').fadeIn(400);
			
			$(document).keyup(function(event) { //keypress event, fadeout on 'escape'
				if(event.keyCode == 27) {
				obj.find('.list').fadeOut(400);
				}
			});
			
			obj.find('.list').hover(function(){ },
				function(){
					$(this).fadeOut(400);
				});
			});
			
			obj.find('.list li').click(function() { //onclick event, change field value with selected 'list' item and fadeout 'list'
			
			field = obj.find('.field')
			
			if (field.length > 0)
			{
		        field.val($(this).html())
				    .css({
					    'background':'#fff',
					    'color':'#333'
				    });
			}
			
			if (typeof callback !== undefined)
			{
			    callback($(this).html());
			}
			
			obj.find('.list').fadeOut(400);
			});
		});
	};
})(jQuery);

/**
  * Mouse hold action
  *
  * @example $("div").bind('mouseheld', function(e) { console.log('Held', e); })
  * @see http://jsfiddle.net/gnarf/pZ6BM/
  * @author gnarf
  */

(function($) {
    function startTrigger(e,data) {
        var $elem = $(this);
        $elem.data('mouseheld_timeout', setTimeout(function() {
            $elem.trigger('mouseheld');
        }, e.data));
    }
    
    function stopTrigger() {
        var $elem = $(this);
        clearTimeout($elem.data('mouseheld_timeout'));
    }


    var mouseheld = $.event.special.mouseheld = {
        setup: function(data) {
            var $this = $(this);
            $this.bind('mousedown', +data || mouseheld.time, startTrigger);
            $this.bind('mouseleave mouseup', stopTrigger);
        },
        teardown: function() {
            var $this = $(this);
            $this.unbind('mousedown', startTrigger);
            $this.unbind('mouseleave mouseup', stopTrigger);
        },
        time: 750 // default to 750ms
    };
})(jQuery);

/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/
 
var Base64 = {
 
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
 
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = Base64._utf8_encode(input);
 
		while (i < input.length) {
 
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
 
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
 
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
 
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
 
		}
 
		return output;
	},
 
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
		while (i < input.length) {
 
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
 
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
 
			output = output + String.fromCharCode(chr1);
 
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
 
		}
 
		output = Base64._utf8_decode(output);
 
		return output;
 
	},
 
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	},
 
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
 
		while ( i < utftext.length ) {
 
			c = utftext.charCodeAt(i);
 
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
 
		}
 
		return string;
	}
}

// LZW-compress a string
function lzw_encode(s) {
    var dict = {};
    var data = (s + "").split("");
    var out = [];
    var currChar;
    var phrase = data[0];
    var code = 256;
    for (var i=1; i<data.length; i++) {
        currChar=data[i];
        if (dict[phrase + currChar] != null) {
            phrase += currChar;
        }
        else {
            out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
            dict[phrase + currChar] = code;
            code++;
            phrase=currChar;
        }
    }
    out.push(phrase.length > 1 ? dict[phrase] : phrase.charCodeAt(0));
    for (var i=0; i<out.length; i++) {
        out[i] = String.fromCharCode(out[i]);
    }
    return out.join("");
}

// Decompress an LZW-encoded string
function lzw_decode(s) {
    var dict = {};
    var data = (s + "").split("");
    var currChar = data[0];
    var oldPhrase = currChar;
    var out = [currChar];
    var code = 256;
    var phrase;
    for (var i=1; i<data.length; i++) {
        var currCode = data[i].charCodeAt(0);
        if (currCode < 256) {
            phrase = data[i];
        }
        else {
           phrase = dict[currCode] ? dict[currCode] : (oldPhrase + currChar);
        }
        out.push(phrase);
        currChar = phrase.charAt(0);
        dict[code] = oldPhrase + currChar;
        code++;
        oldPhrase = phrase;
    }
    return out.join("");
}

function array_merge() {
  //  discuss at: http://phpjs.org/functions/array_merge/
  // original by: Brett Zamir (http://brett-zamir.me)
  // bugfixed by: Nate
  // bugfixed by: Brett Zamir (http://brett-zamir.me)
  //    input by: josh
  //   example 1: arr1 = {"color": "red", 0: 2, 1: 4}
  //   example 1: arr2 = {0: "a", 1: "b", "color": "green", "shape": "trapezoid", 2: 4}
  //   example 1: array_merge(arr1, arr2)
  //   returns 1: {"color": "green", 0: 2, 1: 4, 2: "a", 3: "b", "shape": "trapezoid", 4: 4}
  //   example 2: arr1 = []
  //   example 2: arr2 = {1: "data"}
  //   example 2: array_merge(arr1, arr2)
  //   returns 2: {0: "data"}

  var args = Array.prototype.slice.call(arguments),
    argl = args.length,
    arg,
    retObj = {},
    k = '',
    argil = 0,
    j = 0,
    i = 0,
    ct = 0,
    toStr = Object.prototype.toString,
    retArr = true;

  for (i = 0; i < argl; i++) {
    if (toStr.call(args[i]) !== '[object Array]') {
      retArr = false;
      break;
    }
  }

  if (retArr) {
    retArr = [];
    for (i = 0; i < argl; i++) {
      retArr = retArr.concat(args[i]);
    }
    return retArr;
  }

  for (i = 0, ct = 0; i < argl; i++) {
    arg = args[i];
    if (toStr.call(arg) === '[object Array]') {
      for (j = 0, argil = arg.length; j < argil; j++) {
        retObj[ct++] = arg[j];
      }
    } else {
      for (k in arg) {
        if (arg.hasOwnProperty(k)) {
          if (parseInt(k, 10) + '' === k) {
            retObj[ct++] = arg[k];
          } else {
            retObj[k] = arg[k];
          }
        }
      }
    }
  }
  return retObj;
}
