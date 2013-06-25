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
	<div id="login">
<form name="loginform" id="loginform" action="?" method="post">{if isset($message)}{$message}<br><br>{/if}
	<p>
		<label for="user_login">{"Login"|localize:login}<br />
		<input type="text" name="log" id="user_login" class="input" value="" size="20" /></label>
	</p>
	<p>
		<label for="user_pass">{"Password"|localize:login}<br />
		<input type="password" name="pwd" id="user_pass" class="input" value="" size="20" /></label>
        <input type="hidden" name="recovery" id="recovery" value="0">
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="{"Sign in"|localize:login}" /> &nbsp;<input type="submit" name="wp-submit" onclick="setRecovery();" id="wp-recover" class="button button-primary button-large" style="margin-right: 5px;" value="{"Recover password"|localize:login}" />
	</p>
</form>

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

	<p id="backtoblog"><a href="{$PANTHERA_URL}" title="">&larr; {"Back to website"|localize:login}</a></p>


	</div>


		<div class="clear"></div>
	</body>
	</html>
