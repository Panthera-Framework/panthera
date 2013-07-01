{"cpages"|localizeDomain}
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
                navigateTo("?display=custom");
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
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=custom&action=delete_page&pid='+id, data: '', success: function (response) {
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
    navigateTo("?display=custom&lang="+value);
}
</script>

    <div class="titlebar">{"List of custom pages in"|localize:custompages} <select onChange="getOtherCustomPages()" id="language">
         {foreach from=$locales key=k item=i}
           <option value="{$k}" {if $current_lang eq $k} selected {/if}>{$k}</option>
         {/foreach}
           <option value="all" {if $current_lang eq 'all'} selected {/if} >{"all"|localize:messages}</option>
        </select>
    </div>

    <div class="grid-1">
        <table class="gridTable" style="padding: 0px; margin: 0px;">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company">{"Title"|localize:custompages}</th>
                    <th>{"Created"|localize:custompages}</th>
                    <th>{"Modified"|localize:custompages}</th>
                    <th>{"Author name"|localize:custompages}</th>
                    <th>{"Mod author name"|localize:custompages}</th>
                    <th>{"Options"|localize:custompages}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="7" class="rounded-foot-left"><em>
                    Panthera - {"Custom pages"|localize:custompages}</em></td>
                </tr>
            </tfoot>

            <tbody>
              {foreach from=$pages_list key=k item=i}
                <tr id="custompage_row_{$i.id}">
                    <td><a href="{$AJAX_URL}?display=custom&action=edit_page&uid={$i.unique}" class="ajax_link">{$i.title|localize}</a></td>
                    <td>{$i.created}</td>
                    <td>{$i.modified}</td>
                    <td>{$i.author_name}</td>
                    <td>{$i.mod_author_name}</td>
                    <td><input type="button" value="{"Delete"|localize:messages}" onclick="removeCustomPage({$i.id});"></td>
                </tr>
              {/foreach}
            </tbody>
        </table>

        <br>

        <table class="gridTable" style="padding: 0px; margin: 0px;">
            <thead>
                <tr>
                    <th scope="col" colspan="3" class="rounded-company">{"Add new custom page"|localize:custompages}</th>
                </tr>
            </thead>

            <form action="{$AJAX_URL}?display=custom&action=add_page" method="POST" id="add_page">
            <tbody>
                <tr id="custompage_row_{$i.id}">
                    <td style="width: 300px;"><input name="title" type="text" value='{"Title of new custom page"|localize:custompages}' onfocus="this.value = ''" style="margin-right: 15px; width: 290px;"></td>
                    <td style="width: 80px;">
                        <select name="language" style="margin-right: 16px;">
                        {foreach from=$locales key=k item=i}
                            <option value="{$k}">{$k}</option>
                        {/foreach}
                            <option value="all">{"all"|localize}</option>
                        </select>
                    </td>
                    <td><input type="submit" value="{"Add"|localize}"></td>
                </tr>
            </tbody>
            </form>
        </table>
    </div>
</div>

