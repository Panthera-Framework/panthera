<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Submit language form
  *
  * @author Damian Kęska
  */

$('#changelanguage_form').submit(function () {
    panthera.jsonPOST({ data: '#changelanguage_form', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=settings&action=my_account');
        }
    });

    return false;

});

/**
  * Submit change password form
  *
  * @author Damian Kęska
  */

$('#changepasswd_form').submit(function () {
    panthera.jsonPOST({ data: '#changepasswd_form', messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
            {
                jQuery('#change_success').slideDown();
                setTimeout('jQuery(\'#change_success\').slideUp();', 5000);
                jQuery('#password_window').hide();
            }
        }
    });

    return false;

});

function aclModify(id, name)
{
    panthera.jsonPOST({ url: '?display=settings&action=my_account{$user_uid}', data: 'aclname='+name+'&value='+$('#'+id).val(), success: function (response) {
          if (response.status == "success")
          {
          } else {
              jQuery('#change_error').slideDown();
              jQuery('#change_error').html(response.message);
          }
        }
    });
}
</script>

<div class="titlebar"><span class="titleBarIcons"><img src="{$PANTHERA_URL}/images/default_avatar.png" style="width: 28px"></a></span>{function="localize('Panel with informations about user.', 'settings')"}{include="_navigation_panel.tpl"}</div>

            <br>

            <div class="msgSuccess" id="userinfoBox_success"></div>
            <div class="msgError" id="userinfoBox_failed"></div>

            <table class="gridTable">

             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('User', 'settings')"}</th>
                    <th scope="col"> </th>
                </tr>
             </thead>

             <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>{function="localize('Informations about user', 'settings')"}</em></td>
                </tr>
             </tfoot>

             <tbody>
                <tr>
                    <td>{function="localize('Login', 'settings')"}</td>
                    <td>{$user_login}</td>
                </tr>

                <tr>
                  <td>{function="localize('Password', 'settings')"}</td>
                  <td><a href="#" onclick="jQuery('#password_window').slideToggle(); return false;">{function="localize('Change password', 'settings')"}</a> <div id="password_window" style="display: none;">

                <form action="?display=settings&action=my_account&changepassword{$user_uid}" method="POST" id="changepasswd_form">
                 <table style="width: 400px; border: 0px; font-size: 12px;">
                    <tfoot>
                        <tr>
                            <td colspan="2" class="rounded-foot-left"><em><input type="submit" value="{function="localize('Change password')"}"></em></td>
                        </tr>
                    </tfoot>
                    <thead>
                        {if="!isset($dontRequireOld)"}
                        <tr>
                            <td><input type="password" name="old_passwd"> </td>
                            <td>{function="localize('Old password', 'settings')"}</td>
                        </tr>
                        {/if}

                        <tr>
                            <td><input type="password" name="new_passwd"> </td>
                            <td>{function="localize('New password', 'settings')"}</td>
                        </tr>

                        <tr>
                            <td><input type="password" name="retyped_newpasswd"></td>
                            <td>{function="localize('Retype new password', 'settings')"}</td>
                        </tr>
                    </thead>
                 </table>
                </form>

            </div></td>
                </tr>

                <tr>
                  <td>{function="localize('Avatar', 'settings')"}</td>
                  <td><img src="{$profile_picture}" height="{$avatar_dimensions[0]}" width="{$avatar_dimensions[1]}"><br><br><!--<input type="button" value="{function="localize('Change avatar')"} !IMPLEMENT ME!" style="float:left;">--><br><br></td>
                </tr>

                <tr>
                  <td>{function="localize('Full name', 'settings')"}</td>
                  <td>{$full_name|ucfirst}</td>
                </tr>

                <tr>
                  <td>{function="localize('Primary group', 'settings')"}</td>
                  <td><a href="?display=acl&action=listGroup&group={$primary_group}" class="ajax_link">{$primary_group}</a></td>
                </tr>

                <tr>
                  <td>{function="localize('Joined', 'settings')"}</td>
                  <td>{$joined}</td>
                </tr>

                <tr>
                  <td>{function="localize('Language', 'settings')"}</td>
                  <td>
                    <a href="#" onclick="jQuery('#localize_window').slideToggle(); return false;" id="default_language">{$language|ucfirst}</a>
                    <div id="localize_window" style="display: none;">

                     <form action="?display=settings&action=my_account&changelanguage{$user_uid}" method="POST" id="changelanguage_form">
                       <table style="width: 400px;">
                          <tfoot>
                            <tr>
                                <td colspan="2" class="rounded-foot-left"><em><input type="submit" value="{function="localize('Change language')"}"></em></td>
                              </tr>
                          </tfoot>
                          <tbody>
                              <tr>
                                  <td>
                                    <select name="language">
                                     {loop="$locales_added"}
                                          <option value="{$key}">{$key}</option>
                                     {/loop}
                                    </select>
                                  </td>
                                  <td>{function="localize('Set language', 'settings')"}</td>
                              </tr>
                          </tbody>
                       </table>
                     </form>

                    </div>
                  </td>
                </tr>

                {loop="$user_fields"}
                <tr>
                  <td>{$k}</td>
                  <td>{$i}</td>
                </tr>
                {/loop}

             </tbody>

            </table>
            <br>

            <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col">{function="localize('Permission name', 'settings')"}</th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
                <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>{function="localize('Access control list for current user', 'settings')"}</em></td>
                </tr>
            </tfoot>
            <tbody>
                {loop="$aclList"}
                <tr>
                    <td style="border-right: 0px;">{$value.name}</td>

                    {if="$allow_edit_acl == True"}
                    <td style="border-left: 0px;"><select id="acl_{$key}" onChange="aclModify('acl_{$key}', '{$key}');" style="float: right; margin: 4px;"><option value="1" {if="$value.active == 1"}selected{/if}>{function="localize('Yes', 'messages')"}</option><option value='0' {if="$value.active == 0"}selected{/if}>{function="localize('No', 'messages')"}</option></td>
                    {else}
                    <td style="border-left: 0px;">{$value.value}</td>
                    {/if}
                </tr>

                {/loop}
            </tbody>
        </table>
