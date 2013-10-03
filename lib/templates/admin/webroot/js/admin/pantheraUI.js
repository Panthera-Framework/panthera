panthera.popup.create = function (link) {
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

panthera.popup.close = function () {
    $('#popupOverlay').slideUp(150);
    panthera.logging.output('Closing popup');
    panthera.popup.restoreContent();
    popupOpen = false;
}

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
