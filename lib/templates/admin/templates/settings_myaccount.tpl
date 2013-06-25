<div class="titlebar">{"My account"|localize:settings} - {"Panel with informations about current user."|localize:settings}</div>

            <br>

            <div class="msgSuccess" id="userinfoBox_success"></div>
            <div class="msgError" id="userinfoBox_failed"></div>

            <table class="gridTable">

             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{"My account"|localize:settings}</th>
                    <th scope="col"> </th>
                </tr>

             </thead>
                <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>{"Informations about user"|localize:settings}</em></td>
                </tr>
             </tfoot>

             <tbody>
                <tr>
                    <td>{"Login"|localize:settings}</td>
                    <td>{$user_login}</td>
                </tr>

                <tr>
                  <td>{"Password"|localize:settings}</td>
                  <td><a href="#" onclick="jQuery('#password_window').slideToggle(); return false;">{"Change password"|localize:settings}</a> <div id="password_window" style="display: none;">

                <form action="?display=settings&action=my_account&changepassword{$user_uid}" method="POST" id="changepasswd_form">
                 <table style="width: 400px; border: 0px; font-size: 12px;">
                    <tfoot>
                        <tr>
                            <td colspan="2" class="rounded-foot-left"><em><input type="submit" value="{"Change password"|localize}"></em></td>
                        </tr>
                    </tfoot>
                    <thead>
                        {if !isset($dontRequireOld)}
                        <tr>
                            <td><input type="password" name="old_passwd"> </td>
                            <td>{"Old password"|localize:settings}</td>
                        </tr>
                        {/if}

                        <tr>
                            <td><input type="password" name="new_passwd"> </td>
                            <td>{"New password"|localize:settings}</td>
                        </tr>

                        <tr>
                            <td><input type="password" name="retyped_newpasswd"></td>
                            <td>{"Retype new password"|localize:settings}</td>
                        </tr>
                    </thead>
                 </table>
                </form>

            </div></td>
                </tr>

                <tr>
                  <td>{"Avatar"|localize:settings}</td>
                  <td><img src="{$profile_picture}" height="{$avatar_dimensions[0]}" width="{$avatar_dimensions[1]}"><br><br><input type="button" value="{"Change avatar"|localize} !IMPLEMENT ME!" style="float:left;"><br><br></td>
                </tr>

                <tr>
                  <td>{"Full name"|localize:settings}</td>
                  <td>{$full_name|ucfirst}</td>
                </tr>

                <tr>
                  <td>{"Primary group"|localize:settings}</td>
                  <td>{$primary_group}</td>
                </tr>

                <tr>
                  <td>{"Joined"|localize:settings}</td>
                  <td>{$joined}</td>
                </tr>

                <tr>
                  <td>{"Language"|localize:settings}</td>
                  <td>
                    <a href="#" onclick="jQuery('#localize_window').slideToggle(); return false;" id="default_language">{$language|ucfirst}</a>
                    <div id="localize_window" style="display: none;">

                     <form action="?display=settings&action=my_account&changelanguage{$user_uid}" method="POST" id="changelanguage_form">
                       <table style="width: 400px;">
                          <tfoot>
                            <tr>
                                <td colspan="2" class="rounded-foot-left"><em><input type="submit" value="{"Change language"|localize}"></em></td>
                              </tr>
                          </tfoot>
                          <tbody>
                              <tr>
                                  <td>
                                    <select name="language">
                                     {foreach from=$locales_added key=k item=i}
                                          <option value="{$k}">{$k}</option>
                                     {/foreach}
                                    </select>
                                  </td>
                                  <td>{"Set language"|localize:settings}</td>
                              </tr>
                          </tbody>
                       </table>
                     </form>

                    </div>
                  </td>
                </tr>

                {foreach from=$user_fields key=k item=i}
                <tr>
                  <td>{$k}</td>
                  <td>{$i}</td>
                </tr>
                {/foreach}

             </tbody>

            </table>
            <br>

            <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col">{"Permission name"|localize:settings}</th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
                <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>{"Access control list for current user"|localize:settings}</em></td>
                </tr>
            </tfoot>
            <tbody>
                {foreach from=$aclList key=k item=v}
                <tr>
                    <td style="border-right: 0px;">{$v.name}</td>

                    {if $allow_edit_acl == True}
                    <td style="border-left: 0px;"><select id="acl_{$k}" onChange="aclModify('acl_{$k}', '{$k}');" style="float: right; margin: 4px;"><option value="1" {if $v.active == 1}selected{/if}>{"Yes"|localize:messages}</option><option value="0" {if $v.active == 0}selected{/if}>{"No"|localize:messages}</option></td>
                    {else}
                    <td style="border-left: 0px;">{$v.value}</td>
                    {/if}
                </tr>

                {/foreach}
            </tbody>
        </table>