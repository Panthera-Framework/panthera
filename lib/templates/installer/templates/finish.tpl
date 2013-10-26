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

<div class="header">
        <h1>{function="localize('Your Panthera installation is ready', 'installer')"}</h1>
        <div style="margin-left: 5px;"><span>{function="localize('Within this account you will be able to manage site content, settings, users and database. To change everything anytime you want.', 'installer')"}</span></div>
</div>
<div class="content" style="margin-left: 0px; padding: 0;">
  <div style="margin: 0 auto; width: 50%; text-align: center; margin-top: 30px;">
    <img src="{$PANTHERA_URL}/images/default_avatar.png"></a><br>
    <div style="margin-top: 15px;">
        <p><small>{function="localize('Welcome', 'installer')"} <b>{$userLogin|ucfirst}</b>, {function="localize('use your login and password to login to Administration Panel.', 'installer')"}</small></p>
    </div>
  </div>
</div>
