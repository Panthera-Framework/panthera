<script type="text/javascript">
var localizeBack = "{function="localize('Back')"}";
var localizeUpToDate = "{function="localize('All files are up to date with system templates!', 'templates')"}";

$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Copying static files to application root (template->webrootMerge)
  *
  * @author Damian Kęska
  */

function webrootMerge()
{
    loader = new panthera.ajaxLoader($('#templatesList'));
    panthera.jsonPOST({ url: '?display=templates&cat=admin&action=webrootMerge', async: true, success: function (response) {
        if (response.status == "success")
        {
            $('tr[id^=webroot_]').remove();
        
            i = 0;
            changed = 0;
            for (k in response.result)
            {
            
                if (response.result[i].status == true)
                {
                    $('#webrootMergeBody').append('<tr id="webroot_'+i+'"><td style="text-align: center; border-right: 0px;"><a href="#" onclick="navigateTo(\'?display=browsefile&cat=admin&path='+response.result[i].path+'&back_btn=P2Rpc3BsYXk9dGVtcGxhdGVz\')">'+response.result[i].chrootname+'<span class="tooltip">'+response.result[i].source+'</span></a></td></tr>');  
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
    panthera.jsonPOST({ url: '?display=templates&action=setTemplate&cat=admin&template='+template, async: true, success: function (response) {
            if (response.status != "success")
            {
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
    panthera.jsonPOST({ url: '?display=templates&action=getTemplates&cat=admin&template='+template, spinner: loader, async: true, success: function (response) {
            if (response.status == "success")
            {
                $('tr[id^=templatesList_]').remove();
                
                i=0;
                for (k in response.result)
                {
                    // initialize variables
                    aAttributes = '';
                    additionalColumns = '';
                    validateIcon = '';
                    
                    // selecting templates, viewing etc.
                    if (template == '')
                    {
                        aAttributes = ' onclick="selectTemplate(\''+k+'\');"';
                        checked = 'onclick="selectDefaultTemplate(\''+k+'\', \'checkb_'+i+'\')"';
                        
                        if (response.current == k)
                            checked = 'checked';
                        
                        additionalColumns = '<td style="padding: 10px; width: 1%;"><input type="checkbox" '+checked+' id="checkb_'+i+'"></td>';
                        
                    } else {
                        aAttributes = ' onclick="navigateTo(\'?display=browsefile&cat=admin&path='+response.result[k].item+'&back_btn=P2Rpc3BsYXk9dGVtcGxhdGVz\')"';
                        validateIcon = '<a href="#" style="float: right;" onclick="templateTool(\'validate\', \''+k+'\');"><img src="images/admin/validate-icon.gif" style="width: 20px;"></a>';
                    }
                
                    // append new templates to list
                    $('#templateListBody').append('<tr id="templatesList_'+i+'">'+additionalColumns+'<td style="width: 60px; padding: 10px;">'+response.result[k].place+'</td><td style="width: 350px;"><a href="#"'+aAttributes+'>'+k+'</a>'+validateIcon+'</td></tr>');  
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
    panthera.jsonPOST({ url: '?display=templates&action=exec&cat=admin&name=template_cache_lifetime&value='+$('#template_cache_lifetime').val(), spinner: configLoader, async: true});
}

function template_cache_lifetime_select(value)
{
    $('#template_cache_lifetime').val(value);
    template_cache_lifetime_save();
}


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
        
    panthera.jsonPOST({ url: '?display=templates&cat=admin&action=exec&name='+toolName+'&value='+value, messageBox: 'w2ui', async: true});
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Clear templates cache', 'templates')"}" onclick="templateTool('clear_cache')">
        <input type="button" value="{function="ucfirst(localize('configuration', 'templates'))"}" onclick="panthera.popup.toggle('element:#configurationPopup')">
        <input type="button" value="{function="localize('Webroot update', 'templates')"}" onclick="panthera.popup.toggle('element:#webrootUpdatePopup'); webrootMerge();">
    </div>
</div>

<!-- Configuration -->
<div id="configurationPopup" style="display: none;">
    <div style="text-align: center;">
    <table style="display: inline-block; margin: 0 auto;">
        <thead>
            <th colspan="3">{function="ucfirst(localize('configuration', 'templates'))"}</th>
        </thead>
        
        <tbody id="templatesConfigBody">
            <tr>
                <td style="width: 100px;">{function="ucfirst(localize('cache', 'templates'))"}:</td>
                <td><input type="checkbox" id="template_caching" {if="$config.template_caching == True"}checked{/if}></td>
            </tr>
            
            <tr id="template_cache_lifetime_tr" {if="$config.template_caching == False"}style="display: none;"{/if}>
                <td>{function="ucfirst(localize('cache lifetime', 'templates'))"}:</td>
                <td><input type="text" value="{$config.template_cache_lifetime}" id="template_cache_lifetime"> <input type="button" value="{function="localize('Select', 'templates')"}" onclick="panthera.popup.toggle('?display=_popup_time&cat=admin&type=countSeconds&popup=true&callback=template_cache_lifetime_select', 'secondpopup');"></td>
            </tr>
            
            <tr>
                <td>{function="ucfirst(localize('debugging', 'templates'))"}: </td>
                <td><input type="checkbox"{if="$config.template_debugging == True"} checked {/if}id="template_debugging"></td>
            </tr>
        </tbody>
    </table>
    
    <script type="text/javascript">
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
        
        panthera.jsonPOST({ url: '?display=templates&action=exec&cat=admin&name=template_caching&value='+value, async: true, checkbox: '#template_caching'});
    });
    
    
    /**
      * template_debugging checkbox field
      *
      * @author Damian Kęska
      */
    
    $('#template_debugging').change(function () {
        panthera.jsonPOST({ url: '?display=templates&action=exec&cat=admin&name=template_debugging&value='+$(this).is(':checked'), async: true, checkbox: '#template_debugging'});
    });
    
    /**
      * template_cache_lifetime number field
      *
      * @author Damian Kęska
      */
    
    panthera.inputTimeout({ element: '#template_cache_lifetime', interval: 1200, callback: template_cache_lifetime_save });
    </script>
    </div>
</div>

<!-- Configuration -->
<div id="webrootUpdatePopup" style="display: none;">
    <div style="text-align: center; margin: 30px;">
    <table style="display: inline-block; margin: 0 auto;">
        <thead>
            <th>{function="localize('Webroot templates', 'templates')"}</th>
        </thead>
        <tbody id="webrootMergeBody">
            <tr>
                {$here = localize("here", "templates")}
                {$btn = "<a href='#' onclick='webrootMerge();'><b>$here</b></a>"}
                <td>{function="slocalize('To update static javascripts, images and css files from templates click %s', 'templates', $btn)"}.</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">
        <!-- templates viewer -->
    <div style="display: inline-block;">
        <table style="width: 100%;">
            <thead>
                <th colspan="4">{function="localize('Site templates viewer', 'templates')"}</th>
            </thead>
            
            <tbody id="templateListBody">
              {loop="$templates_list"}
                 <tr id="templatesList_{$key}">
                     <td style="padding: 10px; border-right: 0px; width: 1%;">
                        <input type="checkbox" class="checkb_all" id="checkb_{$key}" {if="$key == $current_template"}checked{else}onclick="$('.checkb_all').attr('checked', false); $(this).attr('checked', true); selectDefaultTemplate('{$key}', 'checkb_{$key}')"{/if}>
                     </td>
                     <td style="width: 60px; padding: 10px; border-right: 0px;"><img src='{$PANTHERA_URL}/images/admin/pantheraUI/template-thumbnail.png' width="120px" height="78px"></td>
                     <!-- <td style="width: 60px; padding: 10px; border-right: 0px;"><img src='' width="120px" height="78px"></td> -->
                     <td style="width: 60px; padding: 10px; border-right: 0px;">{$value.place}</td>
                     <td><a href="#" onclick="selectTemplate('{$key}');">{$key}</td>
                 </tr>
              {/loop}
            </tbody>
       </table>
       
       <div class="underTableDescription">
         <small>{function="localize('Select a template which you want to be displayed on your website front page', 'templates')"}.</small>
       </div>
   </div>
</div>
 
