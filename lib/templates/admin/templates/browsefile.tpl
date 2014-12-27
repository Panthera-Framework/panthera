{include="ui.titlebar"}

<style>td {border-bottom: 0;height: 15px;}</style>

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <input type="button" value=" {function="localize('Information')"} " onclick="panthera.popup.toggle('element:#informationPopup');" style="float: right; margin-right: 15px;">
    </div>
</div>

<!-- Show information -->
<div id="informationPopup" style="display: none;">
    <style>p {color: white;} h3 {color: #404C5A;}</style>
    <table class="formTable" style="margin: 0 auto;">
        <thead>
        <tr>
            <td colspan="2" class="formTableHeader" style="padding-top: 30px; padding-bottom: 30px;">
                <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('File information', 'filesystem')"}</p>
            </td>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="3" style="padding-top: 35px;">
                <input type="button" value="{function="localize('Close')"}" onclick="panthera.popup.close()" style="float: left;">
            </td>
        </tr>
        </tfoot>
        <tbody>
        <tr>
            <th>{function="localize('Permissions', 'filesystem')"}:</th>
            <td><p>{$perms}</p></td>
        </tr>
        <tr>
            <th>{function="localize('Owner', 'filesystem')"}:</th>
            <td><p>{$owner}</p></td>
        </tr>
        <tr>
            <th>{function="localize('Group', 'filesystem')"}:</th>
            <td><p>{$group}</p></td>
        </tr>
        <tr>
            <th>{function="localize('Size', 'filesystem')"}:</th>
            <td><p>{$size}</p></td>
        </tr>
        <tr>
            <th>{function="localize('Modification time', 'filesystem')"}:</th>
            <td><p>{$modification_time}</p></td>
        </tr>
        <tr>
            <th>{function="localize('Mimetype', 'filesystem')"}:</th>
            <td><p>{$mime}</p></td>
        </tr>
        </tbody>
    </table>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<div class="ajax-content" style="text-align: center;">
    <h3>{$file_path}</h3>
    <div style="display: inline-table; margin: 0 auto;">
        {$contents}
    </div>
</div>