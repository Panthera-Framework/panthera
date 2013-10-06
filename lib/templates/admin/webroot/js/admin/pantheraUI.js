/**
  * Confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox = function () {};

/**
  * Create a confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox.create = function (text, callback) {
    panthera.confirmBox.close();

    html = '<div id="popupQuestion" class="popupOverlay popupQuestionOverlay">';
    html += '<div class="popupQuestion">';
    html += '<p class="popupHeader">'+text+'</p>';
    html += '<div class="separatorHorizontal"></div>';
    html += '<div style="margin: 20px;">';
    html += '<input type="button" value="'+panthera.locale.get('No')+'" style="float: left;" id="confirmBox_btn_no"> <input type="button" value="'+panthera.locale.get('Yes')+'" id="confirmBox_btn_yes" style="float: right;">';
    html += '</div></div></div>';

    // append the code
    $('#titleBar').after(html);
    
    // now add callbacks
    $('#confirmBox_btn_no').click(function () { callback('No'); panthera.confirmBox.close();});
    $('#confirmBox_btn_yes').click(function () { callback('Yes'); panthera.confirmBox.close();});
}

/**
  * Close a confirmation box
  *
  * @author Damian Kęska
  */

panthera.confirmBox.close = function () {
    $('#popupQuestion').remove();
}

/**
  * Compatibility with old w2ui interface
  *
  * @author Damian Kęska
  */

function w2confirm(msg, callback) { panthera.confirmBox.create(msg, callback) };

panthera.popup.slots = new Array();

/**
  * Panthera UI popups
  *
  * @author Damian Kęska
  */

panthera.popup.create = function (link, slot) {
    if (!document.getElementById('popupOverlay'))
    {
        $('#ajax-content').append('<div id="popupOverlay"></div>');
    }
    
    panthera.popup.restoreContent();
    data = '';
    
    if (typeof link == "string") 
    {
        popupOpen = link;
        
        if (link.substr(0, 8) == 'element:')
        {
            console.log('Getting content of popup source element, its '+link.replace('element:', ''));
            link = $(link.replace('element:', ''));
        }
    }

    // setting content by jQuery object (from content or value of other HTML tag)
    if (typeof link == "object")
    {
        popupOpen = 'element:#'+link.attr('id');
    
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
		        $('#popupOverlay').html(data);
			    $('#popupOverlay').slideDown(200);
		    }
	    });
	    
	} else {
	    $('#popupOverlay').html(data);
        $('#popupOverlay').slideDown(200);
	}

    return true;
}

/**
  * Close a popup
  *
  * @author Damian Kęska
  */

panthera.popup.close = function (slot) {
    $('#popupOverlay').slideUp(150);
    panthera.logging.output('Closing popup');
    panthera.popup.restoreContent();
    popupOpen = false;
}

/**
  * Toggle popup opened/closed
  *
  * @author Damian Kęska
  */

panthera.popup.toggle = function (link) {
    if (typeof link == "string") 
    {
        type = link;
    } else {
        type = $(link).attr('id');
    }
    
    console.log('Toggle Panthera popup, id:'+type+', previous id='+popupOpen);

    if (popupOpen && popupOpen == type)
    {
        panthera.popup.close();
    } else {
        panthera.popup.create(link);
    }
}

/**
  * Move popup's content back to original HTML tag
  *
  * @author Damian Kęska
  */

panthera.popup.restoreContent = function () {
    panthera.logging.output('Restoring content for popupOpen='+popupOpen);

    if (popupOpen)
    {
        if (popupOpen.substr(0, 8) == 'element:')
        {
            id = $(popupOpen.replace('element:', ''));
            $(id).html($('#popupOverlay').html());
            $('#popupOverlay').html('');
            popupOpen = false;
        }
    }
}

panthera.hooks.add('tabPrepareContent', panthera.popup.restoreContent);
