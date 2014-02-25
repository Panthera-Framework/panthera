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
                
                <tr>
                    <th>{function="localize('Static POST parameters', 'routing')"}</th>
                    <th><input type="text" name="staticpost" value="{$itemRow.staticpost}"></th>
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