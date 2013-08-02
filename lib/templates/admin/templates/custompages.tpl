{$site_header}
{function="localizeDomain('cpages')"}
<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Submit add_page form
  *
  * @author Mateusz Warzyński
  */

$('#add_page').submit(function () {
    panthera.jsonPOST({ data: '#add_page', messageBox: 'userinfoBox', mce: 'tinymce_all', success: function (response) {
            if (response.status == "success")
                navigateTo("?display=custom&cat=admin");
        }
    });

    return false;

});


/**
  * Remove custom page from database
  *
  * @author Mateusz Warzyński
  */

function removeCustomPage(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=custom&cat=admin&action=delete_page&pid='+id, data: '', success: function (response) {
            if (response.status == "success")
                jQuery('#custompage_row_'+id).remove();
        }
    });
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

    <div class="titlebar">{function="localize('List of custom pages in', 'custompages')"} <select onChange="getOtherCustomPages()" id="language">
         {loop="$locales"}
           <option value="{$key}" {if="$current_lang == $key"} selected {/if}>{$key}</option>
         {/loop}
           <option value="all" {if="$current_lang == 'all'"} selected {/if} >{function="localize('all', 'messages')"}</option>
        </select>
        {include="_navigation_panel.tpl"}
    </div>

    <div class="grid-1">
        <table class="gridTable" style="padding: 0px; margin: 0px;">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company">{function="localize('Title', 'custompages')"}</th>
                    <th>{function="localize('Created', 'custompages')"}</th>
                    <th>{function="localize('Modified', 'custompages')"}</th>
                    <th>{function="localize('Author name', 'custompages')"}</th>
                    <th>{function="localize('Mod author name', 'custompages')"}</th>
                    <th>{function="localize('Options', 'custompages')"}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="7" class="rounded-foot-left"><em>
                    Panthera - {function="localize('Custom pages', 'custompages')"}</em></td>
                </tr>
            </tfoot>

            <tbody>
              {loop="$pages_list"}
                <tr id="custompage_row_{$value.id}">
                    <td><a href="{$AJAX_URL}?display=custom&cat=admin&action=edit_page&uid={$value.unique}" class="ajax_link">{$value.title|localize}</a></td>
                    <td>{$value.created}</td>
                    <td>{$value.modified}</td>
                    <td>{$value.author_name}</td>
                    <td>{$value.mod_author_name}</td>
                    <td><input type="button" value="{function="localize('Delete', 'messages')"}" onclick="removeCustomPage({$value.id});"></td>
                </tr>
              {/loop}
            </tbody>
        </table>

        <br>

        <table class="gridTable" style="padding: 0px; margin: 0px;">
            <thead>
                <tr>
                    <th scope="col" colspan="3" class="rounded-company">{function="localize('Add new custom page', 'custompages')"}</th>
                </tr>
            </thead>

            <form action="{$AJAX_URL}?display=custom&cat=admin&action=add_page" method="POST" id="add_page">
            <tbody>
                <tr id="tr_newCustomPage">
                    <td style="width: 300px;"><input name="title" type="text" value='{function="localize('Title of new custom page', 'custompages')"}' onfocus="this.value = ''" style="margin-right: 15px; width: 290px;"></td>
                    <td style="width: 80px;">
                        <select name="language" style="margin-right: 16px;">
                        {loop="$locales"}
                            <option value="{$key}">{$key}</option>
                        {/loop}
                            <option value="all">{function="localize('all')"}</option>
                        </select>
                    </td>
                    <td><input type="submit" value="{function="localize('Add')"}"></td>
                </tr>
            </tbody>
            </form>
        </table>
    </div>
</div>

