{if="$galleryItems"}
    <table class="dashWidget" style="padding-top: 30px; width: 570px;">
        <thead>
            <th colspan="2">
                {function="localize('Recently added gallery images', 'dash')"}
                <span id="widgetRemoveButtons" class="widgetRemoveButtons">
                    <a href="#" onclick="removeWidget('gallery')">
                        <img src="{$PANTHERA_URL}/images/admin/list-remove.png" style="height: 15px; float: right; margin-right: 5px;">
                    </a>
                </span>
            </th>
        </thead>
                
        <tbody>
            <tr>
                <td>
                  {loop="$galleryItems"}
                    <a href="#" onclick="navigateTo('?display=gallery&cat=admin&action=editItemForm&itemid={$value->id}')">
                     <img src="{$value->thumbnail|pantheraUrl}" style="height: 100px; width: 100px;">
                    </a>
                  {/loop}
                </td>
            </tr>          
        </tbody>
    </table>
{/if}