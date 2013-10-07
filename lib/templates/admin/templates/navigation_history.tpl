<table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Navigation')"}</p>
                </td>
            </tr>
        </thead>
        <tbody>
            {loop="$navigation_history"}
            <tr>
                <th style="margin-left: 0px;"><a href="#" onclick="navigateTo('{$value}');"> {$value} </a></th>
            </tr>
            {/loop}
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="2" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Close')"}" onclick="panthera.popup.close()" style="float: right; margin-left: 30px;">
                </td>
            </tr>
        </tfoot>
</table>
