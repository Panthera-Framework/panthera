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
        
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-1.10.0.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery-ui.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/panthera.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/pantheraUI.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery.dropdown.js"></script>
        
        <script type="text/javascript">
            customNextBtn = false;
            
            /**
              * Next step button
              *
              * @hook onNextBtn
              * @return void 
              * @author Damian Kęska
              */
        
            function nextBtn()
            {
                if (customNextBtn == false)
                    navigateTo('?_nextstep=True');
                else
                    $(document).trigger('onNextBtn');
            }
            
            /**
              * Data validation button
              *
              * @return void 
              * @author Damian Kęska
              */
            
            function checkBtn()
            {
                $(document).trigger('onCheckBtn');
            }
        </script>
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
