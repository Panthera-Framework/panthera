{"cpages"|localizeDomain}
<script type="text/javascript">

/**
  * Delete tag from html code
  *
  * @author Mateusz Warzyński
  */

function deleteTag (htmlid)
{
    jQuery('#'+htmlid+'_{$custompage_id}').remove();
}

/**
  * Submit editing page form
  *
  * @author Mateusz Warzyński
  */

$('#save_form_{$custompage_id}').submit(function () {
    panthera.jsonPOST({ data: '#save_form_{$custompage_id}', messageBox: 'userinfoBox', mce: 'tinymce_all'});
    return false;
});

/**
  * Add tag to existing custom page
  *
  * @author Mateusz Warzyński
  */

jQuery('#new_tag_{$custompage_id}').click(function () {
      tagID = Math.round((new Date()).getTime() / 1000);
      $('#tags').append('<tr id="tag_p_'+tagID+'"><td><input type="text" value="" name="tag_'+tagID+'"></td><td><input type="button" value="Usuń" onclick="deleteTag(\'tag_p_'+tagID+'\'); return false;"></td></tr>');
});

/**
  * Change page title in html code
  *
  * @author Mateusz Warzyński
  */

var spanClicked = false;
jQuery('#page_title_editor_{$custompage_id}').click(function () {
    if (spanClicked == true)
        return false;

    spanClicked = true;

    jQuery('#page_title_editor_{$custompage_id}').html('<input type="text" name="content_title" value="{$custompage_title_escaped}">');
});


jQuery(document).ready(function($) {

    /**
      * Init MCE editor
      *
      * @author Mateusz Warzyński
      */

    function initEditor()
    {
        mceSetContent('page_content_{$custompage_id}', htmlspecialchars_decode("{$custompage_html}"));
    }

    {$mce_init = "init_instance_callback: initEditor,"}
    {include file="mce.tpl"}

    mceInit('page_content_{$custompage_id}');

});


</script>
    <div id="edit_window">
        <form action="{$AJAX_URL}?id={$custompage_unique}&lang={$custompage_language}&display=custom&action=edit_page&subaction=post_form&pid={$custompage_id}" method="POST" id="save_form_{$custompage_id}">
        <div class="titlebar">{"Editing page"|localize:custompages} - <span id="page_title_editor_{$custompage_id}" style="color: black;">"{$custompage_title}"</span></div>

        <br>
         <div class="msgSuccess" id="userinfoBox_success"></div>
         <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
        <div class="title-grid">{"Content"|localize:custompages}<span></span></div>
        <div class="content-gird">
               <textarea name="page_content_custom_{$custompage_id}" id="page_content_{$custompage_id}"></textarea><br>

               <input type="submit" value="{"Save"|localize:messages}"><input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_manage_custompage_{$custompage_id}', 1024, 'upload_popup');"> <input type="button" value="{"Back"|localize:messages}" onclick="navigateTo('{navigation::getBackButton()}');" style="float: right;">

           <br><br>
           </div>
        </div>
        <br>

           <table class="gridTable">
             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{"Tags"|localize:custompages}</th>
                  <th scope="col">{"Options"|localize:messages}</th>
                </tr>
             </thead>

             <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>{"Short keywords describing page content, helpfully in search engines positioning."|localize:custompages}</em></td>
                </tr>
             </tfoot>

             <tbody id="tags">
               {foreach from=$tag_list key=k item=i}
                <tr id="tag_p_{$k}_{$custompage_id}">
                    <td><input type="text" value="{$i}" name="tag_{$k}"></td>
                  <td><input type="button" value="{"Delete"|localize:messages}" onclick="deleteTag('tag_p_{$k}'); return false;"></td>
                </tr>
               {/foreach}

             </tbody>
             <tbody>
                <tr>
                  <td><input type="text" name="tag_new"></td>
                  <td><input type="button" value="{"Add new tag"|localize:custompages}" id="new_tag_{$custompage_id}"></td>
                </tr>
             </tbody>
           </table>

        </form>
        </div>