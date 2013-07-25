{include="header.tpl"}

   <!-- Content -->        
    <div class="content">
     <form name="loginform" class="inset" id="loginform" action="?" method="post">
       <input type="text" name="log" id="user_login" placeholder="{function="localize('Username', 'login')"}" class="input-text" autocomplete="off">
       <input type="password" name="pwd" placeholder="{function="localize('Password', 'login')"}" class="input-text" autocomplete="off">
       
       <input type="submit" class="btn-block" value="{function="localize('Sign in', 'login')"}" id="send_button">
       <button class="btn-block" id="wp-recover" onclick="setRecovery();">{function="localize('Recover password', 'login')"}</button>
     </form>
    </div>
   <!-- End of content -->
   
{include="footer.tpl"}
