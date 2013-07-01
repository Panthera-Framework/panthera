{"cpages"|localizeDomain}
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
    {include file="mce.tpl"}

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
          $('#tags').append('<tr id="tag_p_'+tagID+'"><td><input type="text" value="'+$('#new_tag_text').val()+'" name="tag_'+tagID+'"></td><td><input type="button" value="Usuń" onclick="deleteTag(\'tag_p_'+tagID+'\'); return false;"></td></tr>');
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
        <form action="{$AJAX_URL}?id={$custompage_unique}&lang={$custompage_language}&display=custom&action=post_form&pid={$custompage_id}" method="POST" id="save_form">
        <div class="titlebar">{"Editing page"|localize:custompages} - <span id="page_title_editor" style="color: black;">"{$custompage_title}"</span> ({$custompage_language})</div>

        <br>
         <div class="msgSuccess" id="userinfoBox_success"></div>
         <div class="msgError" id="userinfoBox_failed"></div>
         
         <!-- edit in other languages -->
         <div class="grid-1" id="languagesList" style="position: relative;">
          <div class="title-grid">{"This page in other languages"|localize:custompages}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{"Select language from above list to edit or create new page in selected language"|localize:custompages}</small></td>
                    </tr>
                </tfoot>
            
                <tbody>
                    {foreach from=$languages key=k item=i}
                        <tr>
                            <td style="padding: 10px; border-right: 0px; width: 1%;"><a href="#{$k}" onclick="navigateTo('?display=custom&action=edit_page&uid={$custompage_unique}&language={$k}');">{$k}</a></td>
                            <td style="width: 60px; padding: 10px; border-right: 0px;"></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
         </div>
       </div>
       <!-- end of edit in other languages -->
       
       <!-- tags -->
       <div class="grid-2" style="float: left;">
            <div class="title-grid">{"Tags"|localize:custompages}<span></span></div>
            <div class="content-table-grid">
                <table class="insideGridTable">
                    <tfoot>
                       <tr>
                           <td colspan="2"><em>{"Short keywords describing page content, helpfully in search engines positioning."|localize:custompages}</em></td>
                       </tr>
                    </tfoot>

                    <tbody id="tags">
                      {foreach from=$tag_list key=k item=i}
                       <tr id="tag_p_{$k}">
                           <td style="width: 380px;"><input type="text" value="{$i}" name="tag_{$k}" style="width: 360px;"></td>
                           <td style="border-right: 0px;"><input type="button" value="{"Delete"|localize:messages}" onclick="deleteTag('tag_p_{$k}'); return false;"></td>
                       </tr>
                      {/foreach}

                    </tbody>
                    
                    <tbody>
                       <tr>
                         <td style="width: 380px;"><input type="text" name="tag_new" id="new_tag_text" style="width: 360px;"></td>
                         <td style="border-right: 0px;"><input type="button" value="{"Add new tag"|localize:custompages}" id="new_tag"></td>
                       </tr>
                    </tbody>
               </table>
            </div>
        </div>
        <!-- end of tags -->
       
        <div class="grid-2" style="float: right; margin-right: 25px;">
             <div class="title-grid">{"Options"|localize:custompages}<span></span></div>
             <div class="content-table-grid">
                <table class="insideGridTable">
                    <tbody>
                       <tr>
                           <td>{"Set this page for all languages"|localize:custompages}:<br><small>{"Content of this static page will be visibile in all languages"|localize:custompages}</td>
                           <td style="border-right: 0px;"><input type="checkbox" name="for_all_languages" value="1" {if $allPages == True}checked{/if}></td>
                       </tr>
                       
                       <tr>
                         <td style="width: 150px;">{"Save this page in"|localize:custompages}:</td>
                         <td style="border-right: 0px;">
                             <select name="new_language">
                             {foreach from=$languages key=k item=i}
                                 <option value="{$k}"{if $k == $custompage_language}selected{/if}>{$k}</option>   
                             {/foreach}
                             </select>
                         </td>
                       </tr>
                       
                       <tr>
                           <td style="width: 60%;">{"SEO name"|localize:custompages}:<br><small>{"Must be unique"|localize:custompages}, (A-Z, a-z, 0-9, -, _, ., ,, +, %)</small></td>
                           <td style="border-right: 0px;"><input type="text" name="url_id" value="{$custompage_url_id}" style="width: 99%;"></td>
                       </tr>
                       
                       <tr>
                           <td>{"Permissions"|localize:custompages}:</td>
                           <td style="border-right: 0px;"><input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_manage_custompage_{$custompage_id}', 1024, 'upload_popup');"></td>
                       </tr>
                       
                       <tr>
                           <td style="border-bottom: 0px;"></td>
                           <td style="border-bottom: 0px; border-right: 0px;"><input type="submit" value="{"Save page"|localize:custompages}"></td>
                       </tr>
                    </tbody>
                </table>
             </div>
        
        </div>

        <!-- content -->
        <div class="grid-1" style="margin-top: 300px;">
        <div class="title-grid">{"Content"|localize:custompages}<span></span></div>
            <div class="content-gird" style="padding: 0px;">
               <textarea name="page_content_custom" id="page_content" style="width: 100%; height: 350px;"></textarea><br>
           </div>
        </div>
        <!-- end of content -->
        </form>
        
        <div class="grid-1">
            <div class="title-grid">{"Informations"|localize:custompages}<span></span></div>
            <div class="content-table-grid">
                 <table class="insideGridTable">
                    <tr>
                        <td>id</td>
                        <td style="border-right: 0px;">{$custompage_id}</td>
                    </tr>
                    <tr>
                        <td>unique</td>
                        <td style="border-right: 0px;">{$custompage_unique}</td>
                    </tr>
                    
                    <tr>
                        <td>language</td>
                        <td style="border-right: 0px;">{$custompage_language}</td>
                    </tr>
                    
                    <tr>
                        <td>URL</td>
                        <td style="border-right: 0px;"><a href="{$custompage_url_id_address}" target="_blank">{$custompage_url_id_address}</a><br><a href="{$custompage_unique_address}" target="_blank">{$custompage_unique_address}</a><br><a href="{$custompage_id_address}" target="_blank">{$custompage_id_address}</a></td>
                    </tr>
                    
                    <tr>
                        <td style="border-bottom: 0px;">url_id</td>
                        <td style="border-bottom: 0px; border-right: 0px;">{$custompage_url_id}</td>
                    </tr>
                 </table>
            </div>
        </div>
</div>
