{"facebook"|localizeDomain}
<script type="text/javascript">

/**
  * Save information from Facebook about user to database
  *
  * @author Mateusz Warzyński
  */

function synchronizeData()
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=facebook&action=synchronize', data: '', messageBox: 'userinfoBox'});
    return false;
}
</script>

    <div class="titlebar">{"Facebook"|localize:facebook} - {"Facebook integration."|localize:facebook}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company">{"Key"|localize:facebook}</th>
                    <th>{"Value"|localize:facebook}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>
                        <div class="buttons_right">
                              <input type="button" value="{"Synchronize"|localize:facebook}" onclick="synchronizeData();" style="float: right;">
                        </div>
                        faceIntegration - {"Information from Facebook"|localize:facebook}
                    </em></td>
                </tr>
            </tfoot>

            <tbody>
                <tr>
                    <td>{"ID"|localize:facebook}</td>
                    <td>{$user.id}</td>
                </tr>
                <tr>
                    <td>{"Name"|localize:facebook}</td>
                    <td>{$user.name}</td>
                </tr>
                <tr>
                    <td>{"Avatar"|localize:facebook}</td>
                    <td><img src="http://graph.facebook.com/{$user.id}/picture?width=200&height=200"></td>
                </tr>
                <tr>
                    <td>{"Link"|localize:facebook}</td>
                    <td><a href="{$user.link}">{$user.link}</a></td>
                </tr>
                <tr>
                    <td>{"Gender"|localize:facebook}</td>
                    <td>{"$user.gender"|localize:facebook}</td>
                </tr>
                <tr>
                    <td>{"Timezone"|localize:facebook}</td>
                    <td>{$user.timezone}</td>
                </tr>
                <tr>
                    <td>{"Locale"|localize:facebook}</td>
                    <td>{$user.locale}</td>
                </tr>
            </tbody>
        </table>
    </div>
