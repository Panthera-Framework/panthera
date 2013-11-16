{$site_header}

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Refresh', 'accessparser')"}" onclick="navigateTo(window.location);">
    </div>
</div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">

        <thead>
            <tr>
                <th>{function="localize('Client address', 'accessparser')"}</th>
                <th>{function="localize('Date', 'accessparser')"}</th>
                <th style="max-width: 100px;">{function="localize('Method', 'accessparser')"}</th>
                <th>{function="localize('Url request', 'accessparser')"}</th>
                <th>{function="localize('Status', 'accessparser')"}</th>
                <th>{function="localize('Browser headers', 'accessparser')"}</th>
            </tr>
        </thead>

        <tbody class="hovered">
            {loop="$lines"}
            <tr> 
                <td>{$value.client_address}</td>
                <td><small>{$value.time}</small><br>{$value.date}</td>
                <td>{$value.http_method}</td>
                <td><a href="{$value.referer}"><small>{function="end(explode('/', $value.url_request))"}</small></a></td>
                <td>{$value.status}</td>
                <td><small>{$value.browser_headers}</small></td>
            </tr>
            {/loop}
        </tbody>
    </table>
</div>