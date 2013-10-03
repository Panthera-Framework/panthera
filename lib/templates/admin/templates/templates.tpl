<script type="text/javascript">
var localizeBack = "{function="localize('Back')"}";
var localizeUpToDate = "{function="localize('All files are up to date with system templates!', 'templates')"}";
</script>
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/page_templates.js"></script>

{include="ui.titlebar"}

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">

        <!-- configuration -->
        
       <table style="display: inline-block;">
           <thead>
               <th colspan="3">{function="ucfirst(localize('configuration', 'templates'))"}</th>
           </thead>
           
           <tbody id="templatesConfigBody">
                <tr>
                    <td style="width: 100px;">{function="ucfirst(localize('cache', 'templates'))"}:</td><td><input type="checkbox" id="template_caching" {if="$config.template_caching == True"}checked{/if}></td>
                </tr>
                <tr id="template_cache_lifetime_tr" {if="$config.template_caching == False"}style="display: none;"{/if}>
                    <td>{function="ucfirst(localize('cache lifetime', 'templates'))"}:</td><td><input type="text" value="{$config.template_cache_lifetime}" id="template_cache_lifetime"> <input type="button" value="{function="localize('Select', 'templates')"}" onclick="createPopup('_ajax.php?display=_popup_time&cat=admin&type=countSeconds&popup=true&callback=template_cache_lifetime_select', 1024, 'upload_popup');"></td>
                </tr>
                <tr>
                    <td>{function="ucfirst(localize('debugging', 'templates'))"}: </td><td><input type="checkbox"{if="$config.template_debugging == True"} checked {/if}id="template_debugging"></td>
                </tr>
           </tbody>
       </table>
       
       <!-- options -->
          
       <table style="display: inline-block;">
           <thead>
               <th colspan="2">{function="ucfirst(localize('tools', 'templates'))"}</th>
           </thead> 
           
           <tbody id="templatesConfigBody">
              <tr>
                 <td><a href="#clear-templates-cache" onclick="templateTool('clear_cache');">{function="localize('Clear templates cache', 'templates')"}</a></td>
              </tr>
                    
              <tr>
                 <td><input type="text" id="template_validate_input"> <input type="button" value="{function="localize('validate', 'templates', 'ucfirst')"}" onclick="templateTool('validate', $('#template_validate_input'));"></td>
              </tr>
           </tbody>
       </table><br><br>
        
        <!-- webroot merge -->
        
        <table style="display: inline-block;">
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

        <!-- templates viewer -->

        <table style="display: inline-block;">
            <thead>
                <th colspan="3">{function="localize('Site templates viewer', 'templates')"}</th>
            </thead>
            
            <tbody>
              {loop="$templates_list"}
                 <tr id="templatesList_{$key}">
                     <td><input type="checkbox" id="checkb_{$k}" {if="$key == $current_template"}checked{else}onclick='selectDefaultTemplate('{$key}', 'checkb_{$key}')'{/if}></td>
                     <td>{$value.place}</td><td><a href="#" onclick="selectTemplate('{$key}');">{$key}</td>
                 </tr>
              {/loop}
            </tbody>
       </table>
</div>