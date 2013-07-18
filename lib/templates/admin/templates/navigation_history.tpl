<h2 class="popupHeading">{function="localize('Navigation history', 'ajaxpages')"}</h2>

<table class="gridTable">
    <tfoot>
        <tr>
            <td colspan="5" class="rounded-foot-left">
                <em>Panthera - navigation</em>
            </td>
        </tr>
    </tfoot>
            
    <tbody id="_popup_navigation_history">
    {loop="$navigation_history"}
    <tr>
        <td><a href="#" onclick="navigateTo('{$value}');"> {$value} </a></td>
    </tr>
    {/loop}

    </tbody>
</table>
