<div id="syschecksum_window">
    {$site_header}

    {include="ui.titlebar"}

    <div id="topContent">
        <div class="searchBarButtonArea">
            <input type="button" value="{function="localize('Import data')"}" onclick="panthera.popup.toggle('element:#importDataPopup')">
            <input type="button" value="{function="localize('Export current data to file', 'debug')"}" onclick="window.location.href='{$AJAX_URL}?display=syschecksum&cat=admin&export&_bypass_x_requested_with'">
        </div>
    </div>

    <!-- Import data popup -->
    <div style="display: none;" id="importDataPopup">
        <form id="upload_form" action="{$AJAX_URL}?display=syschecksum&cat=admin" method="POST" enctype="multipart/form-data">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 30px; margin-top: 30px;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Import', 'debug')"}</p>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>{function="localize('File', 'debug')"}:</th>
                    <td><input type="file" name="syschecksum"></td>
                </tr>
            
                <tr>
                    <th>{function="localize('Show only modified files', 'debug')"}:</th>
                    <td><input type="checkbox" name="show_only_modified" checked="checked" value="1"></td>
                </tr>
                
                <tr>
                    <th>{function="localize('Compare method', 'debug')"}:</th>
                    <td style="color: white;">
                        <input type="radio" name="method" value="sum"> {function="localize('md5 checksum', 'debug')"}<br>
                        <input type="radio" name="method" value="size" checked> {function="localize('file size', 'debug')"}<br>
                        <input type="radio" name="method" value="time"> {function="localize('modification time', 'debug')"}<br>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Import', 'newsletter')"}" style="float: right; margin-right: 30px;"
                    </td>
                </tr>
            </tfoot>
        </table>
        
        <script type="text/javascript">
        $(document).ready(function(){
            $('#upload_form').submit(function () {
                panthera.htmlPOST( { 'data': '#upload_form', success: function (response) {
                        $('#syschecksum_window').html(response);
                    } 
                });
            
                return false;
            });
        });
        </script>
    </div>


    <!-- Main content -->
    <div class="ajax-content" style="text-align: center;">
        <div style="display: inline-block; margin: 0 auto;">
            <table>
                <thead>
                    <tr>
                        <th colspan="5"><b>{function="localize('Files', 'debug')"}:</b></th>
                    </tr>
                 </thead>
                 
                 <tbody class="hovered">
                    {loop="$files"}
                    <tr {if="$value.bold == True"}style="background-color: rgb(255, 197, 197);"{/if}><td>{$value.name}</td><td>{$value.sum}</td><td>{$value.size}</td><td>{$value.time}</td><td>{if="isset($value.created)"}{function="localize('Created')"}{else}{if="$value.bold == True"}{function="localize('Modified')"}{/if}{/if}</td></tr>
                    {/loop}
                </tbody>
            </table>
        </div>
    </div>
</div>
