<script type="text/javascript">
    function tableTakeAction(tableID, action, contentID, target)
    {
        panthera.jsonPOST( { url: '?{function="getQueryString('GET', '', 'action')"}&action='+action, data: 'id='+contentID+'&tableID='+tableID, success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?{function="getQueryString('GET', '', 'action')"}');
                }
            }
        });
    }

    if (typeof tableActionRemove == "undefined")
    {
        function tableActionRemove(tableID, contentID)
        {
            panthera.confirmBox.create('{function="localize('Are you sure?')"}', function (responseText) {
                if (responseText == '{function="localize('Yes')"}' || responseText == 'Yes')
                {
                    tableTakeAction(tableID, 'remove', contentID);
                }        
            });
        }
    }
    
    if (typeof tableActionEdit == "undefined")
    {
        function tableActionEdit(tableID, contentID)
        {
             panthera.popup.toggle('?{function="getQueryString('GET', '', 'action,tableID,id')"}&action=editForm&tableID='+tableID+'&id='+contentID);
        }
    }
</script>

<table>
    <thead>
        {loop="$header"}
            <th{if="$value.colspan"} colspan="{$value.colspan}"{/if}>
                {if="$value.bold"}<b>{/if}
                {if="$value.italics"}<i>{/if}
                {if="$value.underline"}<u>{/if}
                {$value.title}
                {if="$value.underline"}</u>{/if}
                {if="$value.italics"}</i>{/if}
                {if="$value.bold"}</b>{/if}
                
                {if="$value.sortable"}&nbsp;&darr;{/if}
            </th>
        {/loop}
        
        {if="$editButtons or $deleteButtons"}
            <th>{function="localize('Options')"}</th>
        {/if}
    </thead>
    
    <tbody class="hovered">
        {if="$body"}
            {loop="$body"}
                {$row=$value}
                <tr>
                {loop="$header"}
                    <td id="td_{$row.__id_html}_{$key}" class="td_{$row.__id_html}">{$row[$key]}</td>
                {/loop}
                
                {if="$editButtons or $deleteButtons"}
                    <td>
                        {if="$editButtons"}
                        <a href="#" onclick="tableActionEdit('{$tableID}', '{$row.__id}')">
                            <img src="{$PANTHERA_URL}/images/admin/ui/edit.png" style="max-height: 22px;" alt="Usuń">
                        </a>
                        {/if}
                        
                        {if="$deleteButtons"}
                        <a href="#" onclick="tableActionRemove('{$tableID}', '{$row.__id}')">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="Usuń">
                        </a>
                        {/if}
                    </td>
                {/if}
                </tr>
            {/loop}
        {else}
            <tr>
                <td colspan="{$emptyTableColspan}" style="text-align: center;">{function="localize('No data to display', 'admin')"}</td>
            </tr>
        {/if}
    </tbody>
    
    {if="$pagerID"}
    <tfoot style="background-color: transparent;">
        <tr>
            <td colspan="{$emptyTableColspan}" class="pager">
                {$uiPagerName=$pagerID}{include="ui.pager"}
            </td>
        </tr>
    </tfoot>
    {/if}
</table>