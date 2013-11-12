{$site_header}
<script type="text/javascript">
/**
 * Remove address from table
 *
 * @param string address IP address
 * @author Damian Kęska
 */

function removeAddress(address, id)
{
    panthera.jsonPOST({ url: '?display=firebugSettings&cat=admin&action=remove', data: 'addr='+address, success: function (response) {
            if (response.status == "success")
            {
                $('#addr_'+id).remove();
            }
            
            if ($('.whiteListAddresses').length)
            {
                $('#noAddresses').hide();
            } else {
                $('#noAddresses').show();
            }
        }  
    });
}
</script>

{include="ui.titlebar"}

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Add to whitelist', 'firebug')"}" onclick="panthera.popup.toggle('element:#addToWhitelist')">
    </div>
</div>

<div style="display: none;" id="addToWhitelist">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 25px;">
            <thead>
                 <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Add to whitelist', 'firebug')"}</p>
                    </td>
                 </tr>
             </thead>
             
             <tbody>
                    <tr>
                        <th>{function="localize('Address', 'firebug')"}:</th>
                        <td><input type="text" value="{$current_address}" style="width: 98%;" id="addr"></td>
                    </tr>
             </tbody>
             
             <tfoot>
                    <tr>
                        <td colspan="2" style="padding-top: 35px;">
                            <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                            <input type="button" value="{function="localize('Add')"}" id="addrAddBtn" style="float: right; margin-right: 30px;">
                        </td>
                    </tr>
             </tfoot>
        </table>
        
        <script type="text/javascript">
        /**
          * After click on a "Add" button a form will be sent
          *
          * @event click
          * @author Damian Kęska
          */

        $('#addrAddBtn').click(function () {
            panthera.jsonPOST({ url: '?display=firebugSettings&cat=admin&action=add', data: 'addr='+$('#addr').val(), success: function (response) {
                    if (response.status == "success")
                    {
                       navigateTo('?display=firebugSettings&cat=admin');
                    }
                }
            });

        });
        </script>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-table; margin: 0 auto;">
         <table style="width: 550px;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Whitelist - only listed users will be able to use Firebug', 'firebug')"}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr {if="count($whitelist)"}style="display: none;"{/if} id="noAddresses">
                    <td colspan="2">{function="localize('No addresses in whitelist, everybody is able to use Firebug', 'firebug')"}</td>
                </tr>
                
                {loop="$whitelist"}
                <tr id="addr_{$key}" class="whiteListAddresses">
                    <td>{$value}</td>
                    <td style="width: 1%;"><input type="button" value="{function="localize('Delete')"}" onclick="removeAddress('{$value}', '{$key}');"></td>
                </tr>
                {/loop}
            </tbody>
        </table>
        
        
        <div style="text-align: left;" class="pager">
            <small><i><b>{function="localize('tip', 'firebug')"}:</b> {function="localize('Remove all entries to allow all clients to use Firebug', 'firebug')"}</i></small>
        </div>
    </div>
    
    <div style="display: inline-table; margin: 0 auto;">
        <table>
            <thead>
                <tr>
                    <th colspan="2">{function="ucfirst(localize('informations', 'firebug'))"}</th>
                </tr>
            </thead>
        
            <tbody>
                <tr><td>{function="localize('Client version', 'firebug')"}:</td><td>{$client_version}</td></tr>
                <tr><td>{function="localize('Server version', 'firebug')"}:</td><td>{$server_version}</td></tr>
            </tbody>
        </table>
    </div>
</div>
