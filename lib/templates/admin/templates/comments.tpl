    <div class="titlebar">{"Comments"|localize:comments} - {"List of items which have comments"|localize:comments}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
        <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col" class="rounded-company">{"Item"|localize:comments}</th>
                    <th>{"Author login"|localize:comments}</th>
                    <th>{"Author full name"|localize:comments}</th>
                    <th>{"Modified"|localize:comments}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="7" class="rounded-foot-left"><em>
                    Panthera - {"niceComments"|localize:comments}</em></td>
                </tr>
            </tfoot>

            <tbody>
              {foreach from=$items_list key=k item=i}
                <tr id="comment_row_{$i.id}">
                    <td><a href="{$AJAX_URL}?display=comments&action=show_comments&cmtid={$k}" class="ajax_link">{$i.title|localize}</a></td>
                    <td>{$i.author_login}</td>
                    <td>{$i.author_full_name}</td>
                    <td>{$i.mod_time}</td>
                </tr>
              {/foreach}
            </tbody>
        </table>
    </div>
