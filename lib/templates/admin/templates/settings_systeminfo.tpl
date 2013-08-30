{include="ui.titlebar"}

        <div class="grid-1">
            <h1>{function="localize('Developer informations', 'settings')"}</h1><br><br>
            <table class="gridTable">
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

            <br><br>

            <h1>{function="localize('List of defined constants', 'settings')"}</h1><br>
            <table class="gridTable">
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

            <br><br>

            <h1>{function="localize('List of access controls for current user', 'settings')"}</h1><br>
            <table class="gridTable">
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

            {if="isset($clientInfo)"}            
            <br><br>

            <h1>{function="localize('Detected browser and operating system', 'settings')"}</h1><br>
            <table class="gridTable">
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
