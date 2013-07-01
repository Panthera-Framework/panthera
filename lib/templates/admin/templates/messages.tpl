{"qmessages"|localizeDomain}
<script type="text/javascript">
$(document).ready(function(){
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
});
</script>

    <div class="titlebar">{"Message categories"|localize:qmessages} - {"Articles, quick messages, news etc."|localize:qmessages}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">{"Category name"|localize:qmessages}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="5" class="rounded-foot-left"><em>Panthera - {"quickMessages"|localize:qmessages}</em>
                    </td>
                </tr>
            </tfoot>

            <tbody>
              {foreach from=$categories key=k item=i}
                <tr>
                    <td><a href="{$AJAX_URL}?display=messages&action=display_category&cat={$i.category_name}" class="ajax_link">{$i.title|localize}</a></td>
                </tr>
              {/foreach}
            </tbody>
        </table>
   </div>
