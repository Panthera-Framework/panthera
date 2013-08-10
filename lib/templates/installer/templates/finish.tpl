{include="buttons"}

<script type="text/javascript">
customNextBtn = true;

$(document).ready(function () {
    $('#installer-controll-nextBtn').attr('disabled', false);
    $('#installer-controll-nextBtn').val('{function="localize('Finish', 'installer')"}');
});

$(document).bind('onNextBtn', function () {
    window.location.href = '{$PANTHERA_URL}/pa-login.php';
});
</script>

<h1>{function="localize('Your Panthera installation is ready', 'installer')"}</h1>
<span class="description">{function="localize('Everything seems to be okay, so your application is ready to be developed or used in a production environment according to its development state. This installer has done only basic configuration of your database, locales, settings. For more informations and documentation please take a look at our github page.', 'installer')"}</span>

<div style="margin: 0 auto; width: 50%; text-align: center; margin-top: 30px;">
    <img src="{$PANTHERA_URL}/images/default_avatar.png"></a><br>
    <div style="margin-top: 15px;">
        <small>Welcome <b>{$userLogin|ucfirst}</b>, {function="localize('use your login and password to login to Administration Panel.', 'installer')"}</small>
    </div>
</div>
