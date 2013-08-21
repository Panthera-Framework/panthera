<script type="text/javascript">

var spinner = new panthera.ajaxLoader($('#menu_item'));

    /**
      * Save changes to database (item)
      *
      * @author Mateusz Warzyński
      */

    $('#save_form').submit(function () {
        panthera.jsonPOST({ data: '#save_form', spinner: spinner, messageBox: 'userinfoBox', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?display=menuedit&cat=admin&action=category&category={$cat_type}');
                }
        
            }
        });

        return false;

    });
</script>


<div class="titlebar">{function="localize('Menu editor', 'menuedit')"} - {function="localize('Editing item', 'menuedit')"}: {$item_title}</div><br>

    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1" style="position: relative;" id="menu_item">
      <form id="save_form" method="POST" action="?display=menuedit&cat=admin&action=save_item">
        <table class="gridTable">
              <thead>
                  <tr>
                      <th scope="col" class="rounded-company" style="width: 250px;">&nbsp;</th>
                      <th>&nbsp;</th>
                  </tr>
              </thead>
              <tfoot>
                  <tr>
                      <td colspan="7" class="rounded-foot-left"><em>Panthera menuedit - {function="localize('Editing item', 'menuedit')"}</em><span>
                      <input type="submit" value="{function="localize('Save')"}" style="float: right;"> <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=menuedit&cat=admin&action=category&category={$cat_type}');" style="float: right;">
                  </tr>
              </tfoot>
              <tbody>
                  <tr>
                      <td>{function="localize('Title', 'menuedit')"}</td>
                      <td><input type="text" name="item_title" value="{$item_title}" style="width: 99%;"></td>
                  </tr>
                  <tr>
                      <td>{function="localize('Link', 'messages')"}</td>
                      <td><input type="text" name="item_link" value="{$item_link}" style="width: 99%;"></td>
                  </tr>
                  <tr>
                      <td>{function="localize('Language', 'menuedit')"}</td>
                      <td>
                      <select name="item_language">
                      {loop="$item_language"}
                          <option value="{$key}"{if="$value == True"} selected{/if}>{$key}</option>
                      {/loop}
                      </select>

                      </td>
                  </tr>
                  <tr>
                      <td>{function="localize('SEO friendly name', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                      <td><input type="text" name="item_url_id" value="{$item_url_id}" style="width: 99%;"></td>
                  </tr>
                  <tr>
                      <td>{function="localize('Tooltip', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                      <td><input type="text" name="item_tooltip" value="{$item_tooltip}" style="width: 99%;"></td>
                  </tr>
                  <tr>
                      <td>{function="localize('Icon', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                      <td><input type="text" name="item_icon" value="{$item_icon}" style="width: 99%;"></td>
                  </tr>
                  <tr>
                      <td>{function="localize('Attributes', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small></td>
                      <td><input type="text" name="item_attributes" value='{$item_attributes}' style="width: 99%;"></td>
                  </tr>
              </tbody>
        </table>
        <input type="hidden" name="item_id" value="{$item_id}">
        <input type="hidden" id="cat_type" name="cat_type" value="{$cat_type}">
       </form>
    </div>
