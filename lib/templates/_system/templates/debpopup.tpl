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
        var tablesData = JSON.parse(atob("{$debugTables|json_encode|base64_encode}"));
        
        $(document).ready(function() {
            i = 0;
            
            htmlCode = "";
            
            for(table in tablesData)
            {
                i++;
                
                htmlCode += "<div id=\"tab_"+i+"\" class=\"allTabs\"";
                
                if (i > 1)
                {
                    htmlCode += "style=\"display: none;\"";
                }
                
                htmlCode += ">";
                
                if (!tablesData[table]["items"])
                {
                    htmlCode += "</div>";
                    continue;
                }
                
                htmlCode += "<table style=\"width: 100%;\"><thead>";
                
                for (item in tablesData[table]["header"])
                {
                   htmlCode += "<th style=\"padding-left: 5px;\">"+tablesData[table]["header"][item]+"</th>";
                }

                htmlCode += "</thead><tbody class=\"hovered\">";
                
                for (item in tablesData[table]["items"])
                {
                    htmlCode += "<tr>";
                    
                    for (column in tablesData[table]["items"][item])
                    {
                        htmlCode += "<td style=\"padding: 5px;\">"+tablesData[table]["items"][item][column]+"</td>";
                    }
                    
                    htmlCode += "</tr>";
                }
                
                htmlCode += "</div>";
            }
            
            $(".popupContent").append(htmlCode);

            x = 0;  //horizontal coord
            y = document.height; //vertical coord
            
            if (!localStorage.getItem("debpopupTab") || localStorage.getItem("debpopupTab") == "debug")
            {
                window.scroll(x,y);
            }
            
            if (localStorage.getItem("debpopupTab"))
            {
                showTable(localStorage.getItem("debpopupTab"));
            }
        });
        
        function showTable(tabName)
        {
            if ($("#tab_"+tabName).length)
            {
                $(".allTabs").hide();
                $("#tab_"+tabName).show();
                localStorage.setItem("debpopupTab", tabName);
            }
        }
        </script>
    </head>
    
    <body>
        <div id="logoBar">
            <div class="centerWithContent pantheraLogo">
                <span><a>Panthera Debugger</a></span>
            </div>
            
            <!-- Menubar -->
            
            <div id="menuBarVisibleLayer">
                <div class="centerWithContent" id="menuBar">
                {loop="$debugTables"}
                    <span class="menuItem">
                        <a onclick="showTable('{$key}');">
                            <img src="images/admin/pantheraUI/transparent.png" class="pantheraIcon menuIcon" alt="Dash"> 
                            <span class="menuText">{$value.name}</span>
                        </a>
                    </span>
                {/loop}
                </div>
            </div>
        </div>
        
        <div id="ajax_content" class="centerWithContent popupContent">
            {*}
            {$i=0}
            {loop="$debugTables"}
            {$i=$i+1}
            <div id="tab_{$key}" class="allTabs" {if="$i > 1"}style="display: none;"{/if}>
                {if="$value.items"}
                <table style="width: 100%;">
                    <thead>
                        {$tableVal=$value}
                        {loop="$tableVal.header"}
                            <th style="padding-left: 5px;">{$value}</th>
                        {/loop}
                    </thead>
                    
                    <tbody class="hovered">
                    {loop="$tableVal.items"}
                        {if="$value"}
                        <tr>
                            {loop="$value"}
                                <td style="padding: 5px;">{$value}</td>
                            {/loop}
                        </tr>
                        {/if}
                    {/loop}
                    </tbody>
                </table>
                {else}
                {if="$value.content"}
                {$value.content}
                {else}
                Empty.
                {/if}
                {/if}
            </div>
            {/loop}
            {/*}
        </div>
    </body>
</html>
