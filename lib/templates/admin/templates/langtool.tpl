        <div class="titlebar">{"Languages"|localize:langtool} - {"Manage languages"|localize:langtool}{include file="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
          <table class="gridTable">

            <thead>
                <tr>
                    <th colspan="2">{"Locale"|localize:langtool}</th>
                    <th>{"Path"|localize:langtool}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="3" class="rounded-foot-left"><em>Panthera - {"List of available languages"|localize:langtool}<input type="button" value="{"Back"|localize}" onclick="navigateTo('{navigation::getBackButton()}');" style="float: right;"></em></td>
                </tr>
            </tfoot>

            <tbody>
              {foreach from=$locales key=k item=i}
                <tr>
                    <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$k}.png"></td>
                    <td><a href="?display=langtool&action=domains&locale={$k}">{$k}</a></td>
                    <td>{$i}/{$k}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
