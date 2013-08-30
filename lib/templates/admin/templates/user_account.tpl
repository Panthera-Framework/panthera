<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

// spinners
var spinner = new panthera.ajaxLoader($('#userWindow'));
var acl = new panthera.ajaxLoader($('#aclWindow'));

/**
  * Submit language form
  *
  * @author Damian Kęska
  */

$('#changelanguage_form').submit(function () {
    panthera.jsonPOST({ data: '#changelanguage_form', spinner: spinner, success: function (response) {
            if (response.status == "success")
                navigateTo('?display=users&cat=admin&action=account');
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
    panthera.jsonPOST({ data: '#changepasswd_form', spinner: spinner, success: function (response) {
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
    panthera.jsonPOST({ url: '?display=users&cat=admin&action=account{$user_uid}', data: 'aclname='+name+'&value='+$('#'+id).val(), spinner: acl, success: function (response) {
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

{include="ui.titlebar"}

            <br>

            <div class="msgSuccess" id="userinfoBox_success"></div>
            <div class="msgError" id="userinfoBox_failed"></div>
        <div id="userWindow" style="position: relative;">
            <table class="gridTable">

             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('User', 'users')"}</th>
                    <th scope="col"> </th>
                </tr>
             </thead>

             <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>{function="localize('Informations about user', 'users')"}
                    	<a href="#" onclick="navigateTo('?display=users&cat=admin&action=editUser&uid={$id}')">
                        	<img src="{$PANTHERA_URL}/images/admin/ui/edit.png" style="max-height: 22px; float: right;" alt="{function="localize('Edit', 'users')"}">
                    	</a>
                    </em></td>
                </tr>
             </tfoot>

             <tbody>
                <tr>
                    <td>{function="localize('Login', 'users')"}</td>
                    <td>{$user_login}</td>
                </tr>

                <tr>
                  <td>{function="localize('Password', 'users')"}</td>
                  <td><a href="#" onclick="jQuery('#password_window').slideToggle(); return false;">{function="localize('Change password', 'users')"}</a> <div id="password_window" style="display: none;">

                <form action="?display=users&cat=admin&action=account&changepassword{$user_uid}" method="POST" id="changepasswd_form">
                 <table style="width: 400px; border: 0px; font-size: 12px;">
                    <tfoot>
                        <tr>
                            <td colspan="2" class="rounded-foot-left"><em><input type="submit" value="{function="localize('Change password', 'users')"}"></em></td>
                        </tr>
                    </tfoot>
                    <thead>
                        {if="!isset($dontRequireOld)"}
                        <tr>
                            <td><input type="password" name="old_passwd"> </td>
                            <td>{function="localize('Old password', 'users')"}</td>
                        </tr>
                        {/if}

                        <tr>
                            <td><input type="password" name="new_passwd"> </td>
                            <td>{function="localize('New password', 'users')"}</td>
                        </tr>

                        <tr>
                            <td><input type="password" name="retyped_newpasswd"></td>
                            <td>{function="localize('Retype new password', 'users')"}</td>
                        </tr>
                    </thead>
                 </table>
                </form>

            </div></td>
                </tr>

                <tr>
                  <td>{function="localize('Avatar', 'users')"}</td>
                  <td>
                      <div class="galleryImageFrame">
                        <div class="paGalleryFrameContent">
                            <img src="{$profile_picture}" id="avatar_image" style="max-width: {$avatar_dimensions[1]}px; max-height: {$avatar_dimensions[0]}px;">
                        </div>
                      </div>
                  </td>
                </tr>

                <tr>
                  <td>{function="localize('Full name', 'users')"}</td>
                  <td>{$full_name|ucfirst}</td>
                </tr>

                <tr>
                  <td>{function="localize('Primary group', 'users')"}</td>
                  <td><a href="?display=acl&cat=admin&action=listGroup&group={$primary_group}" class="ajax_link">{$primary_group}</a></td>
                </tr>

                <tr>
                  <td>{function="localize('Joined', 'users')"}</td>
                  <td>{$joined}</td>
                </tr>

                <tr>
                  <td>{function="localize('Language', 'users')"}</td>
                  <td>
                    <a href="#" onclick="jQuery('#localize_window').slideToggle(); return false;" id="default_language">{$language|ucfirst}</a>
                    <div id="localize_window" style="display: none;">

                     <form action="?display=users&cat=admin&action=account&changelanguage{$user_uid}" method="POST" id="changelanguage_form">
                       <table style="width: 400px;">
                          <tfoot>
                            <tr>
                                <td colspan="2" class="rounded-foot-left"><em><input type="submit" value="{function="localize('Change language', 'users')"}"></em></td>
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
                                  <td>{function="localize('Set language', 'users')"}</td>
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
           </div>

            <br>
           <div id="aclWindow" style="position: relative;">
            <table class="gridTable">
            <thead>
                <tr>
                    <th scope="col">{function="localize('Permission name', 'users')"}</th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
                <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>{function="localize('Access control list for current user', 'users')"}</em></td>
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
       </div>