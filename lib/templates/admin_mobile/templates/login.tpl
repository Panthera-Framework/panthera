<!-- Powered by Panthera Framework {$PANTHERA_VERSION} -->
<!--      http://github.com/webnull/panthera           -->
<html>
    <head>

      <meta charset="utf-8" />
      <meta name="format-detection" content="telephone=no" />

      <!-- Required meta viewport tag. Do not modify this! -->
      <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />

      <title>{$site_title}</title>

     <!-- Include jquery -->
      <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
      <script type="text/javascript" src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
      <script type="text/javascript" src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

     <!-- Include panthera scripts -->
      <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
      <script type="text/javascript" src="{$PANTHERA_URL}/js/tiny_mce/tiny_mce.js"></script>
      <script type="text/javascript" src="{$PANTHERA_URL}/js/jquery.form.js"></script>
      <script type="text/javascript" src="{$PANTHERA_URL}/js/admin.js"></script>

     <!-- Include fries styles -->
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/base.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/action-bars.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/chevrons.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/tabs.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/content.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/buttons.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/forms.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/lists.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/spinners.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/icomoon.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/stack.css">
      <link rel="stylesheet" href="{$PANTHERA_URL}/css/fries/sliders.css">

     <!-- Include fries scripts -->
      <script src="{$PANTHERA_URL}/js/fries/stack.js"></script>
      <script src="{$PANTHERA_URL}/js/fries/action-bars.js"></script>
      <script src="{$PANTHERA_URL}/js/fries/spinners.js"></script>
      <script src="{$PANTHERA_URL}/js/fries/tabs.js"></script>

    </head>

 <body ontouchstart="">
  <!-- Page -->
   <div class="page">

   <!-- Content -->
    <div class="content">
     <br>
     	<a href="pa-login.php?__switchdevice=desktop" style="float: left; margin-left: 17px;">Desktop</a>
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
       <!-- <button class="btn-block" id="wp-recover" onclick="setRecovery();">{function="localize('Recover password', 'login')"}</button> -->
     </form>
    </div>
   <!-- End of content -->

   </div>
  <!-- End of page -->
 </body>
</html>


