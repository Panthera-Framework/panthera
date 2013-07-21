<script type="text/javascript">
$(document).ready(function () {
    /**
      * After click on a "Add" button a form will be sent
      *
      * @event click
      * @author Damian Kęska
      */

    $('#addrAddBtn').click(function () {
        panthera.jsonPOST({ url: '?display=firebugSettings&action=add', data: 'addr='+$('#addr').val(), messageBox: 'msgFirebug', success: function (response) {
             if (response.status == "success")
                navigateTo('?display=firebugSettings');
        }});

    });
});

/**
 * Remove address from table
 *
 * @param string address IP address
 * @author Damian Kęska
 */

function removeAddress(address, id)
{
    panthera.jsonPOST({ url: '?display=firebugSettings&action=remove', data: 'addr='+address, messageBox: 'msgFirebug', success: function (response) {
        if (response.status == "success")
            $('#addr_'+id).remove();

        }
   });
}

</script>

<style>
#container {
    background: url("images/admin/menu/firebug.png") no-repeat transparent;
    background-size: 120px;
    background-position: 80% 100%;
}
</style>

<div class="titlebar">{function="localize('Firebug settings', 'firebug')"}</div>
      <div class="msgError" id="msgFirebug_failed"></div>

      <div class="grid-2">
          <div class="title-grid">{function="localize('Whitelist - only listed users will be able to use Firebug', 'firebug')"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="2" class="rounded-foot-left"><small><i><b>{function="localize('tip', 'firebug')"}:</b> {function="localize('Remove all entries to allow all clients to use Firebug', 'firebug')"}</i></small></td>
                    </tr>
                </tfoot>

                <tbody>
                    {if="count($whitelist) == 0"}
                    <tr><td colspan="2">{function="localize('No addresses in whitelist, everybody is able to use Firebug', 'firebug')"}</td></tr>
                    {else}
                    {loop="$whitelist"}
                    <tr id="addr_{$key}">
                        <td>{$value}</td><td style="width: 1%; padding-right: 10px; border-right: 0px;"><input type="button" value="{function="localize('Delete')"}" onclick="removeAddress('{$value}', '{$key}');"></td>
                    </tr>
                    {/loop}
                    {/if}

                    <tr>
                        <td><input type="text" value="{$current_address}" style="width: 98%;" id="addr"></td><td style="width: 1%; padding-right: 10px; border-right: 0px;"><input type="button" value="{function="localize('Add')"}" id="addrAddBtn"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="grid-2">
        <div class="title-grid">{function="localize('informations', 'firebug')"}<span></span></div>
        <div class="content-table-grid">
              <table class="insideGridTable">
                <tbody>
                    <tr><td>{function="localize('Client version', 'firebug')"}:</td><td>{$client_version}</td></tr>
                    <tr><td style="border-bottom: 0px;">{function="localize('Server version', 'firebug')"}:</td><td style="border-bottom: 0px; border-right: 0px;">{$server_version}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
