<!DOCTYPE html>
<html>
    <head>
        <title>Debugging popup</title>
        <meta charset="utf-8">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
        
        <style type="text/css">
            html * { padding:0; margin:0; }
            body * { padding:10px 20px; }
            body * * { padding:0; }
            body { font:small sans-serif; background:#eee; }
            body>div { border-bottom:1px solid #ddd; }
            h1 { font-weight:normal; margin-bottom:.4em; }
            h1 span { font-size:60%; color:#666; font-weight:normal; }
            table { border:none; border-collapse: collapse; width:100%; }
            td, th { vertical-align:top; padding:2px 3px; }
            th { width:12em; text-align:right; color:#666; padding-right:.5em; }
            #info { background:#f6f6f6; }
            #info ol { margin: 0.5em 4em; }
            #info ol li { font-family: monospace; }
            #summary { background: #c1daef; }
            #explanation { background:#eee; border-bottom: 0px none; }
        </style>
        
        <script type="text/javascript">
        $(document).ready(function() {
            x = 0;  //horizontal coord
            y = document.height; //vertical coord
            window.scroll(x,y);
        });
        </script>
    </head>
    
    <body>
        <div id="summary">
            <h1>Debugging messages</h1>
        </div>
        <div id="info">
            <p>
                {$debugMessages}
            </p>  
        </div>
    </body>
</html>
