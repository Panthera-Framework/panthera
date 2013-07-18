<script type="text/javascript">
var localizeBack = "{function="localize('Back')"}";
var localizeUpToDate = "{function="localize('All files are up to date with system templates!', 'templates')"}";
</script>
<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/page_templates.js"></script>

<style>
#container {
    background: url("images/admin/menu/Icon-template.png") no-repeat transparent;
    background-size: 150px;
    background-position: 82% 85%;
}
</style>

   <div class="titlebar">{function="localize('Templates management', 'templates')"}{include="_navigation_panel.tpl"}</div>
   
        <div class="msgError" id="messageBox_failed"></div>
        <div class="msgSuccess" id="messageBox_success"></div>
   
        <!-- webrootMerge -->
   
        <div class="grid-2" id="webrootMergeGrid" style="position: relative;">
          <div class="title-grid">{function="localize('Webroot templates', 'templates')"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td><small><i><b>{function="ucfirst(localize('note', 'templates'))"}:</b> {function="localize('If you updated site or admin template or Panthera libs you should update static files', 'templates')"}</i></small></td>
                    </tr>
                </tfoot>
            
                <tbody id="webrootMergeBody">
                    <tr>
                        {$here = localize("here", "templates")}
                        {$btn = "<a href='#' onclick='webrootMerge();'><b>$here</b></a>"}
                        <td style="text-align: center; border-right: 0px;">{function="slocalize('To update static javascripts, images and css files from templates click %s', 'templates', $btn)"}.</td>
                    </tr>
                </tbody>
            </table>
         </div>
       </div>
       
       <!-- templates viewer -->
       
       <div class="grid-2" id="templatesList" style="position: relative;">
          <div class="title-grid">{function="localize('Site templates viewer', 'templates')"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{function="localize('Here are listed all templates, including its files', 'templates')"}</small></td>
                    </tr>
                </tfoot>
            
                <tbody id="templateListBody">
                    {loop="$templates_list"}
                    <tr id="templatesList_{$key}">
                        <td style="padding: 10px; border-right: 0px; width: 1%;"><input type="checkbox" id="checkb_{$k}" {if="$key == $current_template"}checked{else}onclick='selectDefaultTemplate('{$key}', 'checkb_{$key}')'{/if}></td>
                        <td style="width: 60px; padding: 10px; border-right: 0px;">{$value.place}</td><td style="border-right: 0px;"><a href="#" onclick="selectTemplate('{$key}');">{$key}</td>
                    </tr>
                    {/loop}
                </tbody>
            </table>
         </div>
       </div>
       
       <!-- configuration -->
       
       <div style="float: left; width: 100%;">
       <div class="grid-2" id="templatesConfig" style="position: relative; float: left; margin-right: 20px;">
          <div class="title-grid">{function="ucfirst(localize('configuration', 'templates'))"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
            
                <tbody id="templatesConfigBody">
                    <tr>
                        <td style="width: 100px;">{function="ucfirst(localize('cache', 'templates'))"}:</td><td style="border-right: 0px;"><input type="checkbox" id="template_caching" {if="$config.template_caching == True"}checked{/if}></td>
                    </tr>
                    <tr id="template_cache_lifetime_tr" {if="$config.template_caching == False"}style="display: none;"{/if}>
                        <td>{function="ucfirst(localize('cache lifetime', 'templates'))"}:</td><td style="border-right: 0px;"><input type="text" value="{$config.template_cache_lifetime}" id="template_cache_lifetime"> <input type="button" value="{function="localize('Select', 'templates')"}" onclick="createPopup('_ajax.php?display=_popup_time&type=countSeconds&popup=true&callback=template_cache_lifetime_select', 1024, 'upload_popup');"></td>
                    </tr>
                    <tr>
                        <td style="border-bottom: 0px;">{function="ucfirst(localize('debugging', 'templates'))"}: </td><td style="border-bottom: 0px; border-right: 0px;"><input type="checkbox"{if="$config.template_debugging == True"} checked {/if}id="template_debugging"></td>
                    </tr>
                </tbody>
            </table>
         </div>
       </div>
       </div>
       
       <!-- options -->
       
       <div style="float: left; width: 100%;">
       <div class="grid-2" id="templatesTools" style="position: relative; float: left; margin-right: 20px;">
          <div class="title-grid">{function="ucfirst(localize('tools', 'templates'))"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
            
                <tbody id="templatesConfigBody">
                    <tr>
                        <td style="border-right: 0px;"><a href="#clear-templates-cache" onclick="templateTool('clear_cache');">{function="localize('Clear templates cache', 'templates')"}</a></td>
                    </tr>
                    
                    <tr>
                        <td style="border-bottom: 0px; border-right: 0px;"><input type="text" id="template_validate_input"> <input type="button" value="{function="localize('validate', 'templates', 'ucfirst')"}" onclick="templateTool('validate', $('#template_validate_input'));"></td>
                    </tr>
                </tbody>
            </table>
         </div>
       </div>
       </div>
