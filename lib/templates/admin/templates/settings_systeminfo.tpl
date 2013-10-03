{$site_header}

{include="ui.titlebar"}
<div class="ajax-content" style="text-align: center;">
    <div style="margin: 0 auto; display: inline-block;">
    
        <!-- Developer informations - variables. -->
    
        <h1>{function="localize('Developer informations', 'settings')"}</h1>
        <br><br>
        <table>
            <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('Key', 'messages')"}</th>
                    <th scope="col">{function="localize('Value', 'messages')"}</th>
                </tr>
            </thead>
            <tbody>
                {loop="$settings_list"}
                <tr>
                    <td>{$key}</td>
                    <td>{$value}</td>
                </tr>
                {/loop}
            </tbody>
        </table>
        
        <!-- List of all user-defined constants -->
        
        <br><br>
        <h1>{function="localize('List of defined constants', 'settings')"}</h1>
        <br>
        <table style="width: 768px;">
            <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('Constant', 'settings')"}</th>
                    <th scope="col">{function="localize('Value', 'messages')"}</th>
                </tr>
            </thead>
            <tbody>
                {loop="$constants"}
                <tr>
                    <td>{$key}</td>
                    <td>{$value}</td>
                </tr>
                {/loop}
            </tbody>
        </table>
        
        <!-- ACL -->
        
        {if="count($acl_list)"}
        <br><br>
        <h1>{function="localize('List of access controls for current user', 'settings')"}</h1>
        <br>
        <table>
            <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('Key', 'messages')"}</th>
                    <th scope="col">{function="localize('Value', 'messages')"}</th>
                </tr>
            </thead>
            <tbody>
                {loop="$acl_list"}
                <tr>
                    <td>{$key}</td>
                    <td>{$value}</td>
                </tr>
                {/loop}
            </tbody>
        </table>
        {/if}
        
        <!-- Browser info -->
        
        {if="isset($clientInfo)"}            
        <br><br>
        <h1>{function="localize('Detected browser and operating system', 'settings')"}</h1>
        <br>
        <table style="width: 768px;">
            <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{function="localize('Key', 'messages')"}</th>
                    <th scope="col">{function="localize('Value', 'messages')"}</th>
                </tr>
            </thead>
            <tbody>
                {loop="$clientInfo"}
                <tr>
                    <td>{$key}</td>
                    <td>{$value}</td>
                </tr>
                {/loop}
            </tbody>
        </table>
        {/if}
    </div>
</div>
