{$site_header}
<script type="text/javascript">

/**
  * Jump to other page in a table
  *
  * @author Damian Kęska
  */

function jumpToAjaxPage(id)
{
    panthera.htmlGET({ url: '?display=users&cat=admin&subaction=show_table&usersPage='+id, success: '#usersDiv' });
}

/**
  * Remove group
  *
  * @author Damian Kęska
  */

function removeGroup(name)
{
    panthera.confirmBox.create('{function="localize('Are you sure you want delete this group?', 'users')"}', function (responseText) {
       if (responseText == 'Yes')
        {
            panthera.jsonPOST( { url: '?display=users&cat=admin&action=removeGroup', data: 'group='+name, spinner: groupSpinner, success: function (response) {

                    if (response.status == "success")
                        $('#group_'+response.name).remove();
                }
            });
        }

    });
}

/**
  * Remove user
  *
  * @author Mateusz Warzyński
  */

function removeUser(id)
{
    panthera.confirmBox.create('{function="localize('Are you sure you want delete this user?', 'users')"}', function (responseText) {
       if (responseText == 'Yes')
        {
            panthera.jsonPOST( { url: '?display=users&cat=admin&action=removeUser', data: 'id='+id, success: function (response) {

                    if (response.status == "success")
                        $('#user_'+id).remove();
                }
            });
        }

    });
}

</script>

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="separatorHorizontal"></div>
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Create user', 'users')"}" onclick="panthera.popup.toggle('element:#newUserPopup')">
        <input type="button" value="{function="localize('Create group', 'users')"}" onclick="panthera.popup.toggle('element:#newGroupPopup')">
    </div>
</div>

<!-- Create user popup -->

<div id="newUserPopup" style="display: none;">
      <script type="text/javascript">
      
        $(document).ready(function () {

            /**
              * Create a new user
              *
              * @author Mateusz Warzyński
              */
            
            $('#addUserForm').submit(function () {
                panthera.jsonPOST( { data: '#addUserForm', success: function (response) {
        
                        if (response.status == "success") {
                            navigateTo('?display=users&cat=admin');
                        }
                    }
                });
                return false;
            });
        });
      </script>
      <form action="?display=users&cat=admin&action=add_user" method="POST" id="addUserForm">
         
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">

             <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Create new user', 'users')"}</p>
                     </td>
                 </tr>
             </thead>
             
             <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Create', 'users')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>

             <tbody>
                <tr>
                    <th>{function="localize('Login', 'users')"}</th>
                    <th><input type="text" name="login"></th>
                </tr>

                <tr>
                  <th>{function="localize('Password', 'users')"}</th>
                  <th> 
                       <input type="password" name="passwd" placeholder="{function="localize('Password', 'users')"}"><br>
                       <input type="password" name="retyped_passwd" placeholder="{function="localize('Retype password', 'users')"}" style="margin-top:5px;" id="retype_passwd">
                  </th>
                </tr>
                
                <tr>
                  <th>{function="localize('Avatar', 'users')"}</th>
                  <th>
                      <input type="button" value="{function="localize('Upload file', 'users')"}" onclick="createPopup('_ajax.php?display=upload&cat=admin&popup=true&callback=upload_file_callback', 1300, 550);" style="width: 160px;">
                      <div class="galleryImageFrame" style="margin-top: 7px;">
                        <div class="paGalleryFrameContent" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                            <img src="{$PANTHERA_URL}/images/default_avatar.png" id="avatar_image" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                        </div>
                      </div>

                      <input type="text" name="avatar" id="avatar_link" style="display: none;">
                  </th>
                </tr>

                <tr>
                  <th>{function="localize('Full name', 'users')"}</th>
                  <th><input type="text" name="full_name"></th>
                </tr>

                <tr>
                  <th>{function="localize('Primary group', 'users')"}</th>
                  <th>
                    <select name="primary_group" style="width: 160px;">
                        {loop="$groups"}
                          <option value="{$value.name}">{$value.name}</option>
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
                  <th><input type="text" name="email" placeholder="user@gmail.com"></th>
                </tr>

                <tr>
                  <th>{function="localize('Jabber', 'users')"} <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="jabber" placeholder="user@jabber.org"></th>
                </tr>

             </tbody>
            </table>
         </form>
</div>

<!-- New group popup -->

