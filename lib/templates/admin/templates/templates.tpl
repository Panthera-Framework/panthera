<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

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
                $('#webrootMergeBody').append('<tr id="webroot_0"><td style="text-align: center; border-right: 0px;">{"All files are up to date with system templates!"|localize:templates}</td></tr>');  
                
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
                    $('#templateListBody').append('<tr id="templatesList_back"><td style="width: 60px; padding: 10px; border-right: 0px; text-align: center;" colspan="2"><a href="#" onclick="selectTemplate();">{"Back"|localize}</a></td></tr>');  
                    
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

</script>

<style>
#container {
    background: url("images/admin/menu/Icon-template.png") no-repeat transparent;
    background-size: 150px;
    background-position: 82% 85%;
}
</style>

   <div class="titlebar">{"Templates management"|localize:templates}{include file="_navigation_panel.tpl"}</div>
   
        <div class="msgError" id="messageBox_failed"></div>
        <div class="msgSuccess" id="messageBox_success"></div>
   
        <!-- webrootMerge -->
   
        <div class="grid-2" id="webrootMergeGrid" style="position: relative;">
          <div class="title-grid">{"Webroot templates"|localize:templates}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td><small><i><b>{"note"|localize:templates|ucfirst}:</b> {"If you updated site or admin template or Panthera libs you should update static files"|localize:templates}</i></small></td>
                    </tr>
                </tfoot>
            
                <tbody id="webrootMergeBody">
                    <tr>
                        {$here = localize("here", "templates")}
                        {$btn = "<a href='#' onclick='webrootMerge();'><b>$here</b></a>"}
                        <td style="text-align: center; border-right: 0px;">{slocalize("To update static javascripts, images and css files from templates click %s", "templates", $btn)}.</td>
                    </tr>
                </tbody>
            </table>
         </div>
       </div>
       
       <!-- templates viewer -->
       
       <div class="grid-2" id="templatesList" style="position: relative;">
          <div class="title-grid">{"Site templates viewer"|localize:templates}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{"Here are listed all templates, including its files"|localize:templates}</small></td>
                    </tr>
                </tfoot>
            
                <tbody id="templateListBody">
                    {foreach from=$templates_list key=k item=i}
                    <tr id="templatesList_{$k}">
                        <td style="padding: 10px; border-right: 0px; width: 1%;"><input type="checkbox" id="checkb_{$k}" {if $k == $current_template}checked{else}onclick="selectDefaultTemplate('{$k}', 'checkb_{$k}')"{/if}></td>
                        <td style="width: 60px; padding: 10px; border-right: 0px;">{$i.place}</td><td style="border-right: 0px;"><a href="#" onclick="selectTemplate('{$k}');">{$k}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
         </div>
       </div>
       
       <!-- configuration -->
       
       <div style="float: left; width: 100%;">
       <div class="grid-2" id="templatesConfig" style="position: relative; float: left; margin-right: 20px;">
          <div class="title-grid">{"configuration"|localize:templates|ucfirst}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
            
                <tbody id="templatesConfigBody">
                    <tr>
                        <td style="width: 100px;">{"cache"|localize:templates|ucfirst}:</td><td style="border-right: 0px;"><input type="checkbox" id="template_caching" {if $config.template_caching == True}checked{/if}></td>
                    </tr>
                    <tr id="template_cache_lifetime_tr" {if $config.template_caching == False}style="display: none;"{/if}>
                        <td>{"cache lifetime"|localize:templates|ucfirst}:</td><td style="border-right: 0px;"><input type="text" value="{$config.template_cache_lifetime}" id="template_cache_lifetime"> <input type="button" value="{"Select"|localize:templates}" onclick="createPopup('_ajax.php?display=_popup_time&type=countSeconds&popup=true&callback=template_cache_lifetime_select', 1024, 'upload_popup');"></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0px;">{"debugging"|localize:templates|ucfirst}: </td><td style="border-bottom: 0px; border-right: 0px;"><input type="checkbox"{if $config.template_debugging == True} checked {/if}id="template_debugging"></td>
                    </tr>
                </tbody>
            </table>
         </div>
       </div>
       </div>
       
       <!-- options -->
       
       <div style="float: left; width: 100%;">
       <div class="grid-2" id="templatesTools" style="position: relative; float: left; margin-right: 20px;">
          <div class="title-grid">{"tools"|localize:templates|ucfirst}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
            
                <tbody id="templatesConfigBody">
                    <tr>
                        <td style="border-right: 0px;"><a href="#clear-templates-cache" onclick="templateTool('clear_cache');">{"Clear templates cache"|localize:templates}</a></td>
                    </tr>
                    
                    <tr>
                        <td style="border-bottom: 0px; border-right: 0px;"><input type="text" id="template_validate_input"> <input type="button" value="{"validate"|localize:templates:ucfirst}" onclick="templateTool('validate', $('#template_validate_input'));"></td>
                    </tr>
                </tbody>
            </table>
         </div>
       </div>
       </div>
