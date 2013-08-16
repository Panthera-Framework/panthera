<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="pl-PL">
	<head>
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    	<title>{$site_title}</title>
    	<link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/login.css' type='text/css' media='all' />
        <meta name='robots' content='noindex,nofollow' />
    </head>

 <body class="login login-action-login wp-core-ui">
   <div id="wrapper">

    <form name="loginform" id="loginform" action="?" method="post" class="login-form">

        <div class="header">
        <center>
            <h1>Panthera Framework</h1>
            {if="isset($message)"}
                <span style=" color: #F00; font-size: 12px;">{function="localize($message, 'login')"}!</span>
            {else}
                <span>{function="localize('Fill out the form below to login to Panthera', 'login')"}.</span>
            {/if}
        </center>
        <br/>
        </div>

        <div class="content">
        <input type="text" name="log" id="user_login" class="input username" placeholder="{function="localize('Username', 'login')"}" />
        <div class="user-icon"></div>
        <input type="password" name="pwd" id="user_pass"  class="input password" placeholder="{function="localize('Password', 'login')"}" />
        <div class="pass-icon"></div>
        </div>

        <div class="footer">
        <input type="submit" class="button" value="{function="localize('Sign in', 'login')"}" />
        <input type="button" class="recover" onclick="window.location = 'pa-login.php?__switchdevice=mobile'" value="Mobile" style="float: left;"/>
        <input type="submit" class="recover" onclick="setRecovery();" value="{function="localize('Recover password', 'login')"}" />
        </div>

    </form>

   </div>

  </body>
</html>
