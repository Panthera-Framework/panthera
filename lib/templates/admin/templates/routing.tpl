{$site_header}

{include="ui.titlebar"}

<div id="newRoutePopup" style="display: none;">
      <script type="text/javascript">
      
        var canonicalLink = '{function="Tools::getQueryString('GET', '', 'action')"}';
        
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
                            navigateTo('?{function="Tools::getQueryString('GET', '', 'action')"}');
                        }
                    }
                });
                return false;
            });
        });
      </script>
      
      <form action="?{function="Tools::getQueryString('GET', 'action=new', 'action')"}&action=new" method="POST" id="addRouteForm">
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
             <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;" class="actionNew">{function="localize('New SEO url', 'routing')"}</p>
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px; display: none;" class="actionEdit">{function="localize('Editing URL', 'routing')"}</p>
                         
                         <p style="margin-top: 30px; font-size: 12px;">
* // {function="localize('Match all request URIs', 'routing')"}<br>
[i]                  // {function="localize('Match an integer', 'routing')"}<br>
[i:id]               // {function="localize('Match an integer as \'id\'', 'routing')"}<br>
[a:action]           // {function="localize('Match alphanumeric characters as \'action\'', 'routing')"}<br>
[h:key]              // {function="localize('Match hexadecimal characters as \'key\'', 'routing')"}<br>
[:action]            // {function="localize('Match anything up to the next / or end of the URI as \'action\'', 'routing')"}<br>
[create|edit:action] // {function="localize('Match either \'create\' or \'edit\' as \'action\'', 'routing')"}<br>
[*]                  // {function="localize('Catch all (lazy, stops at the next trailing slash)', 'routing')"}<br>
[*:trailing]         // {function="localize('Catch all as \'trailing\' (lazy)', 'routing')"}<br>
[**:trailing]        // {function="localize('Catch all (possessive - will match the rest of the URI)', 'routing')"}<br>
.[:format]?          // {function="localize('Match an optional parameter \'format\' - a / or . before the block is also optional', 'routing')"}<br>
</p>
                     </td>
                 </tr>
             </thead>
             
             
             <tbody>
                <tr>
                    <th>{function="localize('Route name', 'routing')"}</th>
                    <th><input type="text" name="name" placeholder="{function="localize('viewArticleController', 'routing')"}"></th>
                </tr>
                
                <tr>
                    <th><a title="{function="localize('Higher value = better', 'routing')"}">{function="localize('Priority', 'routing')"}</a></th>
                    <th><input type="text" name="priority" value="0" title="{function="localize('Higher value = better', 'routing')"}"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Input URL', 'routing')"}</th>
                    <th><input type="text" name="path" placeholder="article-[*:seoname]"></th>
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
                            <option value="{$value}"{if="$value == 'index.php'"} selected{/if}>{$value}</option>
                        {/loop}
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('HTTP methods', 'routing')"}</th>
                    <th>
                        <select name="methods">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="DELETE">DELETE</option>
                            <option value="HEAD">HEAD</option>
                            <option value="GET|POST">GET & POST</option>
                            <option value="GET|POST|HEAD">GET & POST & HEAD</option>
                            <option value="GET|POST|HEAD|DELETE">GET & POST & HEAD & DELETE</option>
                            <option value="GET|POST|HEAD|DELETE|PUT">GET & POST & HEAD & DELETE & PUT</option>
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('Static GET parameters', 'routing')"}</th>
                    <th><input type="text" name="staticget" placeholder="display=viewArticle"></th>
                </tr>
                
                <tr class="routing_type_controller">
                    <th>{function="localize('Static POST parameters', 'routing')"}</th>
                    <th><input type="text" name="staticpost"></th>
                </tr>
                
                <tr class="routing_type_http_redirection" style="display: none;">
                    <th>{function="localize('Redirection URL', 'routing')"}</th>
                    <th><input type="text" name="redirect" placeholder="http://example.org"></th>
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

<div id="addressResolve" style="display: none;">
      <script type="text/javascript">
        $(document).ready(function () {

            /**
              * Add a new route
              *
              * @author Damian Kęska
              */
            
            $('#resolveRouteForm').submit(function () {
                panthera.jsonPOST( { data: '#resolveRouteForm' });
                return false;
            });
        });
      </script>
      
      <form action="?{function="Tools::getQueryString('GET', 'action=resolveTest', 'action')"}&action=resolveTest" method="POST" id="resolveRouteForm">
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
                    <th>{function="localize('Input URL', 'routing')"}</th>
                    <th><input type="text" name="uri" placeholder="index.html"></th>
                </tr>
                
                
                <tr>
                    <th>{function="localize('HTTP methods', 'routing')"}</th>
                    <th>
                        <select name="method">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="DELETE">DELETE</option>
                            <option value="HEAD">HEAD</option>
                            <option value="GET|POST">GET & POST</option>
                            <option value="GET|POST|HEAD">GET & POST & HEAD</option>
                            <option value="GET|POST|HEAD|DELETE">GET & POST & HEAD & DELETE</option>
                            <option value="GET|POST|HEAD|DELETE|PUT">GET & POST & HEAD & DELETE & PUT</option>
                        </select>
                    </th>
                </tr>
             </tbody>
             
             <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        
                        <input type="submit" value="{function="localize('Submit', 'routing')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>

            </table>
         </form>
</div>

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}

    <div class="searchBarButtonArea">
    	<input type="button" value="{function="localize('Resolve an address', 'routing')"}" onclick="panthera.popup.toggle('element:#addressResolve')">
        <input type="button" value="{function="localize('Add new route', 'routing')"}" onclick="panthera.popup.toggle('element:#newRoutePopup')">
    </div>
</div>

<style>
.datasheet td {
	font-size: 11px;
}
</style>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block;">
       {$table}
    </div>
</div>
 
