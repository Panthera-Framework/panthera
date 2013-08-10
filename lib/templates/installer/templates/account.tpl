{include="buttons"}
<script type="text/javascript">
    customNextBtn = true;

    function setupAccount()
    {
        panthera.jsonPOST( { data: '#setupAccountForm', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?_nextstep=True');
                } else {
                    if (response.field != undefined)
                    {
                        $('#'+response.field).w2tag(response.message);
                        window.setTimeout('$(\'#'+response.field+'\').w2tag();', 6000);
                    }
                }
            }
        });
    }
    
    $(document).ready(function () {
        $('#installer-controll-nextBtn').attr('disabled', false);
        $(document).bind('onNextBtn', function () { setupAccount(); });
    });
</script>

<h1>{function="localize('Setup your account', 'installer')"}</h1>

<span class="description">{function="localize('Within this account you will be able to manage site content, settings, users and database. To change everything anytime you want.', 'installer')"}</span>

<form action="?" method="POST" id="setupAccountForm">
<table class="table" style="width: 50%; margin: 0 auto; margin-top: 30px;">
    <tbody>
        <!-- avatar -->
        <tr><td style="text-align: center;" colspan="2"><img src="{$PANTHERA_URL}/images/default_avatar.png" style="max-width: 150px; max-height: 150px;"></td></tr>
        
        <!-- login -->
        <tr>
            <td>{function="localize('Login', 'installer')"}:</td> <td style="text-align: center;"><input type="text" placeholder="{function="localize('Login', 'installer')"}" id="login" name="login" style="width: 60%;"></td>
        </tr>
        
        <!-- password -->
        <tr>
            <td>{function="localize('Password', 'installer')"}:</td> <td style="text-align: center;"><input type="password" placeholder="{function="localize('Password', 'installer')"}" id="password" name="password" style="width: 60%;"></td>
        </tr>
        
        <tr>
            <td>{function="localize('Confirm', 'installer')"}:</td> <td style="text-align: center;"><input type="password" placeholder="{function="localize('Confirm', 'installer')"}" id="confirm" name="confirm" style="width: 60%;"></td>
        </tr>
        
        <!-- e-mail address -->
        <tr>
            <td>{function="localize('E-mail address', 'installer')"}:</td> <td style="text-align: center;"><input type="text" placeholder="{function="localize('E-mail address', 'installer')"}" id="email" name="email" style="width: 60%;"></td>
        </tr>
    </tbody>
</table>
</form>
