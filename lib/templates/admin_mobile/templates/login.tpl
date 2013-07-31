{include="header.tpl"}

   <!-- Content -->        
    <div class="content">
     <br>
     <center>
    {if="isset($message)"}
         <span style="color: #F00; font-size: 14px;">{function="localize($message, 'login')"}!</span>
    {else}
         <span style="color: #D1D1D1; font-size: 14px;">{function="localize('Fill out the form below to login to Panthera', 'login')"}.</span>
    {/if}
     </center>
        
     <form name="loginform" class="inset" id="loginform" action="?" method="post">
       <input type="text" name="log" id="user_login" placeholder="{function="localize('Username', 'login')"}" class="input-text" autocomplete="off">
       <input type="password" name="pwd" placeholder="{function="localize('Password', 'login')"}" class="input-text" autocomplete="off">
       
       <input type="submit" class="btn-block" value="{function="localize('Sign in', 'login')"}" id="send_button">
       <button class="btn-block" id="wp-recover" onclick="setRecovery();">{function="localize('Recover password', 'login')"}</button>
     </form>
    </div>
   <!-- End of content -->
   
{include="footer.tpl"}
