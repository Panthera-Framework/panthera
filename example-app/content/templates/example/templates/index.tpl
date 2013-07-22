<!DOCTYPE html>
<html>
    <head>
        <title>Example application</title>
        <meta charset="utf-8">
        
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
    </head>
    
    <body>
        <div id="summary">
            <h1>Example application</h1>
        </div>
        <div id="info">
            <p>
                This site is powered by Panthera Framework {$PANTHERA_VERSION} which features:
                
                <ol>
                    <li>Many libraries to make your job easier and faster</li>
                    <li>Translations management</li>
                    <li>Ready to use Admin Panel</li>
                    <li>PDO database integrated with MySQL and SQLite3 support</li>
                    <li>Plugins, modules and packages with integrated package manager "Leopard" (works like in Unix/Linux operating systems)</li>
                    <li>Fast and lightweight template engine - RainTPL</li>
                    <li>Web and shell tools for debugging and monitoring your application</li>
                    <li>Panthera CLI lets you create shell tools integrated with your application</li>
                    <li>Support for caching queries, templates and many other things</li>
                    <li>Built-in facebook wrapper, gallery module, custom static pages and many other modules</li>
                </ol>
            </p>  
        </div>
    </body>
</html>