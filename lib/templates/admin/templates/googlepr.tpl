{$site_header}

<script type="text/javascript" src="{$PANTHERA_URL}/js/Chart.min.js"></script>

<script type="text/javascript">
/**
  * Get PageRank of given domain
  *
  * @author Mateusz Warzyński
  */

function getPageRank()
{
    panthera.jsonPOST({ url: '?display=googlepr&cat=admin&action=getPageRank', data: '#pageRank', messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
            {
                navigateTo(window.location);
            }
        }
   });
}

$(document).ready(function() {
    $('#pageRank').submit(function() {
        getPageRank();
        return false;
    });
});

</script>

{include="ui.titlebar"}

<div id="topContent" style="min-height: 47px;">
    <div class="dash">
        <div class="searchBarButtonArea">
            <input type="button" onclick="getPageRank();" value="{function="localize('Check Google PageRank', 'googlepr')"}">
        </div>
    </div>
</div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <form method="POST" id="pageRank">
        <table style="margin: 0 auto; width: 740px;">
            <thead>
                <tr>
                    <th colspan="2">{$uiTitlebar.title}</th>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <td>{function="localize('Domain', 'googlepr')"}:</td>
                    <td><input type="text" name="domain" id="domain" style="width: 100%;"></td>
                </tr>   
            </tbody>
        </table>
        
        {if="count($charResults)"}
        <table style="margin: 0 auto; width: 740px; margin-top: 25px;">
            <thead>
                <tr>
                    <th>{function="localize('Domain', 'googlepr')"}</th>
                    <th>{function="localize('Page rank', 'googlepr')"}</th>
                </tr>
            </thead>
            
            <tbody class="hovered">
                {loop="$charResults"}
                <tr>
                    <td>{$key}</td>
                    <td>{$value}</td>
                </tr>
                {/loop}
            </tbody>
        </table>
        {/if}
        
        {if="count($charResults) > 1"}
        <br><br>
        
        <canvas id="myChart" width="900" height="400"></canvas>
        
        <script type="text/javascript">
        var data = {
            labels : [{loop="$charResults"} "{$key}", {/loop}],
            datasets : [
                {
                    fillColor : "rgba(86,104,123,0.75)",
                    strokeColor : "rgba(64,76,90,1)",
                    data : [{loop="$charResults"} {$value}, {/loop}]
                }
            ]
        }

        var myLine = new Chart(document.getElementById("myChart").getContext("2d")).Bar(data);
        </script>
        {/if}
    </form>
</div>
