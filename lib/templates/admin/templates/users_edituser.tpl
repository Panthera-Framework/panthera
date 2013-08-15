<script type="text/javascript">

// spinners
var editUser = new panthera.ajaxLoader($('#editUser'));

/**
  * Get link to avatar from upload
  *
  * @author Mateusz Warzyński
  */

function upload_file_callback(link, mime, type, directory, id, description, author)
{
    if (type != 'image') {
        alert('{function="localize('Selected file is not a image')"}');
        return false;
    }

	$('#avatar_image').attr('src', link);
    $("#avatar_link").val(link);
}

// when page becomes ready
$(document).ready(function () {

    /**
      * Add a new user
      *
      * @author Mateusz Warzyński
      */

    $('#editUserForm').submit(function () {
        panthera.jsonPOST( { data: '#editUserForm', spinner: editUser, success: function (response) {

                if (response.status == "success") {
                    navigateTo('?display=users&cat=admin&action=account&uid={$id}');

                } else {

                    if (response.message != undefined)
                        w2alert(response.message, '{function="localize('Warning', 'acl')"}');
                }
            }
        });
        return false;
    });
    
    $('#addUserForm').submit(function () {
        panthera.jsonPOST( { data: '#addUserForm', spinner: editUser, success: function (response) {

                if (response.status == "success") {
                    navigateTo('?display=users&cat=admin');

                } else {

                    if (response.message != undefined)
                        w2alert(response.message, '{function="localize('Warning', 'acl')"}');
                }
            }
        });
        return false;
    });
});


</script>


<div class="titlebar">{if="$action == edit"}{function="localize('Edit existing user', 'users')"}{else}{function="localize('Add new user', 'users')"}{/if}.{include="_navigation_panel"}</div>

            <br>
      <div id="editUser" style="position: relative;">
       <form {if="$action == edit"} action="?display=users&cat=admin&action=edit_user" method="POST" id="editUserForm" {else} action="?display=users&cat=admin&action=add_user" method="POST" id="addUserForm" {/if}>
            <table class="gridTable">

             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('Key')"}</th>
                    <th scope="col">{function="localize('Value')"}</th>
                </tr>
             </thead>

             <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {if="$action == 'edit'"}{function="localize('Edit user form', 'users')"}{else}{function="localize('Add user form', 'users')"}{/if}<input type="submit" value="{if="$action == 'edit'"}{function="localize('Save')"}{else}{function="localize('Add user', 'users')"}{/if}" style="float: right;"></em></td>
                </tr>
             </tfoot>

             <tbody>
                <tr>
                    <td>{function="localize('Login', 'users')"}</td>
                    <td>
                      {if="$action == 'edit'"}
                        <p>{$user_login}</p>
                        <input type="text" name="uid" value="{$id}" style="display: none;">
                      {else}
                    	<input type="text" name="login">
                      {/if}
                    </td>
                </tr>

                <tr>
                  <td>{function="localize('Password', 'users')"}</td>
                  <td> 
                  	   <input type="password" name="passwd" {if="$action == edit"} value="********" {/if} placeholder="{function="localize('Password', 'users')"}" onfocus="this.value = '';  {if="$action == 'edit'"}  $('#retype_passwd').slideDown(); {/if}"><br>
                       <input type="password" name="retyped_passwd" placeholder="{function="localize('Retype password', 'users')"}" style="margin-top:5px; {if="$action == edit"}  display: none; {/if}" id="retype_passwd">
                  </td>
                </tr>
                
                <tr>
                  <td>{function="localize('Avatar', 'users')"}</td>
                  <td>
                      <input type="button" value="{function="localize('Upload file', 'users')"}" onclick="createPopup('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback', 1300, 550);" style="width: 160px;">
                      <div class="galleryImageFrame" style="margin-top: 7px;">
                        <div class="paGalleryFrameContent">
                            <img {if="$action == 'edit'"} src="{$profile_picture}" {else} src="{$PANTHERA_URL}/images/default_avatar.png" {/if} id="avatar_image" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                        </div>
                      </div>

                      <input type="text" name="avatar" id="avatar_link" style="display: none;">
                  </td>
                </tr>

                <tr>
                  <td>{function="localize('Full name', 'users')"}</td>
                  <td><input type="text" value="{$full_name}" name="full_name"></td>
                </tr>

                <tr>
                  <td>{function="localize('Primary group', 'users')"}</td>
                  <td>
                    <select name="primary_group" style="width: 160px;">
                        {loop="$groups"}
                          <option value="{$value.name}" {if="$value.name == $primary_group"} selected {/if}>{$value.name}</option>
                        {/loop}
                    </select>
                  </td>
                </tr>

                <tr>
                  <td>{function="localize('Language', 'users')"}</td>
                  <td>
                    <select name="language" style="width: 160px;">
                        {loop="$locales_added"}
                          <option value="{$key}" {if="$key == $language"} selected {/if}>{$key}</option>
                        {/loop}
                    </select>
                 </td>
                </tr>

                <tr>
                  <td>{function="localize('E-mail', 'users')"} <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><input type="text" name="email" value="{$email}" placeholder="user@gmail.com"></td>
                </tr>

                <tr>
                  <td>{function="localize('Jabber', 'users')"} <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><input type="text" name="jabber" value="{$jabber}" placeholder="user@jabber.org"></td>
                </tr>

             </tbody>
            </table>
         </form>
       </div>