{$site_header}
<script type="text/javascript">

// spinners
var editUser = new panthera.ajaxLoader($('#editPopup'));

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

</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Ban', 'users')"}" onclick="panthera.popup.toggle('element:#banUser')">
        <input type="button" value="{function="localize('Permissions', 'users')"}" onclick="panthera.popup.toggle('element:#managePermissions')">
        <input type="button" value="{function="localize('Edit', 'users')"}" onclick="panthera.popup.toggle('element:#editPopup')">
    </div>
</div>


<!-- New category popup -->

<div id="banUser" style="display: none;">
    <script type="text/javascript">
    /**
      * Toggle value of ban in user attributes
      *
      * @author Mateusz Warzyński
      */
    
    function toggleBan()
    {
        panthera.jsonPOST({ url: '?display=users&cat=admin&action=account&uid={$id}', data: 'ban=true', success: function (response) {
              if (response.status == "success")
              {
                  navigateTo("?display=users&cat=admin&action=account&uid={$id}");
              }
            }
        });
    }
    </script>

        <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px;">
                           {if="$isBanned == True"}
                             {function="localize('Do you really want to unban this user?', 'users')"}
                           {else}
                             {function="localize('Do you really want to ban this user?', 'users')"}
                           {/if}
                        </p>
                    </td>
                </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('No')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="button" value="{function="localize('Yes')"}" style="float: right; margin-right: 30px;" onclick="toggleBan()">
                    </td>
                </tr>
            </tfoot>
        </table>
</div>

<!-- Edit user popup -->

<div id="editPopup" style="display: none; position: relative;">
      <script type="text/javascript">
      
        $(document).ready(function () {

            /**
              * Edit existing user
              *
              * @author Mateusz Warzyński
              */
        
            $('#editUserForm').submit(function () {
                panthera.jsonPOST( { data: '#editUserForm', spinner: editUser, success: function (response) {
        
                        if (response.status == "success") {
                            navigateTo("?display=users&cat=admin&action=account&uid={$id}");
        
                        }
                    }
                });
                return false;
            });
        });
      </script>
      
      <form action="?display=users&cat=admin&action=edit_user" method="POST" id="editUserForm">
         
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">

             <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Edit user', 'users')"}</p>
                     </td>
                 </tr>
             </thead>
             
             <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>

             <tbody>
                <tr>
                    <input type="text" name="uid" value="{$id}" style="display: none;">
                    <th>{function="localize('Login', 'users')"}</th>
                    <th><input type="text" name="login" value="{$user_login}" disabled></th>
                </tr>

                <tr>
                  <th>{function="localize('Password', 'users')"}</th>
                  <th>
                     {if="!isset($dontRequireOld)"}
                       <input type="password" name="old_passwd" placeholder="{function="localize('Old password', 'users')"}"><br>
                     {/if}
                       <input type="password" name="passwd" placeholder="{function="localize('Password', 'users')"}"><br>
                       <input type="password" name="retyped_passwd" placeholder="{function="localize('Retype password', 'users')"}" style="margin-top:5px;" id="retype_passwd">
                  </th>
                </tr>
                
                <tr>
                  <th>{function="localize('Avatar', 'users')"}</th>
                  <th>
                      <input type="button" value="{function="localize('Upload file', 'users')"}" onclick="createPopup('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback', 1300, 550);" style="width: 160px;">
                      <div class="galleryImageFrame" style="margin-top: 7px;">
                        <div class="paGalleryFrameContent">
                            <img src="{$profile_picture}" id="avatar_image" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                        </div>
                      </div>

                      <input type="text" name="avatar" id="avatar_link" style="display: none;">
                  </th>
                </tr>

                <tr>
                  <th>{function="localize('Full name', 'users')"}</th>
                  <th><input type="text" name="full_name" value="{$full_name}"></th>
                </tr>

                <tr>
                  <th>{function="localize('Primary group', 'users')"}</th>
                  <th>
                    <select name="primary_group" style="width: 160px;">
                        {loop="$groups"}
                          <option value="{$value.name}" {if="$value.name == $primary_group"} selected {/if}>{$value.name}</option>
                        {/loop}
                    </select>
                  </th>
                </tr>

                <tr>
                  <th>{function="localize('Language', 'users')"}</th>
                  <th>
                    <select name="language" style="width: 160px;">
                        {loop="$locales_added"}
                          <option value="{$key}">{$key}</option>
                        {/loop}
                    </select>
                 </th>
                </tr>

                <tr>
                  <th>{function="localize('E-mail', 'users')"} <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="email" placeholder="user@gmail.com" value="{$email}"></th>
                </tr>

                <tr>
                  <th>{function="localize('Jabber', 'users')"} <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="jabber" placeholder="user@jabber.org" value="{$jabber}"></th>
                </tr>

             </tbody>
            </table>
         </form>
