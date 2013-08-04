<h2 class="popupHeading">APC</h2>

<table class="gridTable">
    <thead>
        <tr>
            <th scope="col" class="rounded-company">{function="localize('Device', 'cache')"}</th>
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

    <tfoot>
        <tr>
            <td colspan="2" class="rounded-foot-left">
                <em>Panthera - {function="localize('Cached files', 'cache')"}</em>
            </td>
        </tr>
    </tfoot>

    <tbody id="user_list_tbody">
     {loop="$files"}
      <tr>
        {loop="$value"}
          <td>{$value}</td>
        {/loop}
      </tr>
     {/loop}
    </tbody>
</table>