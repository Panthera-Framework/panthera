<!DOCTYPE html>
<html>
	<head>
	    {$site_header}
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    	<link rel='stylesheet' href='{$PANTHERA_URL}/css/login.css' type='text/css' media='all' />
    	
    	<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
    </head>

 <body class="login">
   
   <div id="wrapper">

    <form name="loginform" id="loginform" action="?" method="post" class="login-form">

        <div class="header">
            <center>
                <h1>Panthera Framework</h1>
                {if="isset($message)"}
                    <span style=" color: #f3f3f3;">{function="localize($message, 'login')"}!</span>
                {else}
                    <span>{function="localize('Fill out the form below to login to Panthera', 'login')"}.</span>
                {/if}
                <img src="{$PANTHERA_URL}/images/admin/pantheraUI/logo-big.png" height="170" style="margin-top: 20px;">
            </center><br/>
        </div>

        <div class="content">
            <input type="text" name="log" id="user_login" class="input username" placeholder="{function="localize('Username', 'login')"}" />
            <input type="password" name="pwd" id="user_pass"  class="input password" placeholder="{function="localize('Password', 'login')"}" style="margin-top: 10px;"/>
        </div>

        <div class="footer">
            <center><input type="submit" class="button" value="{function="localize('Sign in', 'login')"}" /></center><br>
            <input type="hidden" name="recovery" id="recovery">
            {if="$mobileTemplate == True"}<a class="recover" onclick="window.location = 'pa-login.php?__switchdevice=mobile'" style="float: left;"/>Mobile</a>{/if}
            {if="$tabletTemplate == True"}<a class="recover" onclick="window.location = 'pa-login.php?__switchdevice=tablet'" style="float: left; margin-left: 12px;"/>Tablet</a>{/if}
            <a class="recover" onclick="$('#recovery').val('1');" style="float: right;"/>{function="localize('Recover password', 'login')"}</a>
        </div>

    </form>

   </div>

  </body>
</html>
