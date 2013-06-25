<h2 class="popupHeading">{"Navigation history"|localize:ajaxpages}</h2>

<table class="gridTable">
    <tfoot>
        <tr>
            <td colspan="5" class="rounded-foot-left">
                <em>Panthera - navigation</em>
            </td>
        </tr>
    </tfoot>
            
    <tbody id="_popup_navigation_history">
    {foreach from=$navigation_history key=k item=i}
    <tr>
        <td><a href="#" onclick="navigateTo('{$i}');"> {$i} </a></td>
    </tr>
    {/foreach}

    </tbody>
</table>
