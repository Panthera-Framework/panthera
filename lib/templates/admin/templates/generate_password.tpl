{$site_header}

<script type="text/javascript">

// spinner
var generating = new panthera.ajaxLoader($('#generateHash'));

/**
  * Generate random string
  *
  * @author Mateusz Warzyński
  */

function generateRandom()
{
    var lenght = $('#lenght_random').val();
    
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=generate_password&cat=admin&action=generateRandom', data: 'lenght='+lenght, spinner: generating, messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
                $('#password').val(response.random);
                generateHash();
            }
        }
   });
}

/**
  * Generate password hash
  *
  * @author Mateusz Warzyński
  */

function generateHash()
{
    var password = $('#password').val();
    
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=generate_password&cat=admin&action=generatePassword', data: 'password='+password, spinner: generating, messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
                $('#hash').val(response.hash);
            }
        }
   });
}


</script>

{include="ui.titlebar"}

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    
    <table>
            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Password')"}</th>
                    <th colspan="2">{function="localize('Hash')"}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <td><input type="text" id="password" style="width: 200px;" onchange="generateHash();"></td>
                    <td><input type="text" id="hash" style="width: 500px;" disabled></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <input type="button" onclick="generateRandom();" value="{function="localize('Random', 'debug')"}" style="float: right; margin-right: 7px;">
                        <input type="text" id="lenght_random" placeholder="{function="localize('Lenght', 'debug')"}" style="float: right; margin-right: 5px; width: 50px;">
                    </td>
                </tr>
            </tbody>
    </table>
</div>