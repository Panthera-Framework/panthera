{include="ui.titlebar"}

<script type="text/javascript">
    function selectTab(data)
    {
        type = $(data).attr('href').replace('#', '');
        $('.dumpInputTabs').hide();
        $('#'+type).show();
    }
</script>

<div id="topContent">
    <div class="searchBarButtonArea">
        
        <input type="button" value="{function="localize('Refresh')"}" onclick="navigateTo(window.location.href)">
        
        <div class="searchBarButtonAreaLeft">
            <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=settings&cat=admin')">
        </div>
    </div>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; margin: 0 auto; color: black; width: 100%;">
       <div style="padding-top: 40px;">
           
        <table style="width: 100%;">
            <thead>
                <th><a href="#pantheraCookie" onclick="selectTab(this); return false;">pantheraCookie</a></th>
                <th><a href="#_cookie" onclick="selectTab(this); return false;">$_COOKIE</a></th>
                <th><a href="#pantheraSession" onclick="selectTab(this); return false;">pantheraSession</a></th>
                <th><a href="#_get" onclick="selectTab(this); return false;">$_GET</a></th>
                <th><a href="#_post" onclick="selectTab(this); return false;">$_POST</a></th>
                <th><a href="#_server" onclick="selectTab(this); return false;">$_SERVER</a></th>
            </thead>
            
            <tbody>
                <tr>
                    <td style="border: 1px solid #B8BCBF; word-break: break-all;" id="tabContent" colspan="6">
                        
                        <!-- tabs content -->
                        <div {if="$view != 'pantheraCookie' and $view != ''"}style="display: none;"{/if} id="pantheraCookie" class="dumpInputTabs">
                            {$pantheraCookie}<br>
                            <b>{function="localize('Examples of usage', 'debug')"}:</b>
                            <i>
                            <br>&nbsp;$panthera -> session -> cookies -> exists('Created');
                            <br>&nbsp;$panthera -> session -> cookies -> set('Name', 'Damien', time()+60);
                            <br>&nbsp;$panthera -> session -> cookies -> get('Name');
                            <br>&nbsp;$panthera -> session -> cookies -> remove('Name');
                            <br>&nbsp;$panthera -> session -> cookies -> getAll();</i>
                        </div>
                        
                        <div {if="$view != '_cookie'"}style="display: none;"{/if} id="_cookie" class="dumpInputTabs">
                            {$cookie}
                        </div>
                        
                        <div {if="$view != 'pantheraSession'"}style="display: none;"{/if} id="pantheraSession" class="dumpInputTabs">
                            {$pantheraSession}<br>
                            <b>{function="localize('Examples of usage', 'debug')"}:</b>
                            <i>
                            <br>&nbsp;$panthera -> session -> exists('Name');
                            <br>&nbsp;$panthera -> session -> set('Name', 'Damien');
                            <br>&nbsp;$panthera -> session -> get('Name');
                            <br>&nbsp;$panthera -> session -> remove('Name');
                            <br>&nbsp;$panthera -> session -> getAll();</i>
                        </div>
                        
                        <div {if="$view != '_get'"}style="display: none;"{/if} id="_get" class="dumpInputTabs">
                            {$GET}
                        </div>
                        
                        <div {if="$view != '_get'"}style="display: none;"{/if} id="_post" class="dumpInputTabs">
                            {$POST}
                        </div>
                        
                        <div {if="$view != '_server'"}style="display: none;"{/if} id="_server" class="dumpInputTabs">
                            {$SERVER}
                        </div>
                    </td>
                </tr>
            </tbody>
       </table>
       </div>
    </div>
</div>
