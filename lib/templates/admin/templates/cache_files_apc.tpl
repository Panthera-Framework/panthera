    <table style="margin: 0 auto;">
        <thead>
            <tr>
                <th>{function="localize('Device', 'cache')"}</th>
                <th>{function="localize('Inode', 'cache')"}</th>
                <th>{function="localize('Type', 'cache')"}</th>
                <th>{function="localize('Filename', 'cache')"}</th>
                <th>{function="localize('Number of hits', 'cache')"}</th>
                <th>{function="localize('Modification time', 'cache')"}</th>
                <th>{function="localize('Creation time', 'cache')"}</th>
                <th>{function="localize('Deletion time', 'cache')"}</th>
                <th>{function="localize('Access time', 'cache')"}</th>
                <th>{function="localize('Ref count', 'cache')"}</th>
                <th>{function="localize('Memory size', 'cache')"}</th>
            </tr>
        </thead>

        <tbody id="user_list_tbody">
         {loop="$files"}
          <tr>
            {loop="$value"}
              <td>{$value}</td>
            {/loop}
          </tr>
         {/loop}
        </tbody>
        
        <tfoot>
            <tr>
                <td colspan="16">
                    <input type="button" value="{function="localize('Close')"}" onclick="panthera.popup.close()" style="float: right;">
                </td>
            </tr>
        </tfoot>
    </table>