<div id="newGroupPopup" style="display: none;">
    <form action="?display=users&cat=admin&action=createGroup" method="POST" id="createGroupForm">
         
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Create new group', 'users')"}</p>
                     </td>
                 </tr>
            </thead>
             
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Add', 'users')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>

            <tbody>
                <tr>
                    <th>{function="localize('Name', 'users')"}</th>
                    <th><input type="text" name="name" style="width: 95%;"></th>
                </tr>
                    <th>{function="localize('Description', 'users')"}</th>
                    <th><input type="text" name="description" style="width: 95%;"></th>
                </tr>
            </tbody>
         </table>
    </form>
</div>
    
<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">
    <script type="text/javascript">
      $(document).ready(function () {

            /**
              * Add a new group
              *
              * @author Damian Kęska
              */
        
            $('#createGroupForm').submit(function () {
                panthera.jsonPOST( { data: '#createGroupForm', spinner: groupSpinner, success: function (response) {
        
                        if (response.status == "success")
                        {
                            //$('.groupTableItem').remove();
                            $('#groupTableBody').prepend('<tr id="group_'+response.name+'" class="groupTableItem"><td><a href="?display=acl&cat=admin&action=listGroup&group='+response.name+'" class="ajax_link">'+response.name+'</a></td><td>'+response.description+'</td><td><input type="button" value="{function="localize('Remove', 'acl')"}" onclick="removeGroup(\''+response.name+'\');"></td>');
                        } else {
                            if (response.message != undefined)
                            {
                                w2alert(response.message, '{function="localize('Warning', 'acl')"}');
                            }
        
                        }
                    }
                });
                return false;
            });
      });
    </script>
    
    <div>
        
      <!-- Groups -->
        <table style="display: inline-block; position: relative;" id="groupsTable">            
            <thead>
                <tr>
                    <th>{function="localize('Group name', 'acl')"}</th>
                    <th colspan="2">{function="localize('Description', 'acl')"}</th>
                </tr>
            </thead>
            
            <tbody id="groupTableBody">
            {loop="$groups"}
                <tr id="group_{$value.name}" class="groupTableItem">
                    <td><a href="?display=acl&cat=admin&action=listGroup&group={$value.name}" class="ajax_link">{$value.name}</a></td>
                    <td>{$value.description}</td>
                    <td>
                        <a href="#" onclick="removeGroup('{$value.name}');">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove', 'acl')"}">
                        </a>
                    </td>
                </tr>
            {/loop}

                <form action="?display=users&cat=admin&action=createGroup" method="POST" id="createGroupForm">
                <tr id="groupsAddTr" style="display: none;">
                    <td><input type="text" name="name" style="width: 95%;"></td><td><input type="text" name="description" style="width: 95%;"></td><td><input type="submit" value="{function="localize('Add new group', 'acl')"}"></td>
                </tr>
                </form>
            </tbody>
        </table>
        
        
      <!-- Users -->   
        <table style="display: inline-block; position: relative;" id="usersTable">
            <thead>
                 <tr>
                     <th></th>
                     <th>{function="localize('Name', 'users')"}</th>
                     <th>{function="localize('Primary group', 'users')"}</th>
                     <th colspan="2">{function="localize('Default language', 'users')"}</th>
                 </tr>
            </thead>
            
            <tfoot style="background-color: transparent;">
               <tr>
                 <td colspan="7" class="pager">{$uiPagerName="users"}
                   {include="ui.pager"}
                 </td>
               </tr>
            </tfoot>
            
            <tbody>
              {loop="$users_list"}
                 <tr id="user_{$value.login}"}>
                    <td style="padding-left: 15px; padding-right: 15px;"><img src="{$value.avatar}" style="max-height: 30px; max-width: 23px;"></td>
                    <td {if="$value.banned"}style="text-decoration: line-through;"{/if}>{if="$view_users == True"}<a href='?display=users&cat=admin&action=account&uid={$value.id}' class='ajax_link'>{$value.name}</a>{else}{$value.name}{/if}</td>
                    <td><a href="?display=acl&cat=admin&action=listGroup&group={$value.primary_group}" class="ajax_link">{$value.primary_group}</a></td>
                    <td>{$value.language|ucfirst}</td>
                    <td>
                        <a href="#" onclick="removeUser('{$value.login}');">
                            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                        </a>
                    </td>
                 </tr>
              {/loop}
            </tbody>
       </table>
       
      </div>
    </div>
        
<br><br>