<!DOCTYPE html>
<html>
    <head>
        <title>Panthera installer</title>
        <meta charset="utf-8">
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/login.css' type='text/css' media='all' />
        <link rel='stylesheet' href='{$PANTHERA_URL}/css/admin/jquery.dropdown.css' type='text/css' media='all' />
        <script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
        <script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/admin/jquery.dropdown.js"></script>
        <script type="text/javascript" src="{$PANTHERA_URL}/js/panthera.js"></script>

        <style type="text/css">
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
        
        .disabled {
            background: #A1B7C9;
            border: 1px solid #A1B7C9;
        }
        
        .disabled:hover {
            background: #A1B7C9;
            border: 1px solid #A1B7C9;
        }
        </style>
    </head>
    
    
    <body>
        <div id="wrapper">
            <div id="ajax_content">{include="$stepTemplate"}</div>
            
            <div style="right: 0; bottom: 0; float: right; margin-bottom: 20px;">
                <input type="button" class="button disabled" value="Back"> 
                <input type="button" class="button" value="Next">
            </div>
        </div>
    </body>
</html>
