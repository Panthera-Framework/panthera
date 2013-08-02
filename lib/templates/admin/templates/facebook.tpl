{function="localizeDomain('facebook')"}
<script type="text/javascript">

/**
  * Save information from Facebook about user to database
  *
  * @author Mateusz Warzyński
  */

function synchronizeData()
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=facebook&cat=admin&action=synchronize', data: '', messageBox: 'userinfoBox'});
    return false;
}
</script>

    <div class="titlebar">{function="localize('Facebook', 'facebook')"} - {function="localize('Facebook integration.', 'facebook')"}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company">{function="localize('Key', 'facebook')"}</th>
                    <th>{function="localize('Value', 'facebook')"}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>
                        <div class="buttons_right">
                              <input type="button" value="{function="localize('Synchronize', 'facebook')"}" onclick="synchronizeData();" style="float: right;">
                        </div>
                        faceIntegration - {function="localize('Information from Facebook', 'facebook')"}
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
                <tr>
                    <td>{function="localize('ID', 'facebook')"}</td>
                    <td>{$user.id}</td>
                </tr>
                <tr>
                    <td>{function="localize('Name', 'facebook')"}</td>
                    <td>{$user.name}</td>
                </tr>
                <tr>
                    <td>{function="localize('Avatar', 'facebook')"}</td>
                    <td><img src="http://graph.facebook.com/{$user.id}/picture?width=200&height=200"></td>
                </tr>
                <tr>
                    <td>{function="localize('Link', 'facebook')"}</td>
                    <td><a href="{$user.link}">{$user.link}</a></td>
                </tr>
                <tr>
                    <td>{function="localize('Gender', 'facebook')"}</td>
                    <td>{function="localize('$user.gender', 'facebook')"}</td>
                </tr>
                <tr>
                    <td>{function="localize('Timezone', 'facebook')"}</td>
                    <td>{$user.timezone}</td>
                </tr>
                <tr>
                    <td>{function="localize('Locale', 'facebook')"}</td>
                    <td>{$user.locale}</td>
                </tr>
            </tbody>
        </table>
    </div>
