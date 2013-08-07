<!DOCTYPE html>
<html>
    <head>
        <title>{$site_title}</title>
        {$site_header}
        <meta charset="utf-8">
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/login.css' type='text/css' media='all' />
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/jquery.dropdown.css' type='text/css' media='all' />
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/w2ui-1.2.min.css" />
        <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery.dropdown.js"></script>
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
        body {
            color: black;
            font-family: "HelveticaNeue-Light", "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
        }
        
        #wrapper {
            width: 50%;
            min-height: 300px;
            padding-right: 20px;
            padding-left: 20px;
            margin: 0 auto;
            margin-top: 50px;
            background: #F3F3F3;
            border: 1px solid #FFF;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
            -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.5);
            -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
            margin-bottom: 200px;
        }
        
        #ajax_content {
            height: 75%;
            padding-top: 20px;
            padding-bottom: 20px;
            right: 0; bottom: 0;
        }
        
        h1 {
            font-family: 'Bree Serif', serif;
            font-weight: 300;
            font-size: 28px;
            line-height: 34px;
            color: #414848;
            text-shadow: 1px 1px 0 #FFF;
            margin-bottom: 10px;
        }
        
        .table {
            border: 1px solid #E6E8EA;
            border-radius: 5px 5px 5px 5px;
            box-shadow: none;
            border-bottom: none;
            text-decoration: none;
            border-bottom: 1px solid #E6E8EA;
            background-color: white;
            border-spacing: 0px;
        }
        
        .table td {
            font-size: smaller;
            border-bottom: 1px solid #E6E8EA;
            padding: 10px 10px 10px 10px;
        }
        
        .table tr {
            border-bottom: 1px solid #E6E8EA;
        }
        
        .selectBox {
            padding: 4px;
			border-radius: 4px;
			cursor: pointer;
        }
        
        .selectBox:hover {
            background: #F2F2F2;
        }
        
        .button {
            padding: 6px 25px;
            font-family: 'Bree Serif', serif;
            font-weight: 300;
            font-size: 18px;
            color: #FFF;
            text-shadow: 0px 1px 0 rgba(0, 0, 0, 0.25);
            background: #71BBF5;
            border: 1px solid #46B3D3;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: inset 0 0 2px rgba(255, 255, 255, 0.75);
            -moz-box-shadow: inset 0 0 2px rgba(256,256,256,0.75);
            -webkit-box-shadow: inset 0 0 2px rgba(255, 255, 255, 0.75);
            height: 40px;
        }
        
        .button:hover {
            background: #4B8EC2;
            border: 1px solid rgba(256,256,256,0.75);

            box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
            -moz-box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
            -webkit-box-shadow: inset 0 1px 3px rgba(0,0,0,0.5);
        }
        
        .button:disabled {
            background: #A1B7C9;
            border: 1px solid #A1B7C9;
        }
        
        .button:disabled:hover {
            background: #A1B7C9;
            border: 1px solid #A1B7C9;
        }
        
        .checkButton {
            background: #A7E098;
            border: 1px solid #60BD48;
            display: none;
        }
        
        .description {
            margin-left: 5px;
            font-size: smaller;
        }

        input[type="text"], input[type="password"] {
            border: solid 1px #cccccc;
            background: -webkit-gradient(linear, left top, left bottom, from(rgb(250, 250, 250)), to(rgb(247, 247, 247)));
            background: -moz-linear-gradient(top, rgb(250, 250, 250) 0%, rgb(247, 247, 247) 100%);
		    background: -o-linear-gradient(top, rgb(250, 250, 250) 0%,rgb(247, 247, 247) 100%);
		    background: -ms-linear-gradient(top, rgb(250, 250, 250) 0%,rgb(247, 247, 247) 100%);
		    background: linear-gradient(top, rgb(250, 250, 250) 0%,rgb(247, 247, 247) 100%);
		    border-radius: 2px;
		    height: 18px;
		    padding: 3px;
        }

        input[type="text"]:hover, input[type="text"]:active, input[type="password"]:hover, input[type="password"]:active {
            background: white;
            box-shadow: 1px 2px 2px rgb(221, 221, 221);
        }
        
        a {
            text-decoration: none;
        }
        
        a:visited {
            color: blue;
        }
        
        .redButton {
            background: #FA8A8A;
            border: 1px solid #A03B3B;
            height: 25px;
            padding-left: 8px;
            padding-right: 8px;
            padding-bottom: 25px;
            font-size: 15px;
        }
        
        code {
            margin: 15px;
            display: block;
            padding: 15px;
            border: 1px solid #D8D8D8;
            border-radius: 2px;
            background: #FAFAFB;
            font-size: 12px;
        }
        
        h2 {
            font-family: 'Bree Serif', serif;
            font-weight: 300;
            font-size: 20px;
            line-height: 34px;
            color: #414848;
            text-shadow: 1px 1px 0 #FFF;
            margin-bottom: 10px;
        }
        </style>
    </head>
    
    
    <body>
        <div id="wrapper">
            <div id="ajax_content">{include="$stepTemplate"}</div>
            
            <div style="width: 100%; height: 60px;">
                <div style="margin-bottom: 20px; height: 100px; float: right;" id="buttonsBar">
                    <input type="button" class="button" disabled value="{function="localize('Back', 'installer')"}" id="installer-controll-backBtn" onclick="customNextBtn = false; navigateTo('?_stepbackward=True');"> 
                    <input type="button" class="button" value="{function="localize('Next', 'installer')"}" id="installer-controll-nextBtn" onclick="nextBtn()">
                    <input type="button" class="button checkButton" value="{function="localize('Check', 'installer')"}" id="installer-controll-checkBtn" onclick="checkBtn()">
                </div>
            </div>
        </div>
    </body>
</html>
