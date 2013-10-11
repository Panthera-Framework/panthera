{$site_header}

<script type="text/javascript">
/**
  * Generate password hash
  *
  * @author Mateusz Warzyński
  */

function generateHash()
{
    panthera.jsonPOST({ data: '#generatePasswordForm', messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
                $('#password').val(response.password);
                $('#hash').html(response.hash);
                $('#length').val(response.len);
            }
        }
   });
}


</script>

{include="ui.titlebar"}

<div id="topContent">
    <div class="dash">
        <div class="searchBarButtonArea">
            <input type="button" onclick="generateHash();" value="{function="localize('Generate', 'generate_password')"}">
        </div>
    </div>
</div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <form action="?display=generate_password&cat=admin&action=generatePassword" method="POST" id="generatePasswordForm">
        <table style="margin: 0 auto; width: 740px;">
            <thead>
                <tr>
                    <th colspan="2">{$uiTitlebar.title}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <td>{function="localize('Length', 'generate_password')"}:</td>
                    <td><input type="text" name="length" id="length" style="width: 100%;"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Characters range')"}:</td>
                    <td><input type="text" name="range" id="range" value="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_.,?!" style="width: 100%;"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Password', 'generate_password')"}:</td>
                    <td><input type="text" name="password" id="password" style="width: 100%;"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Hash', 'generate_password')"}:</td>
                    <td><span id="hash"></span></td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
