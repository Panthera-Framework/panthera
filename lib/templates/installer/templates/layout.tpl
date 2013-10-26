<!DOCTYPE html>
<html>
    <head>
        <title>{$site_title}</title>
        {$site_header}
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/login.css' type='text/css' media='all' />
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/pantheraInstaller.css' type='text/css' media='all' />
        <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/pantheraUI.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/w2ui-1.2.min.js"></script>
        
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
               <input type="button" class="button" disabled value="{function="localize('Back', 'installer')"}" id="installer-controll-backBtn" onclick="customNextBtn = false; navigateTo('?_stepbackward=True');" style="float: left; margin-left: 60px;">
               <input type="button" class="button" value="{function="localize('Next', 'installer')"}" id="installer-controll-nextBtn" onclick="nextBtn()" style="float: right; margin-right: 60px;">
               <input type="button" class="button checkButton" value="{function="localize('Check', 'installer')"}" id="installer-controll-checkBtn" onclick="databaseCheck()" style="float: right; margin-right: 10px; display: none;"> 
          </div>            
        </div>
      </div>
    </body>
</html>
