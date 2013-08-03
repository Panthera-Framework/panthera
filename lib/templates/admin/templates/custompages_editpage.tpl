{$site_header}
{function="localizeDomain('cpages')"}
<script type="text/javascript">

/**
  * Delete tag from html code
  *
  * @author Mateusz Warzyński
  */

function deleteTag (htmlid)
{
    $('#'+htmlid).remove();
}


jQuery(document).ready(function($) {

    /**
      * Init MCE editor
      *
      * @author Mateusz Warzyński
      */

    function initEditor()
    {
        mceSetContent('page_content', htmlspecialchars_decode("{$custompage_html}"));
    }

    {$mce_init = "init_instance_callback: initEditor,"}
    {include="mce.tpl"}

    mceInit('page_content');
    
    /**
      * Submit editing page form
      *
      * @author Mateusz Warzyński
      */

    $('#save_form').submit(function () {
        panthera.jsonPOST({ data: '#save_form', messageBox: 'userinfoBox', mce: 'tinymce_all'});
        return false;
    });

    /**
      * Add tag to existing custom page
      *
      * @author Mateusz Warzyński
      */

    $('#new_tag').click(function () {
          tagID = Math.round((new Date()).getTime() / 1000);
          $('#tags').append('<tr id="tag_p_'+tagID+'"><td><input type="text" value="'+$('#new_tag_text').val()+'" name="tag_'+tagID+'"></td><td style="border-right: 0px;"><input type="button" value="Usuń" onclick="deleteTag(\'tag_p_'+tagID+'\'); return false;"></td></tr>');
    });

    /**
      * Change page title in html code
      *
      * @author Mateusz Warzyński
      */

    var spanClicked = false;
    jQuery('#page_title_editor').click(function () {
        if (spanClicked == true)
            return false;

        spanClicked = true;

        jQuery('#page_title_editor').html('<input type="text" name="content_title" value="{$custompage_title_escaped}">');
    });

});
</script>

    <div id="edit_window">
        <form action="{$AJAX_URL}?id={$custompage_unique}&lang={$custompage_language}&display=custom&cat=admin&action=post_form&pid={$custompage_id}" method="POST" id="save_form">
        <div class="titlebar">{function="localize('Editing page', 'custompages')"} - <span id="page_title_editor" style="color: black;">"{$custompage_title}"</span> ({$custompage_language}){include="_navigation_panel"}</div>

        <br>
         <div class="msgSuccess" id="userinfoBox_success"></div>
         <div class="msgError" id="userinfoBox_failed"></div>
         
         <!-- edit in other languages -->
         <div class="grid-1" id="languagesList" style="position: relative;">
          <div class="title-grid">{function="localize('This page in other languages', 'custompages')"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{function="localize('Select language from above list to edit or create new page in selected language', 'custompages')"}</small></td>
                    </tr>
                </tfoot>
            
                <tbody>
                    {loop="$languages"}
                        <tr>
                            <td style="padding: 10px; border-right: 0px; width: 1%;"><a href="#{$key}" onclick="navigateTo('?display=custom&cat=admin&action=edit_page&uid={$custompage_unique}&language={$key}');">{$key}</a></td>
                            <td style="width: 60px; padding: 10px; border-right: 0px;"></td>
                        </tr>
                    {/loop}
                </tbody>
            </table>
         </div>
       </div>
       <!-- end of edit in other languages -->
       
       <!-- tags -->
       <div class="grid-2" style="float: left; margin-bottom: 100px;" id="customTagsWindow">
            <div class="title-grid">{function="localize('Tags', 'custompages')"}<span></span></div>
            <div class="content-table-grid">
                <table class="insideGridTable">
                    <tfoot>
                       <tr>
                           <td colspan="2"><em>{function="localize('Short keywords describing page content, helpfully in search engines positioning.', 'custompages')"}</em></td>
                       </tr>
                    </tfoot>

                    <tbody id="tags">
                      {loop="$tag_list"}
                       <tr id="tag_p_{$key}">
                           <td style="width: 380px;"><input type="text" value="{$value}" name="tag_{$key}" style="width: 360px;"></td>
                           <td style="border-right: 0px;"><input type="button" value="{function="localize('Delete', 'messages')"}" onclick="deleteTag('tag_p_{$key}'); return false;"></td>
                       </tr>
                      {/loop}

                    </tbody>
                    
                    <tbody>
                       <tr>
                         <td style="width: 380px;"><input type="text" name="tag_new" id="new_tag_text" style="width: 360px;"></td>
                         <td style="border-right: 0px;"><input type="button" value="{function="localize('Add new tag', 'custompages')"}" id="new_tag"></td>
                       </tr>
                    </tbody>
               </table>
            </div>
        </div>
        <!-- end of tags -->
       
        <div class="grid-2" style="float: right; margin-right: 25px;" id="customOptionsWindow">
             <div class="title-grid">{function="localize('Options', 'custompages')"}<span></span></div>
             <div class="content-table-grid">
                <table class="insideGridTable">
                    <tbody>
                       <tr id="tr_for_all_languages">
                           <td>{function="localize('Set this page for all languages', 'custompages')"}:<br><small>{function="localize('Content of this static page will be visibile in all languages', 'custompages')"}</td>
                           <td style="border-right: 0px;"><input type="checkbox" name="for_all_languages" value="1" {if="$allPages == True}checked{/if"}></td>
                       </tr>
                       
                       <tr id="tr_save_language">
                         <td style="width: 150px;">{function="localize('Save this page in', 'custompages')"}:</td>
                         <td style="border-right: 0px;">
                             <select name="new_language">
                             {loop="$languages"}
                                 <option value="{$key}"{if="$key == $custompage_language"}selected{/if}>{$key}</option>   
                             {/loop}
                             </select>
                         </td>
                       </tr>
                       
                       <tr id="try_url_id">
                           <td style="width: 60%;">{function="localize('SEO name', 'custompages')"}:<br><small>{function="localize('Must be unique', 'custompages')"}, (A-Z, a-z, 0-9, -, _, ., ,, +, %)</small></td>
                           <td style="border-right: 0px;"><input type="text" name="url_id" value="{$custompage_url_id}" style="width: 99%;"></td>
                       </tr>
                       
                       <tr id="tr_permissions">
                           <td>{function="localize('Permissions', 'custompages')"}:</td>
                           <td style="border-right: 0px;"><input type="button" value="{function="localize('Manage permissions', 'messages')"}" id="permissionsButton" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_manage_custompage_{$custompage_id}', 1024, 'upload_popup');"></td>
                       </tr>
                       
                       <tr id="tr_menu">
                           <td>{function="localize('Menu')"}:</td>
                           <td style="border-right: 0px;"><input type="button" value="{function="localize('Add to menu', 'menuedit')"}" onclick="createPopup('_ajax.php?display=menuedit&cat=admin&popup=true&action=quickAddFromPopup&link={$custompage_url_id_address}&title={$custompage_title}&language={$custompage_language}', 1024, 550);"></td>
                       </tr>
                       
                       <tr id="tr_save">
                           <td style="border-bottom: 0px;"></td>
                           <td style="border-bottom: 0px; border-right: 0px;"><input type="submit" value="{function="localize('Save page', 'custompages')"}"></td>
                       </tr>
                    </tbody>
                </table>
             </div>
        
        </div>

        <!-- content -->
        <div class="grid-1" style="margin-top: 350px;">
        <div class="title-grid">{function="localize('Content', 'custompages')"}<span></span></div>
            <div class="content-gird" style="padding: 0px;">
               <textarea name="page_content_custom" id="page_content" style="width: 100%; height: 350px;"></textarea><br>
           </div>
        </div>
        <!-- end of content -->
        </form>
        
        <div class="grid-1">
            <div class="title-grid">{function="localize('Informations', 'custompages')"}<span></span></div>
            <div class="content-table-grid">
                 <table class="insideGridTable">
                    <tr id="tr_informations_id">
                        <td>id</td>
                        <td style="border-right: 0px;">{$custompage_id}</td>
                    </tr>
                    <tr id="tr_informations_unique">
                        <td>unique</td>
                        <td style="border-right: 0px;">{$custompage_unique}</td>
                    </tr>
                    
                    <tr id="tr_informations_language">
                        <td>language</td>
                        <td style="border-right: 0px;">{$custompage_language}</td>
                    </tr>
                    
                    <tr id="tr_informations_url">
                        <td>URL</td>
                        <td style="border-right: 0px;"><a href="{$custompage_url_id_address}" target="_blank">{$custompage_url_id_address}</a><br><a href="{$custompage_unique_address}" target="_blank">{$custompage_unique_address}</a><br><a href="{$custompage_id_address}" target="_blank">{$custompage_id_address}</a></td>
                    </tr>
                    
                    <tr id="tr_informations_url_id">
                        <td style="border-bottom: 0px;">url_id</td>
                        <td style="border-bottom: 0px; border-right: 0px;">{$custompage_url_id}</td>
                    </tr>
                 </table>
            </div>
        </div>
</div>
