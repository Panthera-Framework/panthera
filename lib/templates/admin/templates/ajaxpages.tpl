<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>

   <div class="titlebar">{"Index of ajax pages"|localize:ajaxpages} - {"Complete list of all ajax avaliable subpages"|localize:ajaxpages}</div>

        <br>

      <table class="gridTable">

        <thead>
            <tr><th colspan="2"><b>{"Pages index"|localize:ajaxpages}:</b></th></tr>
         </thead>

        <tfoot>
            <tr>
                <td colspan="2" class="rounded-foot-left"><em>Panthera - {"ajax pages"|localize:ajaxpages} <input type="button" value="{"Back"|localize}" onclick="navigateTo('?display=settings&action=system_info');" style="float: right;">
                <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('{$AJAX_URL}?display=acl&popup=true&name=can_see_ajax_pages');" style="float: right; margin-right: 7px;"></em></td>
            </tr>
        </tfoot>

        <tbody>
            {foreach from=$pages key=k item=v}
            <tr><td>{$v.location} / <a href="{$v.link}" class="ajax_link">{$v.name}</a></td></tr>
            {/foreach}
        </tbody>
       </table>

