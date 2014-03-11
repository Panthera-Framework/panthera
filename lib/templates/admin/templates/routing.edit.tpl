<script type="text/javascript">
function selectRoutingType()
{
    if ($('input[name=routingTypeX]:checked').val() == '1')
    {
        $('.routing_type_http_redirection').show();
        $('.routing_type_controller').hide();
    } else {
        $('.routing_type_http_redirection').hide();
        $('.routing_type_controller').show();
    }
}

$(document).ready(function() {
    {if="$itemRow.redirect or $itemRow.code"}
    $('input[name=routingTypeX][value=1]').attr('checked', true);
    {else}
    $('input[name=routingTypeX][value=2]').attr('checked', true);
    {/if}
    selectRoutingType();
});
</script>

<form action="?{function="getQueryString('GET', 'action=new', 'action')"}&action=edit" method="POST" id="editRouteForm">
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
             <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;" class="actionEdit">{function="localize('Editing URL', 'routing')"}</p>
                     </td>
                 </tr>
             </thead>
             
             
             <tbody>
                <tr>
                    <th>{function="localize('Route name', 'routing')"}</th>
                    <th><input type="text" name="name" value="{$itemRow.name}"><input type="hidden" name="oldid" value="{$rowID}"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Input URL', 'routing')"}</th>
                    <th><input type="text" name="path" value="{$itemRow.path}"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Routing type', 'routing')"}</th>
                    <th><input type="radio" name="routingTypeX" id="routingTypeX" value="1" {if="$itemRow.redirect or $itemRow.code"}checked{/if} onchange="selectRoutingType();"> {function="localize('HTTP redirection', 'routing')"}<br><input type="radio" id="routingTypeX" name="routingTypeX" value="2" {if="!$itemRow.redirect and !$itemRow.code"}checked{/if} onchange="selectRoutingType();"> {function="localize('Controller', 'routing')"}</th>
                </tr>
                
                <tr class="routing_type_controller">
                    <th>{function="localize('Controller', 'routing')"}</th>
                    <th>
                        <select name="controller">
                        {loop="$controllers"}
                            <option value="{$value}"{if="$itemRow.controller == $value"} selected{/if}>{$value}</option>
                        {/loop}
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('HTTP methods', 'routing')"}</th>
                    <th>
                        <select name="methods">
                            <option value="GET|POST"{if="$itemRow.methods == 'GET, POST'"} selected{/if}>GET & POST</option>
                            <option value="GET"{if="$itemRow.methods == 'GET'"} selected{/if}>GET</option>
                            <option value="POST"{if="$itemRow.methods == 'POST'"} selected{/if}>POST</option>
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('Static GET parameters', 'routing')"}</th>
                    <th><input type="text" name="staticget" value="{$itemRow.staticget}"></th>
                </tr>
                
                <tr class="routing_type_controller">
                    <th>{function="localize('Static POST parameters', 'routing')"}</th>
                    <th><input type="text" name="staticpost" value="{$itemRow.staticpost}"></th>
                </tr>
                
                <tr class="routing_type_http_redirection" style="display: none;">
                    <th>{function="localize('Redirection URL', 'routing')"}</th>
                    <th><input type="text" name="redirect" value="{$itemRow.redirect}"></th>
                </tr>
                
                <tr class="routing_type_http_redirection" style="display: none;">
                    <th>{function="localize('Redirection code', 'routing')"}</th>
                    <th>
                        <select name="code">
                            <option value=""></option>
                            <option value="300"{if="$itemRow.code == 300"} selected{/if}>{function="localize('300 multiple choices', 'routing')"}</option>
                            <option value="301"{if="$itemRow.code == 301"} selected{/if}>{function="localize('301 moved permanently', 'routing')"}</option>
                            <option value="302"{if="$itemRow.code == 302"} selected{/if}>{function="localize('302 found', 'routing')"}</option>
                            <option value="303"{if="$itemRow.code == 303"} selected{/if}>{function="localize('303 see other', 'routing')"}</option>
                            <option value="307"{if="$itemRow.code == 307"} selected{/if}>{function="localize('307 temporary redirect', 'routing')"}</option>
                        </select>
                    </th>
                </tr>
             </tbody>
             
             <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        
                        <input type="submit" value="{function="localize('Save', 'users')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>

            </table>
            
            <input type="hidden" name="action" value="new">
            <input type="hidden" name="tableID" value="{$tableID}">
         </form>
         
<script type="text/javascript">
      
        var canonicalLink = '{function="getQueryString('GET', '', 'action')"}';
      
        $(document).ready(function () {

            /**
              * Edit a route
              *
              * @author Damian Kęska
              */
            
            $('#editRouteForm').submit(function () {
                panthera.jsonPOST( { data: '#editRouteForm', success: function (response) {
        
                        if (response.status == "success") {
                            navigateTo('?{function="getQueryString('GET', '', 'action')"}');
                        }
                    }
                });
                return false;
            });
        });
      </script>