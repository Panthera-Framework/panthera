<!DOCTYPE html>
<html>
	<head>
	    {$site_header}
    	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    	<link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/jquery.dropdown.css' type='text/css' media='all' />
    	<link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/login.css' type='text/css' media='all' />
    	<link rel="icon" type="image/png" href="{$PANTHERA_URL}/images/admin/pantheraUI/favicon.ico" />
    	
    	<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-migrate-1.2.1.min.js"></script>
    	<script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery.dropdown.js"></script>
    	
    	<style>
    	html {
    		color: white;
    	}
    	</style>
    </head>

 <body class="login">
   
   <div id="wrapper">

    <form name="loginform" id="loginform" action="?" method="post" class="login-form">

        <div class="header">
           <div style="float: right;">
            <span data-searchbardropdown="#languageDropdown" id="languageDropdownSpan" style="font-size: 11px; cursor: pointer;">
                <img src="{$PANTHERA_URL}/images/admin/flags/{$language}.png" style="height: 10px; margin: 1px;">
            </span>
            
            <div id="languageDropdown" class="searchBarDropdown searchBarDropdown-tip searchBarDropdown-relative">
                <ul class="searchBarDropdown-menu">
                    {loop="$flags"}
                    <li>
                        <a href="?{function="Tools::getQueryString('GET', '', array('_', '_locale'))"}&_locale={$value}&cat=admin">
                        <img src="{$PANTHERA_URL}/images/admin/flags/{$value}.png" style="height: 12px; margin: 1px;"> {$value}
                        </a>
                    </li>
                    {/loop}
                </ul>
            </div>
           </div>
            
            <center>
                <h1>Panthera Framework</h1>
                {if="isset($message)"}
                    <span>{function="localize($message, 'login')"}!</span><br/>
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
            {if="$mobileTemplate == True"}<a class="recover" onclick="window.location = 'pa-login.php?__switchdevice=mobile'" style="float: left;">Mobile</a>{/if}
            {if="$tabletTemplate == True"}<a class="recover" onclick="window.location = 'pa-login.php?__switchdevice=tablet'" style="float: left; margin-left: 12px;">Tablet</a>{/if}
            {if="$facebook"}<a class="recover" href="?facebook" style="float: left; margin-left: 12px; text-decoration: none;">{function="localize('Login with Facebook', 'facebook')"}</a>{/if}
            <a class="recover" onclick="$('#recovery').val('1');" style="float: right;"/>{function="localize('Recover password', 'login')"}</a>
        </div>

    </form>

   </div>

  </body>
</html>
