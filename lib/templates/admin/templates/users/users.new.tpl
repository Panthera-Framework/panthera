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
<form action="?display=users&cat=admin&action=addUser" method="POST" id="addUserForm">
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
            <th>{function="localize('Login', 'users')"}:</th>
            <th><input type="text" name="login"></th>
        </tr>

        <tr>
            <th>{function="localize('Password', 'users')"}:</th>
            <th>
                <input type="password" name="passwd" placeholder="{function="localize('Password', 'users')"}"><br>
                <input type="password" name="retyped_passwd" placeholder="{function="localize('Retype password', 'users')"}" style="margin-top:5px;" id="retype_passwd">
            </th>
        </tr>

        <tr>
            <th>{function="localize('Avatar', 'users')"}:</th>
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
            <th>{function="localize('Full name', 'users')"}:</th>
            <th><input type="text" name="full_name"></th>
        </tr>

        <tr>
            <th>{function="localize('Primary group', 'users')"}:</th>
            <th>
                <select name="primary_group" style="width: 160px;">
                    {loop="$groups"}
                        <option value="{$value.name}">{$value.name}</option>
                    {/loop}
                </select>
            </th>
        </tr>

        <tr>
            <th>{function="localize('Language', 'users')"}:</th>
            <th>
                <select name="language" style="width: 160px;">
                    {loop="$locales_added"}
                        <option {if="$key == 'english'"} selected {/if} value="{$key}">{$key}</option>
                    {/loop}
                </select>
            </th>
        </tr>

        <tr>
            <th>{function="localize('Gender', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
            <th>
                <select name="gender" style="width: 210px;">
                    {loop="pantheraUser::$genders"}
                        <option value="{$value}">{function="localize($value, 'users')"}</option>
                    {/loop}
                </select>
            </th>
        </tr>

        <tr>
            <th>{function="localize('Address', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
            <th><input type="text" name="address" placeholder=""></th>
        </tr>

        <tr>
            <th>{function="localize('City', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
            <th><input type="text" name="city" placeholder="Warsaw"></th>
        </tr>

        <tr>
            <th>{function="localize('Postal code', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
            <th><input type="text" name="postal_code" placeholder="10-200"></th>
        </tr>

        <tr>
            <th>{function="localize('E-mail', 'users')"}: <small>({function="localize('optionally', 'users')"})</small></th>
            <th><input type="text" name="email" placeholder="user@gmail.com"></th>
        </tr>

        <tr>
            <th>{function="localize('Jabber', 'users')"}:<small>({function="localize('optionally', 'users')"})</small></th>
            <th><input type="text" name="jabber" placeholder="user@jabber.org"></th>
        </tr>

        </tbody>
    </table>

    <input type="text" style="display: none;" name="hash" value="{$usersCacheHash}">

</form>