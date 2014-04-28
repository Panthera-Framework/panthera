{$site_header}

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Settings')"}" onclick="panthera.popup.toggle('element:#settingsPopup')">
        <input type="button" value="{function="localize('Refresh', 'accessparser')"}" onclick="navigateTo(window.location);">
    </div>
</div>

<!-- Settings popup -->

<div id="settingsPopup" style="display: none;">
   <form action="?display=accessparser&cat=admin&action=savePath" method="POST" id="savePath">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px; margin-top: 30px;">
        
        <tbody>
            <tr style="margin-top: 30px;">
                <th style="padding-left: 20px;">{function="localize('Path to access log', 'accessparser')"}:</th>
                <th><input type="text" style="width: 95%;" name="path" value="{$path}"></th>
            </tr>
        </tbody>
        
        <tfoot>
          <tr>
            <td colspan="2" style="padding-top: 35px;">
                <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
            </td>
          </tr>
        </tfoot>
        
    </table>
   </form>
   
   <script type="text/javascript">
      
      /**
        * Save path to access log
        *
        * @author Mateusz Warzyński
        */
   
    $('#savePath').submit(function () {
        panthera.jsonPOST({ data: '#savePath', messageBox: 'w2ui', success: function (response) {
            // refresh the page
            if (response.status == "success")
                navigateTo('{$AJAX_URL}?display=accessparser&cat=admin');
            } 
        });
        return false;
    });
   </script>
</div>


<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">

        <thead>
            <tr>
                <th>{function="localize('Client address', 'accessparser')"}</th>
                <th>{function="localize('Date', 'accessparser')"}</th>
                <th style="max-width: 100px;">{function="localize('Method', 'accessparser')"}</th>
                <th style="max-width: 200px;">{function="localize('Url request', 'accessparser')"}</th>
                <th>{function="localize('Status', 'accessparser')"}</th>
                <th>{function="localize('Browser headers', 'accessparser')"}</th>
            </tr>
        </thead>

     {if="isset($error)"}
        
        <tbody>
            <td colspan="6"><p>{$error_message}</p></td>
        </tbody>
        
     {else}
     
        <tbody class="hovered">
            {loop="$lines"}
            <tr> 
                <td>{$value.client_address}</td>
                <td><small>{$value.time}</small><br>{$value.date}</td>
                <td>{$value.http_method}</td>
                <td style="word-break: break-all;"><small><a href="{$value.url_request}" target="_blank">/{function="end(explode('/', $value.url_request))"}</a></small></td>
                <td>{$value.status}</td>
                <td><small>{$value.browser_headers}</small></td>
            </tr>
            {/loop}
        </tbody>
     {/if}
    </table>
    <div style="width: 65%; margin: 0 auto; margin-top: 10px;">
       <div style="display: inline-block; font-size: 12px;">{$uiPagerName="accessParserLines"}{include="ui.pager"}</div>
    </div>
</div>