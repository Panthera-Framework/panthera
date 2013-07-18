<script type="text/javascript">
function jumpToAjaxPage(id)
{
    panthera.htmlGET({ url: '?display=settings&action=users&subaction=show_table&usersPage='+id, success: '#all_users_window' });
}
</script>

<div class="titlebar">{function="localize('Users')"} - {function="localize('All registered users on this website', 'settings')"}{include="_navigation_panel.tpl"}</div>

        <div class="grid-1">
            <div id="all_users_window">
            <table class="gridTable">
                  <thead>
                      <tr>
                              <th scope="col">{function="localize('Login', 'settings')"}</th>
                              <th scope="col">{function="localize('Full name', 'settings')"}</th>
                              <th scope="col">{function="localize('Primary group', 'settings')"}</th>
                              <th scope="col">{function="localize('Joined', 'settings')"}</th>
                              <th scope="col">{function="localize('Default language', 'settings')"}</th>
                        </tr>
                  </thead>
                  <tfoot>
                      <td colspan="6"><em>{function="localize('Users')"} {$users_from}-{$users_to},
                        {loop="$pager"}

                            {if="$value == true"}
                            <a href="#" onclick="jumpToAjaxPage({$key}); return false;"><b>{$key+1}</b></a>

                            {else}
                            <a href="#" onclick="jumpToAjaxPage({$key}); return false;">{$key+1}</a>
                            {/if}

                        {/loop}
                        </em></td>
                  </tfoot>

                  <tbody>

                        {loop="$users_list"}
                        <tr>
                              <td>{if="$view_users == True"}<a href='?display=settings&action=my_account&uid={$value.id}' class='ajax_link'>{$value.login}</a>{else}{$value.login}{/if}</td>
                              <td>{$value.full_name}</td>
                              <td>{$value.primary_group}</td>
                              <td>{$value.joined}</td>
                              <td>{$value.language|ucfirst}</td>
                        </tr>
                        {/loop}
                  </tbody>
             </table>
             </div>

        </div>
