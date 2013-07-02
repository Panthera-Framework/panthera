/**
  * Panthera main class
  *
  * @author Damian Kęska
  */

function panthera(){}

panthera.debug = true;
panthera.ajaxSlide = false;

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
    {
        input.async = false;
    }
    
    if (input.contentType == undefined)
    {
        input.contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
    }
    
    // run panthera built-in ajaxLoaderInit spinner
    if (input.spinner != undefined)
    {
        if (getConstructorName(input.spinner) == "ajaxLoaderInit")
        {
            if (panthera.debug == true)
                console.log("Using ajaxLoaderInit spinner");
        
            input.spinner.ajaxLoaderInit();
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
            if ($(input.data))
            {
                formID = input.data;
                input.data = $(formID).serialize();
                
                if (input.isUploading == true)
                {
                    input.data = new FormData(document.getElementById(input.data.substring(1, input.data.length)));
                    input.processData = false;
                    input.contentType = false;
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
    {
        console.log(input.method+" "+input.url);
        console.log(input.data);
    }
    
    // so, lets just use jQuery with our data to make a request
    $.ajax({
    
      // custom progressbar
      xhr: function() {
            var xhr = jQuery.ajaxSettings.xhr();
            
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
    
      type: input.method,
      url: input.url,
      data: input.data,
      async: input.async,
      processData: input.processData,
      contentType: input.contentType,
      
      success: function (response) {
            if (panthera.debug == true)
                console.log("Response: success"); 
            
      
            // insert response to message boxes if enabled
            if (input.messageBox != undefined && input.dataType == 'json')
            {
                if (panthera.debug == true)
                {
                    console.log("messageBox="+input.messageBox);
                    console.log("#"+input.messageBox+"_success = "+$('#'+input.messageBox+'_success'));
                    console.log("#"+input.messageBox+"_failed = "+$('#'+input.messageBox+'_failed'));
                    console.log(response);
                }
                
                // show response status message
                messageBoxShow(input.messageBox, response);
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
                
                    if (response.status == "success")
                        input.spinner.stop();
                    else
                        input.spinner.error();
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
        {
            panthera.toggleCheckbox(input.checkbox);
        }
      
    },
      
      dataType: input.dataType
    });
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
    
    for (k in splitted)
    {
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
    
    input.element.keyup(function(){
        typingTimer = setTimeout(input.callback, input.interval);
    });
    
    input.element.keydown(function(){
        clearTimeout(typingTimer);
    });
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

function createPopup(link, width, height)
{
    if(isNaN(width))
        width = 960;
    
    if(isNaN(height))
        height = 450;


    panthera.htmlGET({ url: link, success: function(data) {
			$().w2popup({ body: data, width: width, height: height });
		}
	});

    return false;
}

function closePopup()
{
    $().w2popup('close');
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

function showAclWindow() {
	box = new $.popupBox({
			//onAjaxed: function(){ alert('Loading complete') },
			type: 'ajax', 
			url: '_ajax.php?display=acl&action=popup_table&uid=1'
	});
	
	box.show();
}

function tabPrepareContent(tab, link) {
    if (panthera.ajaxSlide == true)
        $("#container-main").animate({ marginTop: "-=10000px",}, 600 );
        
	unloadMCE();
	
	if(typeof onAjaxUnload == 'function')
	    onAjaxUnload();
	//if(currentTab != tab)
	//{
	//    if(
	//}

	updateAdressBar(link);

	// jeśli karta już została otwarta a klikamy na nią z innej karty to tylko na nią przełączamy bez odświeżania zawartości
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

	// zapiszemy do cache jaki link jest aktualnie w karcie otwarty
	tabsData[tab] = link;
}

function navigateTo(link) {
	currentTab = '#ajax_content';
	console.log(link);
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
