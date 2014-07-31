{include="upload.js.tpl"}

<style type="text/css">
.uploadTable thead tr th {
    font-size: 11px;
}
.uploadTable tbody tr {
    color: black;
}
</style>

<div id="header" style="display: block; width: 65%; margin: 0 auto; height: 35px; text-align: center; color: white;">
    <div style="display: inline-grid; float: left;">
    <p style="font-size: 12px; float: left; margin-top: 20px;"><b>{function="localize('Category', 'upload')"}:</b>&nbsp;
        <div class="select" style="margin-top: 14px; margin-left: 3px;">
         <select onChange="changeCategory();" id="upload_category">
           {loop="$categories"}
            <option value="{$value->name}" {if="$setCategory == $value->name"} selected {/if}>{$value->getName()}</option>
           {/loop}
         </select>
        </div>
    </p>
    </div>
    
    <div style="display: inline-grid;">
    <p style="font-size: 12px; float: left; margin-top: 20px; margin-left: 20px; word-break: break-all;"><b>{function="localize('File types', 'upload')"}:</b>&nbsp; 
    	{$category->mime_type}
    </p>
    </div>
    
    <div style="display: inline-grid;">
    <p style="font-size: 12px; float: left; margin-top: 20px; margin-left: 20px;"><b>{function="localize('Max file size', 'upload')"}:</b>&nbsp; 
    	{$category->getMaxfilesize(true)}
    </p>
    </div>
</div>

