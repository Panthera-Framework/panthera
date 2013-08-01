<h2 class="popupHeading">{$server}</h2>

<table class="gridTable">
    <thead>
        <tr>
            <th scope="col" class="rounded-company">{function="localize('Key')"}</th>
            <th colspan="2">{function="localize('Value')"}</th>
        </tr>
    </thead>
    
    <tfoot>
        <tr>
            <td colspan="2" class="rounded-foot-left">
                <em>Panthera - {function="localize('Statistics of server', 'cache')"}</em>
            </td>
        </tr>
    </tfoot>
            
    <tbody id="user_list_tbody">
     {loop="$stats"}
      <tr>
        <td>{$key}</td>
        <td>{$value}</td>
      </tr>
     {/loop}
    </tbody>
</table>