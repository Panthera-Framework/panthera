<!DOCTYPE html>
<html>
    <head>
        {$site_header}
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/login.css' type='text/css' media='all' />
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/pantheraInstaller.css' type='text/css' m
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/admin/jquery.dropdown.css">
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/admin/jquery-ui.css">
        <link rel="icon" type="image/png" href="{$PANTHERA_URL}/images/admin/pantheraUI/favicon.ico" />
        {include="installer.js.tpl"}
    </head>
    
    
    <body class="login">
       <div class="login-form">
        <div id="ajax_content">{include="$stepTemplate"}</div>
        
        <div class="footer">
             <div id="separator" style="position: relative; height: 1px; width: 100%; top: -10px; background: #6e8093;"></div>
               <input type="button" class="button" disabled value="{function="localize('Back', 'installer')"}" id="installer-controll-backBtn" onclick="customNextBtn = false; navigateTo('?_stepbackward=True');" style="float: left; margin-left: 60px;">
               <input type="button" class="button" value="{function="localize('Next', 'installer')"}" id="installer-controll-nextBtn" onclick="nextBtn()" style="float: right; margin-right: 60px;">
               <input type="button" class="button checkButton" value="{function="localize('Check', 'installer')"}" id="installer-controll-checkBtn" onclick="databaseCheck()" style="float: right; margin-right: 10px; display: none;"> 
          </div>            
        </div>
      </div>
    </body>
</html>
