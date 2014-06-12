{$site_header}

{include="ui.titlebar"}

<div id="newRoutePopup" style="display: none;">
      <script type="text/javascript">
      
        var canonicalLink = '{function="getQueryString('GET', '', 'action')"}';
        
        function selectRoutingType()
        {
            if ($('input[name=routingType]:checked').val() == '1')
            {
                $('.routing_type_http_redirection').show();
                $('.routing_type_controller').hide();
            } else {
                $('.routing_type_http_redirection').hide();
                $('.routing_type_controller').show();
            }
        }
      
        $(document).ready(function () {

            /**
              * Add a new route
              *
              * @author Damian Kęska
              */
            
            $('#addRouteForm').submit(function () {
                panthera.jsonPOST( { data: '#addRouteForm', success: function (response) {
        
                        if (response.status == "success") {
                            navigateTo('?{function="getQueryString('GET', '', 'action')"}');
                        }
                    }
                });
                return false;
            });
        });
      </script>
      
      <form action="?{function="getQueryString('GET', 'action=new', 'action')"}&action=new" method="POST" id="addRouteForm">
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
             <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;" class="actionNew">{function="localize('New SEO url', 'routing')"}</p>
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px; display: none;" class="actionEdit">{function="localize('Editing URL', 'routing')"}</p>
                     </td>
                 </tr>
             </thead>
             
             
             <tbody>
                <tr>
                    <th>{function="localize('Route name', 'routing')"}</th>
                    <th><input type="text" name="name"></th>
                </tr>
                
                <tr>
                    <th><a title="{function="localize('Higher value = better', 'routing')"}">{function="localize('Priority', 'routing')"}</a></th>
                    <th><input type="text" name="priority" value="0" title="{function="localize('Higher value = better', 'routing')"}"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Input URL', 'routing')"}</th>
                    <th><input type="text" name="path"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Routing type', 'routing')"}</th>
                    <th><input type="radio" name="routingType" id="routingType" value="1" onchange="selectRoutingType();"> {function="localize('HTTP redirection', 'routing')"}<br><input type="radio" id="routingType" name="routingType" value="2" checked onchange="selectRoutingType();"> {function="localize('Controller', 'routing')"}</th>
                </tr>
                
                <tr class="routing_type_controller">
                    <th>{function="localize('Controller', 'routing')"}</th>
                    <th>
                        <select name="controller">
                        {loop="$controllers"}
                            <option value="{$value}">{$value}</option>
                        {/loop}
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('HTTP methods', 'routing')"}</th>
                    <th>
                        <select name="methods">
                            <option value="GET|POST">GET & POST</option>
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('Static GET parameters', 'routing')"}</th>
                    <th><input type="text" name="staticget"></th>
                </tr>
                
                <tr class="routing_type_controller">
                    <th>{function="localize('Static POST parameters', 'routing')"}</th>
                    <th><input type="text" name="staticpost"></th>
                </tr>
                
                <tr class="routing_type_http_redirection" style="display: none;">
                    <th>{function="localize('Redirection URL', 'routing')"}</th>
                    <th><input type="text" name="redirect"></th>
                </tr>
                
                <tr class="routing_type_http_redirection" style="display: none;">
                    <th>{function="localize('Redirection code', 'routing')"}</th>
                    <th>
                        <select name="code">
                            <option value=""></option>
                            <option value="300">{function="localize('300 multiple choices', 'routing')"}</option>
                            <option value="301">{function="localize('301 moved permanently', 'routing')"}</option>
                            <option value="302">{function="localize('302 found', 'routing')"}</option>
                            <option value="303">{function="localize('303 see other', 'routing')"}</option>
                            <option value="307">{function="localize('307 temporary redirect', 'routing')"}</option>
                        </select>
                    </th>
                </tr>
             </tbody>
             
             <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        
                        <input type="submit" value="{function="localize('Save', 'users')"}" style="float: right; margin-right: 30px; display: none;" class="actionEdit">
                        <input type="submit" value="{function="localize('Create', 'users')"}" style="float: right; margin-right: 30px;" class="actionNew">
                    </td>
                </tr>
            </tfoot>

            </table>
            
            <input type="hidden" name="action" value="new">
            <input type="hidden" name="tableID" value="{$tableID}">
         </form>
</div>

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}

    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Add new route', 'routing')"}" onclick="panthera.popup.toggle('element:#newRoutePopup')">
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block;">
       {$table}
    </div>
</div>
 