<div id="content">
   {if="$view_type == 'images'"}
    <div class="uploadBoxCentered">
    
     {if="count($files) < 1"}
        <p style="color: white; text-align: center;">{function="localize('There are no uploaded files', 'upload')"}.</p>
     {else}
      <div style="text-align: center;">
        {$i=0}
        {loop="$files"}
        {$i=$i+1}
                <div class="uploadBox" id="file_{$value.id}" rel="{$key}" style="background-color: #404C5A;" onclick="selectFile({$value.id});">
                  <div class="boxInner" style="position: relative;">
                        <div class="boxImg"><img src="{$value.icon}" id="item_img_{$key}" {if="$value.type == 'image'"} style="width: 100%;" {/if}></div>
                        <div class="titleBox"><b>{$value.object->getName()}</b></div>
                        
                        <input type="hidden" id="item_title_{$value.id}" value="{$value.object->getName()}">
                        <input type="hidden" id="item_delete_{$value.id}" value="{if="$value.ableToDelete == True"}1{else}0{/if}">
                        <input type="hidden" id="item_description_{$value.id}" value="{$value.description}">
                        <input type="hidden" id="item_id_{$value.id}" value="{$value.id}">
                        <input type="hidden" id="item_author_{$value.id}" value="{$value.author}">
                        <input type="hidden" id="item_mime_{$value.id}" value="{$value.mime}">
                        <input type="hidden" id="item_type_{$value.id}" value="{$value.type}">
                        <input type="hidden" id="item_link_{$value.id}" value="{$value.object->getLink()}">
                        <input type="hidden" id="item_directory_{$value.id}" value="{$value.directory}">
                  </div>
                </div>
        {/loop}
      </div>
     {/if}
    </div>
   
   {else}

    <div style="text-align: center; margin-top: 20px;">
     <table style="margin-top: 5px; margin-bottom: 30px; display: inline-table; width: 65.3%; margin-left: 29px;" class="uploadTable">
        <thead>
           <tr style="border: 1px solid #4d565c; border-bottom: 0;">
            <th>{function="localize('Icon', 'upload')"}</th>
            <th>{function="localize('Name', 'upload')"}</th>
            <th>{function="localize('Description', 'upload')"}</th>
            <th>{function="localize('Mime type', 'upload')"}</th>
            <th>{function="localize('Protected', 'upload')"}</th>
            <th colspan="2">{function="localize('Author', 'upload')"}</th>
           </tr>
        </thead>
       
        <tbody>
        
        {if="count($files) < 1"}
        
            <tr>
                <td colspan="6">{function="localize('There are no uploaded files', 'upload')"}.</td>
            </tr>
        
        {else}
        
            {loop="$files"}
              <tr id="file_{$value.id}" onclick="selectFile({$value.id});">
                
                <td style="padding-top: 4px; padding-right: 6px; padding-left: 6px; width: 30px;">
                    <img src="{$value.icon}" style="max-height: 30px; max-width: 30px;">
                </td>
                
                <td style="width: 200px;">{$value.object->getName()}</td>
                <td style="width: 200px;">{$value.description}</td>
                <td style="width: 80px;">{$value.mime}</td>
                <td style="width: 40px;">{if="$value.object->protected"}{function="localize('Yes')"}{else}{function="localize('No')"}{/if}</td>
                <td style="width: 120px;">{$value.object->getAuthor()}</td>
                <td style="width: 1px; padding-right: 5px; padding-left: 5px; z-index: 10;">
                    <a href="{$value.link}" target="_blank" download="{$value.name}" onclick="selectFile({$value.id});" id="download{$value.id}">
                        <img src="{$PANTHERA_URL}/images/admin/menu/downloads.png">
                    </a>
                </td>
                
                <script type="text/javascript">
                $("file_{$value.id}").bind('mouseheld', function(e) { $('download{$value.id}').click(); });
                </script>
                
                <input type="hidden" id="item_title_{$value.id}" value="{$value.name}">
                <input type="hidden" id="item_delete_{$value.id}" value="{if="$value.ableToDelete == True"}1{else}0{/if}">
                <input type="hidden" id="item_description_{$value.id}" value="{$value.description}">
                <input type="hidden" id="item_id_{$value.id}" value="{$value.id}">
                <input type="hidden" id="item_author_{$value.id}" value="{$value.author}">
                <input type="hidden" id="item_mime_{$value.id}" value="{$value.mime}">
                <input type="hidden" id="item_type_{$value.id}" value="{$value.type}">
                <input type="hidden" id="item_link_{$value.id}" value="{$value.link}">
                <input type="hidden" id="item_directory_{$value.id}" value="{$value.directory}">
                
              </tr>
            {/loop}
        {/if}            
        
        </tbody>
    </table>
   </div>
  {/if}
  
  
  <div style="width: 65%; margin: 0 auto; padding-bottom: 10px;">
    <div style="text-align: center; font-size: 12px; color: white; margin-bottom: -20px;">{$uiPagerName="adminUpload"}{include="ui.pager"}</div>
    <input type="button" value="{function="localize('Close')"}" style="float: right;" onclick="panthera.popup.close();">
    <input type="button" value="{function="localize('Change view', 'upload')"}" style="float: right;" onclick="panthera.popup.create('?{function="Tools::getQueryString('GET', 'changeView=' .$view_change, '_')"}')">

    <input type="text" id="file_name" style="display: none;">
    <input type="text" id="file_description" style="display: none;">
    <input type="text" id="file_author" style="display: none;">
    <input type="text" id="file_type" style="display: none;">
    <input type="text" id="file_mime" style="display: none;">
    <input type="text" id="file_link" style="display: none;">
    <input type="text" id="file_directory" style="display: none;">
    <input type="text" id="file_id" style="display: none;">
    <input type="text" id="file_k" style="display: none;">
    
    {if="$upload_files == True"}
        <input type='button' value="{function="localize('Add new file', 'upload')"}" style="margin-left: 5px; float: left;" onclick="panthera.popup.toggle('?{function="Tools::getQueryString('GET', 'action=popupUploadFileWindow', '_')"}')">
      {/if}
    {if="$callback"}
        <input type="button" value="{function="localize('Select this file', 'upload')"}" style="float: left; margin-left: 5px;" onclick="callBack();" id="_upl_select_file">
    {else}
      
      	{if="$isAdmin"}
        	{if="$seeOtherUsersUploads"}
            	<input type="button" style="float: left;" value="{function="localize('Hide other users files', 'upload')"}" onclick="panthera.popup.create('?{function="Tools::getQueryString('GET', 'otherUsers=false', '_')"}')">
                {else}
                <input type="button" style="float: left;" value="{function="localize('Show other users files', 'upload')"}" onclick="panthera.popup.create('?{function="Tools::getQueryString('GET', 'otherUsers=true', '_')"}')">
            {/if}
        {/if}
    {/if}
    <input type="button" value="{function="localize('Delete selected files', 'upload')"}" style="float: left; margin-left: 5px; display: none;" id="file_delete" onclick="deleteSelectedFiles();">
  </div>
 </div>
