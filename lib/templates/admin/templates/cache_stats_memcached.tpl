<table style="margin: 0 auto; margin-top: 30px;">
    <thead>
        <tr>
            <th scope="col" class="rounded-company">{function="localize('Key')"}</th>
            <th colspan="2">{function="localize('Value')"}</th>
        </tr>
    </thead>
    
    <tbody id="user_list_tbody">
     {loop="$stats"}
      <tr>
        <td>{$key}</td>
        <td>{$value}</td>
      </tr>
     {/loop}
    </tbody>
    
    <tfoot>
        <tr>
            <th colspan="2">
                <input type="button" value="{function="localize('Close')"}" onclick="panthera.popup.close()" style="float: right; margin-top: 10px;">
            </th>
        </tr>
    </tfoot>
</table>
