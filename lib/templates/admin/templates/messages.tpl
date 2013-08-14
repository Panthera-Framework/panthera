{function="localizeDomain('qmessages')"}
<script type="text/javascript">
$(document).ready(function(){
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
});
</script>

    <div class="titlebar">{function="localize('Message categories', 'qmessages')"} - {function="localize('Articles, quick messages, news etc.', 'qmessages')"}{include="_navigation_panel"}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Category name', 'qmessages')"}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {function="localize('quickMessages', 'qmessages')"}</em>
                    </td>
                </tr>
            </tfoot>

            <tbody>
              {loop="$categories"}
                <tr>
                    <td><a href="{$AJAX_URL}?display=messages&cat=admin&action=display_category&category={$value.category_name}" class="ajax_link">{$value.title|localize}</a></td>
                </tr>
              {/loop}
            </tbody>
        </table>
   </div>
