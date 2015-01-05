{$site_header}
<script type="text/javascript">

// spinners
var editUser = new panthera.ajaxLoader($('#editPopup'));

/**
 * Get link to avatar from upload
 *
 * @author Mateusz Warzyński
 */
function getAvatar(link, id)
{
    $('#avatar_image').attr('src', link);
    $("#avatar_link").attr('value', link);
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
    	{if="$userSwitchable"}<form action="?display=users&cat=admin&action=switchUser&uid={$user->id}&_bypass_x_requested_with" method="POST" style="float: right; margin-left: 5px;"><input type="hidden" name="uid" value="{$user->id}"><input type="submit" value="{function="localize('Login as this user', 'users')"}"></form>{/if}
        {if="$permissions.canBlockUser"}<input type="button" value="{function="localize('Ban', 'users')"}" onclick="panthera.popup.toggle('element:#banUser', 'banUser')" style="margin-right: 1px;">{/if}
        {if="$permissions.canSeePermissions"}<input type="button" value="{function="localize('Permissions', 'users')"}" onclick="panthera.popup.toggle('element:#managePermissions', 'managePermissions')">{/if}
        <input type="button" value="{function="localize('Edit', 'users')"}" onclick="panthera.popup.toggle('element:#editPopup', 'editUser')">
    </div>
</div>


{if="$permissions.canBlockUser"}
<!-- User banning popup -->
<div id="banUser" style="display: none;">
    <script type="text/javascript">
    /**
     * Toggle value of ban in user attributes
     *
     * @author Mateusz Warzyński
     */
    function toggleBan()
    {
        panthera.jsonPOST({ url: '?display=users&cat=admin&action=ban&uid={$user->id}', data: '', success: function (response) {
              if (response.status == "success")
              {
                  navigateTo("?display=users&cat=admin&action=account&uid={$user->id}");
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
                           {if="$user->isBanned()"}
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
                        <input type="button" value="{function="localize('No')"}" onclick="panthera.popup.close('banUser')" style="float: left; margin-left: 30px;">
                        <input type="button" value="{function="localize('Yes')"}" style="float: right; margin-right: 30px;" onclick="toggleBan()">
                    </td>
                </tr>
            </tfoot>
        </table>
</div>
{/if}

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
                            navigateTo("?display=users&cat=admin&action=account&uid={$user->id}");
        
                        }
                    }
                });
                return false;
            });
        });
      </script>
      
      <form action="?display=users&cat=admin&action=editUser" method="POST" id="editUserForm">
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
             <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 20px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Edit user', 'users')"}</p>
                     </td>
                 </tr>
             </thead>
             
             <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close('editUser')" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>

             <tbody>
                <tr>
                    <input type="text" name="uid" value="{$user->id}" style="display: none;">
                    <th>{function="localize('Login', 'users')"}:</th>
                    <th><input type="text" name="login" value="{$user->login}" disabled></th>
                </tr>

                <tr>
                  <th>{function="localize('Password', 'users')"}:</th>
                  <th>
                     {if="!isset($dontRequireOld)"}
                       <input type="password" name="old_passwd" placeholder="{function="localize('Old password', 'users')"}"><br>
                     {/if}
                       <input type="password" name="passwd" placeholder="{function="localize('Password', 'users')"}"><br>
                       <input type="password" name="retyped_passwd" placeholder="{function="localize('Retype password', 'users')"}" style="margin-top:5px;" id="retype_passwd">
                  </th>
                </tr>

                <tr>
                    <th>{function="localize('Active', 'users')"}:</th>
                    <th><input type="checkbox" name="active" value="1"{if="$user->active"} checked{/if}></th>
                </tr>
                
                <tr>
                  <th>{function="localize('Avatar', 'users')"}:</th>
                  <th>
                      <input type="button" value="{function="localize('Change avatar', 'users')"}" onclick="panthera.popup.toggle('?display=avatars&cat=admin&action=displayAvatars&callback=getAvatar', 'avatarPopup');" style="width: 160px;">
                      <div class="galleryImageFrame" style="margin-top: 7px;">
                        <div class="paGalleryFrameContent" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                            <img src="{$profile_picture}" id="avatar_image" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                        </div>
                      </div>

                      <input type="text" name="avatar" id="avatar_link" style="display: none;">
                  </th>
                </tr>

                <tr>
                  <th>{function="localize('Full name', 'users')"}:</th>
                  <th><input type="text" name="full_name" value="{$user->full_name}"></th>
                </tr>
                
                {if="$permissions.canEditOthers"}
                <tr>
                  <th>{function="localize('Primary group', 'users')"}:</th>
                  <th>
                    <div class="select" style="margin-top: 14px; margin-left: 3px; width: 208px;">
                     <select name="primary_group" style="width: 210px;">
                       {loop="$groups"}
                        <option value="{$value.group_id}" {if="$value.group_id == $user->primary_group"} selected {/if}>{$value.name}</option>
                       {/loop}
                     </select>
                    </div>
                  </th>
                </tr>
                {/if}
                
                <tr>
                  <th>{function="localize('Language', 'users')"}:</th>
                  <th>
                    <div class="select" style="margin-top: 14px; margin-left: 3px; width: 208px;">
                     <select name="language" style="width: 210px;">
                       {loop="$locales_added"}
                        <option{if="$key == $user->language"} selected {/if}>{$key}</option>
                       {/loop}
                     </select>
                    </div>
                 </th>
                </tr>
                
                <tr>
                  <th>{function="localize('Gender', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
                  <th>
                    <div class="select" style="margin-top: 14px; margin-left: 3px; width: 208px;">
                     <select name="gender" style="width: 210px;">
                        <option {if="$user->gender == 'male'"} selected {/if} value="male">{function="localize('male', 'users')"}</option>
                        <option {if="$user->gender == 'female'"} selected {/if} value="female">{function="localize('female', 'users')"}</option>
                     </select>
                    </div>
                 </th>
                </tr>
                
                <tr>
                  <th>{function="localize('Address', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="address" placeholder="" value="{$user->address}"></th>
                </tr>
                
                <tr>
                  <th>{function="localize('City', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="city" placeholder="Warsaw" value="{$user->city}"></th>
                </tr>
                
                <tr>
                  <th>{function="localize('Postal code', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="postal_code" placeholder="10-200" value="{$user->postal_code}"></th>
                </tr>
                
                <tr>
                  <th>{function="localize('E-mail', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="email" placeholder="user@gmail.com" value="{$user->email}"></th>
                </tr>

                <tr>
                  <th>{function="localize('Jabber', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="jabber" placeholder="user@jabber.org" value="{$user->jabber}"></th>
                </tr>

                {if="$permissions.canEditOthers"}
                <tr>
                  <th>{function="localize('Facebook ID', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
                  <th><input type="text" name="facebookID" placeholder="100000000000000" value="{$facebookID}"></th>
                </tr>
                {/if}
             </tbody>
            </table>
         </form>
</div>

{if="$permissions.canSeePermissions"}
<!-- Manage permissions popup -->
<div id="managePermissions" style="display: none;">
       <script type="text/javascript">
        function aclModify(id, name)
        {
            var bool =  $('#acl_'+id).val();
            var yes = $('#yes_text').val();
            var no = $('#no_text').val();
            
            panthera.jsonPOST({ url: '?display=users&cat=admin&action=account{$user_uid}', data: 'aclname='+name+'&value='+bool, success: function (response) {
                  if (response.status == "success")
                  {
                      if (response.value == false) {
                        $('#text_'+id).text(no);
                        $('#text_'+id).css('color', '#941111');
                        $('#acl_'+id).val("1");
                      } else {
                        $('#text_'+id).text(yes);
                        $('#text_'+id).css('color', "#14D614");
                        $('#acl_'+id).val("0");
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
                           <a href="#" onClick="aclModify('{$key|md5}', '{$key}');" id="text_{$key|md5}" {if="$value.value == localize('Yes')"} style="color: #14D614;" {else} style="color: #941111;" {/if}>{$value.value}</a>
                    </th>
                </tr>
                
                <input type="text" id="acl_{$key|md5}" {if="$value.value == 'Yes'"} value="0" {else} value="1" {/if} style="display: none;">
              {/loop}
            </tbody>
         </table>
         
         <input type="text" value="{function="localize('No')"}" id="no_text" style="display: none;">
         <input type="text" value="{function="localize('Yes')"}" id="yes_text" style="display: none;">
</div>
{/if}

<!-- Ajax content -->
<div class="ajax-content" style="text-align: center;">
      <table style="display: inline-block; position: relative; margin-bottom: 60px;" id="userTable">
             <thead>
                <tr>
                    <th colspan="2" style="min-width: 150px;">{$user->getName()}</th>
                </tr>
             </thead>

             <tbody class="hovered">
                <tr>
                    <td>{function="localize('Login', 'users')"}:</td>
                    <td><p>{$user->login}</p></td>
                </tr>

                {if="$profile_picture"}
                <tr>
                  <td>{function="localize('Avatar', 'users')"}:</td>
                  <td>
                      <div class="galleryImageFrame" style="margin-top: 7px; margin-bottom: 7px;">
                        <div class="paGalleryFrameContent" style="background: none; height: inherit; width: inherit;">
                            <img src="{$profile_picture}" id="avatar_image" style="max-width: {$avatar_dimensions[0]}px; max-height: {$avatar_dimensions[1]}px;">
                        </div>
                      </div>
                  </td>
                </tr>
                {/if}

                <tr>
                  <td>{function="localize('Full name', 'users')"}:</td>
                  <td><p>{$user->full_name}</p></td>
                </tr>

                <tr>
                  <td>{function="localize('Primary group', 'users')"}:</td>
                  <td><p>{$user->group_name}</p></td>
                </tr>

                <tr>
                  <td>{function="localize('Language', 'users')"}:</td>
                  <td><p>{$user->language|ucfirst}</p></td>
                </tr>
                
                <tr>
                  <td>{function="localize('Joined', 'users')"}:</td>
                  <td><p>{$user->joined}</p></td>
                </tr>
                
                <tr>
                  <td>{function="localize('Status', 'users')"}:</td>
                  <td><p>{if="!$user->isBanned()"}<span style="color: green;"> {function="localize('Normal', 'users')"} </span> {else} <span style="color: red;"> {function="localize('Blocked  ', 'users')"} </span> {/if} </p></td>
                </tr>
                
              {if="$user->gender"}
                <tr>
                  <td>{function="localize('Gender', 'users')"} <small>({function="localize('optionally', 'users')"})</small>:</td>
                  <td><p>{$user->gender}</p></td>
                </tr>
              {/if}
              
              {if="$user->address"}
                <tr>
                  <td>{function="localize('Address', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$user->address}</p></td>
                </tr>
              {/if}
              
              {if="$user->city"}
                <tr>
                  <td>{function="localize('City', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$user->city}</p></td>
                </tr>
              {/if}
              
              {if="$user->postal_code"}
                <tr>
                  <td>{function="localize('Postal code', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$user->postal_code}</p></td>
                </tr>
              {/if}

              {if="$user->email"}
                <tr>
                  <td>{function="localize('E-mail', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$user->email}</p></td>
                </tr>
              {/if}

              {if="$user->jabber"}
                <tr>
                  <td>{function="localize('Jabber', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$user->jabber}</p></td>
                </tr>
              {/if}
              
              {if="$facebookID"}
                <tr>
                  <td>{function="localize('Facebook ID', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></td>
                  <td><p>{$facebookID}</p></td>
                </tr>
              {/if}
              
              {if="count($user_fields) > 0"}
               {loop="$user_fields"}
                <tr>
                  <td>{$key}</td>
                  <td>{$value}</td>
                </tr>
               {/loop}
              {/if}

             </tbody>
            </table>
            
            {if="$lastloginHistory"}
            	<table style="display: inline-block; position: relative;">
            		<thead>
            			<tr>
            				<th colspan="3">{function="localize('Last login history', 'users')"}</th>
            			</tr>
            		</thead>
            		
            		<tbody class="hovered">
            			{loop="$lastloginHistory"}
            			<tr>
            				<td>{$value.date}{if="$value.retries"} ({function="slocalize('%s retries', 'users', $value.retries)"}){/if}</td>
            				<td title="{$value.useragent}">{$value.browser}{if="$value.system"} ({$value.system}){/if}{if="$value.location"}, {$value.location}{/if}{if="$value.ip"}, {$value.ip}{/if}</td>
            			</tr>
            			{/loop}
            		</tbody>
            	</table>
            {/if}
</div>
