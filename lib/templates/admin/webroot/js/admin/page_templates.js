$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Copying static files to application root (template->webrootMerge)
  *
  * @author Damian Kęska
  */

function webrootMerge()
{
    loader = new panthera.ajaxLoader($('#webrootMergeGrid'));
    panthera.jsonPOST({ url: '?display=templates&action=webrootMerge', spinner: loader, async: true, success: function (response) {
        if (response.status == "success")
        {
            $('tr[id^=webroot_]').remove();
        
            i = 0;
            changed = 0;
            for (k in response.result)
            {
            
                if (response.result[i].status == true)
                {
                    $('#webrootMergeBody').append('<tr id="webroot_'+i+'"><td style="text-align: center; border-right: 0px;"><a href="#" onclick="navigateTo(\'?display=browsefile&path='+response.result[i].path+'&back_btn=P2Rpc3BsYXk9dGVtcGxhdGVz\')">'+response.result[i].chrootname+'<span class="tooltip">'+response.result[i].source+'</span></a></td></tr>');  
                    changed++;
                }
                    
                i++;          
                
                loader.update();
            }
            
            if (changed == 0)
                $('#webrootMergeBody').append('<tr id="webroot_0"><td style="text-align: center; border-right: 0px;">'+localizeUpToDate+'</td></tr>');  
                
           loader.update();
        }
    }});
}

/**
  * Set default site template
  *
  * @param string template Template name
  * @return void 
  * @author Damian Kęska
  */

function selectDefaultTemplate(template, caller)
{
    loader = new panthera.ajaxLoader($('#templatesList'));

    panthera.jsonPOST({ url: '?display=templates&action=setTemplate&template='+template, spinner: loader, async: true, success: function (response) {
            if (response.status == "success")
                selectTemplate();
            else {
                if (caller != undefined)
                {
                    $('#'+caller).attr('checked', false);
                }
            
            }
        }
    });
}

/**
  * Template selection and viewer
  *
  * @param string template Template name (optional)
  * @return void 
  * @author Damian Kęska
  */

function selectTemplate(template)
{
    if (template == undefined)
        template = '';

    loader = new panthera.ajaxLoader($('#templatesList'));
    panthera.jsonPOST({ url: '?display=templates&action=getTemplates&template='+template, spinner: loader, async: true, success: function (response) {
            if (response.status == "success")
            {
                console.log(response.result);
                $('tr[id^=templatesList_]').remove();
                
                i=0;
                for (k in response.result)
                {
                    // initialize variables
                    aAttributes = '';
                    additionalColumns = '';
                    
                    // selecting templates, viewing etc.
                    if (template == '')
                    {
                        aAttributes = ' onclick="selectTemplate(\''+k+'\');"';
                        checked = 'onclick="selectDefaultTemplate(\''+k+'\', \'checkb_'+i+'\')"';
                        
                        if (response.current == k)
                            checked = 'checked';
                        
                        additionalColumns = '<td style="padding: 10px; border-right: 0px; width: 1%;"><input type="checkbox" '+checked+' id="checkb_'+i+'"></td>';
                        
                    } else {
                        aAttributes = ' onclick="navigateTo(\'?display=browsefile&path='+response.result[k].item+'&back_btn=P2Rpc3BsYXk9dGVtcGxhdGVz\')"';
                    }
                
                    // append new templates to list
                    $('#templateListBody').append('<tr id="templatesList_'+i+'">'+additionalColumns+'<td style="width: 60px; padding: 10px; border-right: 0px;">'+response.result[k].place+'</td><td style="border-right: 0px; width: 350px;"><a href="#"'+aAttributes+'>'+k+'<span class="tooltip">'+response.result[k].item+'</span></a><a href="#" style="float: right;" onclick="templateTool(\'validate\', \''+k+'\');"><img src="images/admin/validate-icon.gif" style="width: 20px;"></a></td></tr>');  
                    i++;
                    
                    loader.update();
                }
                
                if (template != '')
                    $('#templateListBody').append('<tr id="templatesList_back"><td style="width: 60px; padding: 10px; border-right: 0px; text-align: center;" colspan="2"><a href="#" onclick="selectTemplate();">'+localizeBack+'</a></td></tr>');  
                    
                loader.update();
            }
        }
    });
}

/** CONFIGURATION **/

function template_cache_lifetime_save()
{
    panthera.jsonPOST({ url: '?display=templates&action=exec&name=template_cache_lifetime&value='+$('#template_cache_lifetime').val(), spinner: configLoader, async: true});
}

function template_cache_lifetime_select(value)
{
    $('#template_cache_lifetime').val(value);
    template_cache_lifetime_save();
}


$(document).ready(function () {

    configLoader = new panthera.ajaxLoader($('#templatesConfig'));
    
    /**
      * template_caching checkbox field
      *
      * @author Damian Kęska
      */


    $('#template_caching').change(function () {
        // show or hide div
        if($(this).is(':checked')){
            $('#template_cache_lifetime_tr').show('slow');
            value = 'true';
        } else {
            $('#template_cache_lifetime_tr').hide('slow');
            value = 'false';
        }
        
        panthera.jsonPOST({ url: '?display=templates&action=exec&name=template_caching&value='+value, spinner: configLoader, async: true, checkbox: '#template_caching'});
    });
    
    
    /**
      * template_debugging checkbox field
      *
      * @author Damian Kęska
      */
    
    $('#template_debugging').change(function () {
        panthera.jsonPOST({ url: '?display=templates&action=exec&name=template_debugging&value='+$(this).is(':checked'), spinner: configLoader, async: true, checkbox: '#template_debugging'});
    });
    
    /**
      * template_cache_lifetime number field
      *
      * @author Damian Kęska
      */
    
    panthera.inputTimeout({ element: '#template_cache_lifetime', interval: 1200, callback: template_cache_lifetime_save });
});

toolsLoader = new panthera.ajaxLoader($('#templatesTools'));

/**
  * Execute a tool
  *
  * @param string toolName
  * @return void
  * @author Damian Kęska
  */

function templateTool(toolName, value)
{
    if (typeof value === "object")
        value = value.val()
        
    panthera.jsonPOST({ url: '?display=templates&action=exec&name='+toolName+'&value='+value, spinner: toolsLoader, messageBox: 'messageBox', async: true});
}
