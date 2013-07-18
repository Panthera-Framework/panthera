<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml" lang="pl-PL">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>{$site_title}</title>
	<link rel='stylesheet' id='wp-admin-css'  href='{$PANTHERA_URL}/css/wp/wp-admin.min.css' type='text/css' media='all' />
<link rel='stylesheet' id='buttons-css'  href='{$PANTHERA_URL}/css/wp/buttons.min.css' type='text/css' media='all' />
<link rel='stylesheet' id='colors-fresh-css'  href='{$PANTHERA_URL}/css/wp/colors-fresh.min.css' type='text/css' media='all' />
<meta name='robots' content='noindex,nofollow' />
	</head>
<body class="login login-action-login wp-core-ui">

<div id="wrapper">

    <form name="loginform" id="loginform" action="?" method="post" class="login-form">

        <div class="header">
        <h1>Login Form</h1>
        <span>Fill out the form below to login to my super awesome control panel.</span>
        </div>

        <div class="content">
        <input type="text" name="log" id="user_login" class="input username" placeholder="Username" />
        <div class="user-icon"></div>
        <input type="password" name="pwd" id="user_pass"  class="input password" placeholder="Password" />
        <div class="pass-icon"></div>
        </div>

        <div class="footer">
        <input type="submit" name="wp-submit" id="wp-submit" class="button" value="{function="localize('Sign in', 'login')"}" />
        <input type="submit" name="wp-submit" onclick="setRecovery();" id="wp-recover" class="register" style="margin-right: 5px;" value="{function="localize('Recover password', 'login')"}" />
        </div>

    </form>

</div>
<div class="gradient"></div>



<!-- =================================== -->


<script type="text/javascript">
function attempt_focus(){
setTimeout( function(){
    try{
        d = document.getElementById('user_pass');
        d.value = '';
        d.focus();
        d.select();
    } catch(e){}
  }, 200);
}

function setRecovery()
{
    document.getElementById('recovery').value = "1";
}



attempt_focus();
if(typeof wpOnload=='function')wpOnload();
</script>
	</body>
	</html>
