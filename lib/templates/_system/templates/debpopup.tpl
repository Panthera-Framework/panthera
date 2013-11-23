<!DOCTYPE html>
<html>
    <head>
        <title>Debugging popup</title>
        <meta charset="utf-8">
        <link rel="icon" type="image/png" href="{$PANTHERA_URL}/images/admin/pantheraUI/favicon.ico" />
        <link rel="stylesheet" type="text/css" href="{$PANTHERA_URL}/css/pantheraUI.css">
        <script type="text/javascript" src="http://code.jquery.com/jquery-1.10.0.min.js"></script>
        
        <style type="text/css">
        body * {
            padding: 0px 0px;
        }
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
        <div id="logoBar">
            <div class="centerWithContent pantheraLogo">
                <span><a href="http://panthera.kablownia.org:82/darbs-tools//pa-admin.php">Debugging</a></span>
            </div>
            
            <!-- Menubar -->
            
            <div id="menuBarVisibleLayer">
                <div class="centerWithContent" id="menuBar">
                    <span class="menuItem">
                        <a href="#"><img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-{$PANTHERA_URL}/images/admin/menu/dashboard.png menuIcon" alt="Dash"> 
                            <span class="menuText">Messages log</span>
                        </a>
                    </span>
                </div>
            </div>
        </div>
        
        <div id="ajax_content" class="centerWithContent">
            <div id="tab_debug">
                <table>
                    <thead>
                        <th style="padding-left: 5px;">Time</th>
                        <th style="padding-left: 5px;">Diffirence</th>
                        <th style="padding-left: 5px;">Category</th>
                        <th style="padding-left: 5px;">Message</th>
                    </thead>
                    
                    <tbody>
                    {loop="$debugArray"}
                        <tr>
                            <td style="padding: 5px;">{$value.timing[0]}</td><td style="padding: 5px;">{$value.timing[1]}</td><td style="padding: 5px;">{$value.category}</td><td style="padding: 5px;">{$value.message}</td>
                        </tr>
                    {/loop}
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</html>
