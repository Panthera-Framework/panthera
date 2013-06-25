<div class="titlebar">{"Users"|localize} - {"All registered users on this website"|localize:settings}</div>

        <div class="grid-1">
            <div id="all_users_window">
            <table class="gridTable">
                  <thead>
                      <tr>
                              <th scope="col">{"Login"|localize:settings}</th>
                              <th scope="col">{"Full name"|localize:settings}</th>
                              <th scope="col">{"Primary group"|localize:settings}</th>
                              <th scope="col">{"Joined"|localize:settings}</th>
                              <th scope="col">{"Default language"|localize:settings}</th>
                        </tr>
                  </thead>
                  <tfoot>
                      <td colspan="6"><em>{"Users"|localize} {$users_from}-{$users_to},
                        {foreach from=$pager key=user item=active}

                            {if $active == true}
                            <a href="#" onclick="jumpToAjaxPage({$user}); return false;"><b>{$user+1}</b></a>

                            {else}
                            <a href="#" onclick="jumpToAjaxPage({$user}); return false;">{$user+1}</a>
                            {/if}

                        {/foreach}
                        </em></td>
                  </tfoot>

                  <tbody>

                        {foreach from=$users_list key=k item=v}
                        <tr>
                              <td>{if $view_users == True}<a href="?display=settings&action=my_account&uid={$v.id}" class="ajax_link">{$v.login}</a>{else}{$v.login}{/if}</td>
                              <td>{$v.full_name}</td>
                              <td>{$v.primary_group}</td>
                              <td>{$v.joined}</td>
                              <td>{$v.language|ucfirst}</td>
                        </tr>

                        {/foreach}
                  </tbody>
             </table>
             </div>

        </div>