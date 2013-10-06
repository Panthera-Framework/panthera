{$site_header}

<script type="text/javascript">

/**
  * Remove custom page from database
  *
  * @author Mateusz Warzyński
  */

function removeCustomPage(id)
{
    //w2confirm('{function="localize('Are you sure you want delete this page?', 'custompages')"}', function (responseText) {
        //if (responseText == 'Yes')
        //{
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=custom&cat=admin&action=delete_page&pid='+id, data: '', success: function (response) {
                    if (response.status == "success")
                        jQuery('#custompage_row_'+id).remove();
                }
            });
        //}
    //});
}

function uiTop_callback(response)
{
    //alert(response);
}


/**
  * Get custom pages by language
  *
  * @author Mateusz Warzyński
  */

function getOtherCustomPages()
{
    value = jQuery('#language').val();
    navigateTo("?display=custom&cat=admin&lang="+value);
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Add new custom page', 'custompages')"}" onclick="panthera.popup.toggle('element:#addNewPagePopup')">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Adding new page -->
<div style="display: none;" id="addNewPagePopup">
    <form action="{$AJAX_URL}?display=custom&cat=admin&action=add_page" method="POST" id="add_page">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 25px;">
             <thead>
                 <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add new custom page', 'custompages')"}</p>
                    </td>
                 </tr>
             </thead>
             
              <tbody>
                    <tr>
                        <th>{function="localize('Title', 'custompages')"}:</th>
                        <td><input type="text" name="title"></td>
                    </tr>
                    
                    
                    <tr style="background-color: transparent;">
                        <th>{function="localize('Language')"}:</th>
                        <td>
                            <select name="language" style="margin-right: 16px;">
                            {loop="$locales"}
                                <option value="{$key}">{$key}</option>
                            {/loop}
                                <option value="all">{function="localize('all', 'custompages')"}</option>
                            </select>
                        </td>
                    </tr>
              </tbody>
              
              <tfoot>
                    <tr>
                        <td colspan="2" style="padding-top: 35px;">
                            <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                            <input type="submit" value="{function="localize('Add')"}" style="float: right; margin-right: 30px;">
                        </td>
                    </tr>
              </tfoot>
        </table>
    </form>
    
    <script type="text/javascript">
    /**
      * Submit add_page form
      *
      * @author Mateusz Warzyński
      */

    $('#add_page').submit(function () {
        panthera.jsonPOST({ data: '#add_page', mce: 'tinymce_all', success: function (response) {
                if (response.status == "success")
                    navigateTo("?display=custom&cat=admin");
            }
        });

        return false;

    });
    </script>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; margin: 0 auto;">
        <table>
            <thead>
                <tr>
                    <th>{function="localize('Title', 'custompages')"}</th>
                    <th>{function="localize('Created', 'custompages')"}</th>
                    <th>{function="localize('Modified', 'custompages')"}</th>
                    <th>{function="localize('Avaliable in', 'custompages')"}</th>
                    <th>{function="localize('Options', 'custompages')"}</th>
                </tr>
            </thead>
            
            <tbody>
                {if="count($pages_list) > 0"}
                {loop="$pages_list"}
                <tr id="custompage_row_{$value.id}">
                    <td><a href="{$AJAX_URL}?display=custom&cat=admin&action=edit_page&uid={$value.unique}" class="ajax_link">{$value.title|localize}</a></td>
                    <td>{$value.created} {function="localize('by', 'custompages')"} {$value.author_name}</td>
                    <td>{if="$value['created'] == $value['modified']"}{function="localize('without changes', 'custompages')"}{else}{$value.modified} {function="localize('by', 'custompages')"} {$value.mod_author_name}{/if}</td>
                    <td>
                        <select>
                            {loop="$value['languages']"}
                            <option>{$key}</option>
                            {/loop}
                        </select>
                    </td>
                    <td>{if="$value.managementRights"}
                        <a href="#" onclick="removeCustomPage({$value.id});">
                        <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove', 'messages')"}">
                        </a>
                        {/if}
                    </td>
                </tr>
                {/loop}
                {else}
                <tr>
                    <td colspan="6" style="text-align: center;">{function="localize('No any records found', 'custompages')"}</td>
                </tr>
                {/if}
            </tbody>
        </table>
        
        <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="customPages"}{include="ui.pager"}</div>
    </div>
</div>
