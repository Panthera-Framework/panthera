/**
  * Confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox = function () {};
panthera.alertBox = function () {};

/**
  * Alias to panthera.confirmBox.create with isAlertBox argument set to True as default
  *
  * @param string text
  * @param function callback
  * @return mixed 
  * @author Damian Kęska
  */

panthera.alertBox.create = function (text, callback) {
    return panthera.confirmBox.create(text, callback, true);
}

/**
  * Create a confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox.create = function (text, callback, isAlertBox) {
    panthera.confirmBox.close();

    html = '<div id="popupQuestion" class="popupOverlay popupQuestionOverlay" style="z-index: 9999;">';
    html += '<div class="popupQuestion">';
    html += '<p class="popupHeader">'+text+'</p>';
    html += '<div class="separatorHorizontal"></div>';
    html += '<div style="margin: 20px;">';
    
    // alert box support
    if (isAlertBox)
    {
        html += '<input type="button" value="'+panthera.locale.get('Close')+'" style="float: right;" id="confirmBox_btn_close">';
    } else {
        html += '<input type="button" value="'+panthera.locale.get('No')+'" id="confirmBox_btn_no"> <input type="button" value="'+panthera.locale.get('Yes')+'" id="confirmBox_btn_yes" style="float: right;">';
    }
    
    html += '</div></div></div>';

    // append the code
    $('#titleBar').after(html);
    
    // now add callbacks
    if (isAlertBox)
    {
        // Button: Close
        $('#confirmBox_btn_close').click(function () {
            if (callback)
            {
                callback('Close');
            }
            
            panthera.confirmBox.close();
        });
        
    } else {
        // Button: No
        $('#confirmBox_btn_no').click(function () {
            if (callback)
            {
                callback('No');
            }
            
            panthera.confirmBox.close();
        });
        
        // Button: Yes
        $('#confirmBox_btn_yes').click(function () {
            if (callback)
            {
                callback('Yes');
            }
            
            panthera.confirmBox.close();
        });
    }
}

/**
  * Close a confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox.close = function () {
    $('#popupQuestion').remove();
}

panthera.alertBox.close = panthera.confirmBox.close;

/**
  * Compatibility with old w2ui interface
  *
  * @author Damian Kęska
  */

function w2confirm(msg, callback) { panthera.confirmBox.create(msg, callback) };

panthera.popup.slots = {};
panthera.popup.i = 0;

/**
  * Panthera UI popups
  *
  * @author Damian Kęska
  */

panthera.popup.create = function (link, slot) {
    if (slot && slot != '__default__' && !is_numeric(slot))
    {
        elementName = 'popupOverlay_'+slot;
    } else {
        slot = '__default__';
        elementName = 'popupOverlay';
    }

    panthera.logging.output('Creating popup with id='+elementName);

    if (!document.getElementById(elementName))
    {
        panthera.popup.i++;
        panthera.logging.output('Inserted #'+elementName+' right after #topContent');
        
        
        if (document.getElementById('topContent'))
        {
            $('#topContent').after('<div id="'+elementName+'" style="z-index: '+(100+panthera.popup.i)+';" class="popupOverlay"></div>');
        } else if (document.getElementById('ajax-content')) {
            $('#ajax-content').before('<div id="'+elementName+'" style="z-index: '+(100+panthera.popup.i)+';" class="popupOverlay"></div>');
        }
    }
    
    panthera.popup.restoreContent(slot);
    data = '';
    
    if (typeof link == "string") 
    {
        panthera.popup.slots[slot] = link;
        
        if (link.substr(0, 8) == 'element:')
        {
            panthera.logging.output('Getting content of popup source element, its '+link.replace('element:', ''));
            link = $(link.replace('element:', ''));
        }
    }

    // setting content by jQuery object (from content or value of other HTML tag)
    if (typeof link == "object")
    {
        panthera.popup.slots[slot] = 'element:#'+link.attr('id');
    
        if (link.val())
        {
            data = link.val();
            link.val('');
        } else {
            data = link.html();
            link.html('');
        }
        
    } 
    
    if (typeof link == "string")
    {
        panthera.logging.output('Got link: '+link);
    
        panthera.htmlGET({ url: link, success: function(data) {
                panthera.logging.output('Inserting ajax HTML result into #'+elementName);
                
		        $('#'+elementName).html(data);
			    $('#'+elementName).slideDown(200);
		    }
	    });
	    
	} else {
	    panthera.logging.output('Copying raw HTML code into #'+elementName);
	    $('#'+elementName).html(data);
        $('#'+elementName).slideDown(200);
	}

    return true;
}

/**
  * Close a popup
  *
  * @author Damian Kęska
  */

panthera.popup.close = function (slot) {
    if (slot && slot != '__default__')
    {
        elementName = 'popupOverlay_'+slot;
    } else {
        slot = '__default__';
        elementName = 'popupOverlay';
    }

    $('#'+elementName).slideUp(150);
    panthera.logging.output('Closing popup id=#'+elementName);
    panthera.popup.restoreContent(slot);
    panthera.popup.slots[slot] = false;
}

/**
  * Toggle popup opened/closed
  *
  * @author Damian Kęska
  */

panthera.popup.toggle = function (link, slot) {
    if (!slot)
    {
        slot = '__default__';
    }


    if (typeof link == "string") 
    {
        type = link;
    } else {
        type = $(link).attr('id');
    }
    
    console.log('Toggle Panthera popup, id:'+type+', previous id='+panthera.popup.slots[slot]);

    if (panthera.popup.slots[slot] && panthera.popup.slots[slot] == type)
    {
        panthera.popup.close(slot);
    } else {
        panthera.popup.create(link, slot);
    }
}

/**
  * Move popup's content back to original HTML tag
  *
  * @author Damian Kęska
  */

panthera.popup.restoreContent = function (slot) {
    if (slot && slot != '__default__')
    {
        elementName = 'popupOverlay_'+slot;
    } else {
        slot = '__default__';
        elementName = 'popupOverlay';
    }

    panthera.logging.output('Restoring content for id='+panthera.popup.slots[slot]+', slot='+slot);

    if (panthera.popup.slots[slot])
    {
        if (panthera.popup.slots[slot].substr(0, 8) == 'element:')
        {
            id = $(panthera.popup.slots[slot].replace('element:', ''));
            $(id).html($('#'+elementName).html());
            $('#'+elementName).html('');
            panthera.popup.slots[slot] = false;
        }
    }
}

/**
  * Backwards compatibility with old template that was using w2ui
  *
  * @param string message
  * @return void 
  * @author Damian Kęska
  */

function w2alert(message)
{
    return panthera.alertBox.create(message);
}

panthera.hooks.add('tabPrepareContent', panthera.popup.restoreContent);
panthera.defaultMessageHandler = 'w2ui';