</div>

<!-- Manage permissions popup -->

<div id="managePermissions" style="display: none;">
       <script type="text/javascript">
        function aclModify(id, name)
        {
            var bool =  $('#'+id).val();
            
            panthera.jsonPOST({ url: '?display=users&cat=admin&action=account{$user_uid}', data: 'aclname='+name+'&value='+bool, success: function (response) {
                  if (response.status == "success")
                  {
                      if (response.value == false) {
                        $('#text_'+name).text('No');
                        $('#text_'+name).css('color', '#941111');
                        $('#'+id).val("1");
                      } else {
                        $('#text_'+name).text('Yes');
                        $('#text_'+name).css('color', "#14D614");
                        $('#'+id).val("0");
                      }
                  } else {
                      jQuery('#change_error').slideDown();
                      jQuery('#change_error').html(response.message);
                  }
                }
            });
        }
       </script>
    
    
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Permissions', 'users')"}</p>
                     </td>
                 </tr>
            </thead>

            <tbody>
              {loop="$aclList"}
                <tr style="border-bottom: 1px solid #404c5a;">
                    <th style="padding-top: 0px; padding-bottom: 0px; font-size: 11px;">{$value.name}</th>
                    <th style="padding-top: 0px; padding-bottom: 0px; width: 22px;">
                           <a href="#" onClick="aclModify('acl_{$key}', '{$key}');" id="text_{$key}" {if="$value.value == 'Yes'"} style="color: #14D614;" {else} style="color: #941111;" {/if}>{$value.value}</a>
                    </th>
                </tr>
                
                <input type="text" id="acl_{$key}" {if="$value.value == 'Yes'"} value="0" {else} value="1" {/if} style="display: none;">
              {/loop}
            </tbody>
         </table>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Ajax content -->
<div class="ajax-content" style="text-align: center;">
      <table style="display: inline-block; position: relative;" id="userTable">

             <thead>
                <tr>
                    <th scope="col" style="min-width: 150px;"></th>
                    <th scope="col" style="min-width: 300px;"></th>
                </tr>
             </thead>

             <tbody>
                <tr>
                    <td>{function="localize('Login', 'users')"}</td>
                    <td><p>{$user_login}</p></td>
                </tr>
                
                <tr>
                  <td>{function="localize('Avatar', 'users')"}</td>
                  <td>
                      <div class="galleryImageFrame" style="margin-top: 7px; margin-bottom: 7px;">
                        <div class="paGalleryFrameContent">
                            <img src="{$profile_picture}" id="avatar_image" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                        </div>
                      </div>
                  </td>
                </tr>

                <tr>
                  <td>{function="localize('Full name', 'users')"}</td>
                  <td><p>{$full_name}</p></td>
                </tr>

                <tr>
                  <td>{function="localize('Primary group', 'users')"}</td>
                  <td><p>{$primary_group}</p></td>
                </tr>

                <tr>
                  <td>{function="localize('Language', 'users')"}</td>
                  <td><p>{$language}</p></td>
                </tr>
                
                <tr>
                  <td>{function="localize('Joined', 'users')"}</td>
                  <td><p>{$joined}</p></td>
                </tr>
                
                <tr>
                  <td>{function="localize('Status', 'users')"}</td>
                  <td><p>{if="$isBanned == 0"}<span style="color: green;"> {function="localize('Normal', 'users')"} </span> {else} <span style="color: red;"> {function="localize('Blocked  ', 'users')"} </span> {/if} </p></td>
                </tr>

              {if="$email"}
                <tr>
                  <td>{function="localize('E-mail', 'users')"} <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$email}</p></td>
                </tr>
              {/if}

              {if="$jabber"}
                <tr>
                  <td>{function="localize('Jabber', 'users')"} <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$jabber}</p></td>
                </tr>
              {/if}

             </tbody>
            </table>
</div>
