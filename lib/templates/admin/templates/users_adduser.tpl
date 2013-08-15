<script type="text/javascript">

// spinners
var addUser = new panthera.ajaxLoader($('#addUser'));

/**
  * Get link to avatar from upload
  *
  * @author Mateusz Warzyński
  */

function upload_file_callback(link, mime, type, directory, id, description, author)
{
    if(type != 'image')
    {
        alert('{function="localize('Selected file is not a image')"}');
        return false;
    }

    $('#avatar').val(link);
    document.getElementById("avatar_image").src = link;
}

// when page becomes ready
$(document).ready(function () {

    /**
      * Add a new user
      *
      * @author Mateusz Warzyński
      */

    $('#addUserForm').submit(function () {
        panthera.jsonPOST( { data: '#addUserForm', spinner: addUser, success: function (response) {

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


<div class="titlebar">{function="localize('Add new user.', 'settings')"}{include="_navigation_panel"}</div>

            <br>
      <div id="userAdd" style="position: relative;">
       <form action="?display=users&cat=admin&action=add_user" method="POST" id="addUserForm">
            <table class="gridTable">

             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('Key')"}</th>
                    <th scope="col">{function="localize('Value')"}</th>
                </tr>
             </thead>

             <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {function="localize('Add user form', 'users')"}<input type="submit" value="{function="localize('Add user', 'users')"}" style="float: right;"></em></td>
                </tr>
             </tfoot>

             <tbody>
                <tr>
                    <td>{function="localize('Login', 'users')"}</td>
                    <td><input type="text" name="login"></td>
                </tr>

                <tr>
                  <td>{function="localize('Password', 'settings')"}</td>
                  <td> <input type="password" name="passwd" placeholder="{function="localize('Password', 'settings')"}" ><br>
                       <input type="password" name="retyped_passwd" placeholder="{function="localize('Retype password', 'settings')"}" style="margin-top:5px;">
                 </td>
                </tr>

                <tr>
                  <td>{function="localize('Avatar', 'settings')"}</td>
                  <td>
                      <input type="button" value="{function="localize('Upload file', 'gallery')"}" onclick="createPopup('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback', 1300, 550);" style="width: 160px;">
                      <div class="galleryImageFrame" style="margin-top: 7px;">
                        <div class="paGalleryFrameContent">
                            <img src="{$PANTHERA_URL}/images/default_avatar.png" id="avatar_image" style="max-width: 180px; max-height: 220px;">
                        </div>
                      </div>

                      <input type="text" name="avatar" id="avatar" style="display: none;">
                  </td>
                </tr>

                <tr>
                  <td>{function="localize('Full name', 'settings')"}</td>
                  <td><input type="text" name="full_name"></td>
                </tr>

                <tr>
                  <td>{function="localize('Primary group', 'settings')"}</td>
                  <td>
                    <select name="primary_group" style="width: 160px;">
                        {loop="$groups"}
                          <option value="{$value.name}">{$value.name}</option>
                        {/loop}
                    </select>
                  </td>
                </tr>

                <tr>
                  <td>{function="localize('Language', 'settings')"}</td>
                  <td>
                    <select name="language" style="width: 160px;">
                        {loop="$locales_added"}
                          <option value="{$key}">{$key}</option>
                        {/loop}
                    </select>
                 </td>
                </tr>

                <tr>
                  <td>{function="localize('E-mail', 'users')"} <small>(optionally)</small></td>
                  <td><input type="text" name="email" placeholder="user@gmail.com"></td>
                </tr>

                <tr>
                  <td>{function="localize('Jabber', 'users')"} <small>(optionally)</small></td>
                  <td><input type="text" name="jabber" placeholder="user@jabber.org"></td>
                </tr>

             </tbody>
            </table>
         </form>
       </div>