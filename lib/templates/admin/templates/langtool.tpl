        <div class="titlebar">{function="localize('Languages', 'langtool')"} - {function="localize('Manage languages', 'langtool')"}{include="_navigation_panel.tpl"}</div><br>

        <div class="grid-1">
        <div class="content-table-grid">
          <table class="insideGridTable">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Locale', 'langtool')"}</th>
                    <th>{function="localize('Path', 'langtool')"}</th>
                </tr>
            </thead>


            <tbody>
              {loop="$locales"}
                <tr>
                    <td style="width: 30px;"><img src="{$PANTHERA_URL}/images/admin/flags/{$key}.png"></td>
                    <td><a href="#" onclick="navigateTo('?display=langtool&cat=admin&action=domains&locale={$key}');">{$key}</a></td>
                    <td>{$value}/{$key}</td>
                </tr>
              {/loop}
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="3" class="rounded-foot-left"><em>Panthera - {function="localize('List of available languages', 'langtool')"}<input type="button" value="{function="localize('Locales management', 'langtool')"}" onclick="navigateTo('?display=locales&cat=admin');" style="float: right;"></em></td>
                </tr>
            </tfoot>
            
          </table>
          </div>
        </div>
