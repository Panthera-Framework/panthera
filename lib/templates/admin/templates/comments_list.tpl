{"comments"|localizeDomain}
<script type="text/javascript">
$(document).ready(function(){
    $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});
});

function removeComment(id)
{
    // poput is needed
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=comments&cat=admin&action=delete_comment&cmtid='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                jQuery('#comment_row_'+id).remove();
        }
    });

    return false;
}
</script>

    <div class="titlebar">{"Comments"|localize:comments} - {"List of comments under item"|localize:comments}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company">{"Title"|localize:comments}</th>
                    <th>{"Content"|localize:comments}</th>
                    <th>{"Created"|localize:comments}</th>
                    <th>{"Modified"|localize:comments}</th>
                    <th>{"Author login"|localize:comments}</th>
                    <th>{"Options"|localize:messages}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="7" class="rounded-foot-left"><em>
                      <div class="buttons_right">
                        <input type="button" value='{"Back"|localize:messages}' onclick="navigateTo('?display=comments&cat=admin');" style="float: right;">
                      </div>
                    Panthera - {"niceComments"|localize:comments}</em></td>
                </tr>
            </tfoot>

            <tbody>
              {foreach from=$comments_list key=k item=i}
                <tr id="comment_row_{$i.id}">
                    <td><a href="{$AJAX_URL}?display=comments&cat=admin&action=edit_comment&cmtid={$i.id}" class="ajax_link">{$i.title|localize}</a></td>
                    <td>{$i.content}</td>
                    <td>{$i.date}</td>
                    <td>{$i.modified}</td>
                    <td>{$i.author_login}</td>
                    <td><input type="button" value="{"Delete"|localize:messages}" onclick="removeComment({$i.id}); return false;"></td>
                </tr>
              {/foreach}
            </tbody>
        </table>
    </div>