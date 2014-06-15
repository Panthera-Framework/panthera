{$site_header}
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

/**
  * Init MCE editor
  *
  * @author Mateusz Warzyński
  */

function initEditor()
{
    mceSetContent('page_content', htmlspecialchars_decode("{$custompage.html|filterInput:wysiwyg}"));
}

function upload_file_callback(data, mime)
{
    if (mime.substr(0, 5) == 'image')
    {
        $('#content_image').val(data);
    }
}
</script>
{function="uiMce::display()"}
<script type="text/javascript">
jQuery(document).ready(function($) {

    mceInit('page_content');
    
    /**
      * Submit editing page form
      *
      * @author Mateusz Warzyński
      */

    $('#save_form').submit(function () {
        panthera.jsonPOST({ data: '#save_form', mce: 'tinymce_all'});
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

        $('#page_title_editor').html('<input type="text" name="content_title" value="{$custompage_title_escaped}">');
    });

});
</script>

{include="ui.titlebar"}

<form action="{$AJAX_URL}?id={$custompage.unique}&lang={$custompage_language}&display=custom&cat=admin&action=savePage&pid={$custompage.id}" method="POST" id="save_form">
<div id="topContent">
    <div class="searchBarButtonArea">
        
        <div class="searchBarButtonAreaLeft">
            <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=custom&cat=admin');">
        </div>
    
        <span data-searchbardropdown="#searchDropdown" id="searchDropdownSpan" style="position: relative; cursor: pointer;">
             <input type="button" value="{function="localize('Edit translations', 'custompages')"}">
        </span>

        <div id="searchDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
            <ul class="searchBarDropdown-menu">
            {loop="$languages"}
                <li style="text-align: left;"><a href="#{$key}" onclick="navigateTo('?display=custom&cat=admin&action=editPage&uid={$custompage.unique}&language={$key}');">{$key|ucfirst}</a></li>
            {/loop}
            </ul>
        </div>
       
        <input type="button" value="{function="localize('Add to menu', 'menuedit')"}" onclick="createPopup('_ajax.php?display=menuedit&cat=admin&popup=true&action=quickAddFromPopup&link={$custompage_url_id_address}&title={$custompage.title}&language={$custompage_language}');">
        <input type="submit" value="{function="localize('Save page', 'custompages')"}">
    </div>
</div>

<style>
    .rightTable {
        width: 40%;
        margin-bottom: 25px;
        margin-top: 25px;
        float: right;
        vertical-align: text-top;
    }
    
    .leftTable {
        width: 55%; float: left;
    }

    @media screen and (max-width: 1351px) {
        .customPagesTables {
            float: left;
            width: 100%;
        }
    }
</style>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; width: 90%; margin: 0 auto;">
        <table id="table_details" class="rightTable customPagesTables">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Basic page settings', 'custompages')"}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr id="tr_title">
                    <td>{function="localize('Title', 'custompages')"}:</td><td><input type="text" name="content_title" value="{$custompage.title|addslashes}" style="width: 99%;"></td>
                </tr>
                
                <tr id="tr_description">
                    <td>{function="localize('Description', 'custompages')"}:</td><td><input type="text" name="content_description" value="{$custompage.description|addslashes}" style="width: 99%;"></td>
                </tr>
                
                <tr id="tr_image">
                    <td>{function="localize('Image', 'custompages')"}:<br><small>{function="localize('Page image or thumbnail', 'custompages')"}</small></td><td><input type="text" id="content_image" name="content_image" value="{$custompage.image|addslashes}" style="min-width: 40px; width: 40%;"> 
                        <input type="button" value="Select" onclick="panthera.popup.toggle('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback');">
                    </td>
                </tr>
                
                <tr id="tr_for_all_languages">
                    <td>{function="localize('Set this page for all languages', 'custompages')"}:<br><small>{function="localize('Content of this static page will be visibile in all languages', 'custompages')"}</small></td>
                    <td><input type="checkbox" name="for_all_languages" value="1" {if="$allPages == True}checked{/if"}></td>
                </tr>
                
                <tr id="tr_save_language">
                    <td style="width: 150px;">{function="localize('Save this page in', 'custompages')"}:</td>
                    <td>
                        <select name="new_language" title="{function="localize('Warning! Be careful with this option. If there is already a translation of this page in selected language you could overwrite it.', 'custompages')"}">
                        {loop="$languages"}
                        <option value="{$key}"{if="$key == $custompage_language"}selected{/if}>{$key}</option>   
                        {/loop}
                        </select>
                    </td>
                </tr>
                
                <tr id="try_url_id">
                    <td style="width: 60%;">{function="localize('SEO name', 'custompages')"}:<br><small>{function="localize('Must be unique', 'custompages')"}, (A-Z, a-z, 0-9, -, _, ., ,, +, %)</small></td>
                    <td><input type="text" name="url_id" value="{$custompage.url_id}" style="width: 99%;"></td>
                </tr>
            </tbody>
        </table>
    
        <div class="pageContentTextarea leftTable customPagesTables">
            <textarea name="page_content_custom" id="page_content" style="width: 100%; height: 450px;"></textarea>
        </div>
        
        <table id="table_tags" class="rightTable customPagesTables">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Tags', 'custompages')"}</th>
                </tr>
            </thead>
            
            <tbody id="tags">
                {loop="$tag_list"}
                <tr id="tag_p_{$key}">
                    <td style="width: 380px;"><input type="text" value="{$value}" name="tag_{$key}" style="width: 360px;"></td>
                    {if="!isset($readOnly)"}<td><input type="button" value="{function="localize('Delete', 'messages')"}" onclick="deleteTag('tag_p_{$key}'); return false;"></td>{/if}
                </tr>
                {/loop}
            </tbody>
            
            {if="!isset($readOnly)"}
            <tbody>
                <tr>
                    <td><input type="text" name="tag_new" id="new_tag_text" style="width: 95%;"></td>
                    <td><input type="button" value="{function="localize('Add new tag', 'custompages')"}" id="new_tag"></td>
                </tr>
            </tbody>
            {/if}
        </table>
    </div>
</div>
