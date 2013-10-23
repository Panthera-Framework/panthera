<!DOCTYPE html>
<html>
    <head>
        <title>{$site_title}</title>
        {$site_header}
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/login.css' type='text/css' media='all' />
        <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>
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

        <style type="text/css">
            .login-form {
                position: relative;
                margin-top: 7%;
                min-width: 700px;
                min-height: 500px;
            }
            
            .login-form .header {
                padding-top: 30px;
                margin-left: 35px;
                margin-right: 30px;
            }
            
            .login-form .header h1 {
                font-size: 19px;
            }
            
            .login-form .content {
                margin-top: 40px;
                border: 0;
                margin-left: 35px;
            }
            
            .login-form .content p {
                color: #fff;
                font-size: 13px;
            }
            
            .login-form .content select {
                padding: 2px;
            }
            
            .login-form .footer {
                position: absolute;
                bottom: 0;
                width: 100%;
                padding: 0;
                padding-top: 10px;
                padding-bottom: 10px;
                
                border-top: 1px solid #3d4957; 
            }
        
        </style>
    </head>
    
    
    <body class="login">
       <div class="login-form">
        <div id="ajax_content">{include="$stepTemplate"}</div>
        <div class="footer">
               <input type="button" class="button" disabled value="{function="localize('Back', 'installer')"}" id="installer-controll-backBtn" onclick="customNextBtn = false; navigateTo('?_stepbackward=True');" style="float: left; margin-left: 60px;">
               <input type="button" class="button" value="{function="localize('Next', 'installer')"}" id="installer-controll-nextBtn" onclick="nextBtn()" style="float: right; margin-right: 60px;"> 
               <input type="button" class="button checkButton" value="{function="localize('Check', 'installer')"}" id="installer-controll-checkBtn" onclick="checkBtn()" style="float: right; margin-right: 10px;">
          </div>            
        </div>
      </div>
    </body>
</html>
