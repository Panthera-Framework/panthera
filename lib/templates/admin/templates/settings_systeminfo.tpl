<div class="titlebar">{"Informations about system"|localize:settings} - {"Panel with main settings and informations about Panthera"|localize:settings}</div>

        <div class="grid-1">
            <h1>{"Developer informations"|localize:settings}</h1>

            <br>

            <table class="gridTable">
             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{"Key"|localize:messages}</th>
                    <th scope="col">{"Value"|localize:messages}</th>
                </tr>
             </thead>

             <tbody>
               {foreach from=$settings_list key=k item=v}
                <tr>
                    <td>{$k}</td>
                    <td>{$v}</td>
                </tr>
               {/foreach}
             </tbody>
            </table>

            <br><br>

            <h1>{"List of defined constants"|localize:settings}</h1><br>
            <table class="gridTable">
             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{"Constant"|localize:settings}</th>
                    <th scope="col">{"Value"|localize:messages}</th>
                </tr>
             </thead>

             <tbody>
               {foreach from=$constants key=k item=v}
                <tr>
                    <td>{$k}</td>
                    <td>{$v}</td>
                </tr>
               {/foreach}
             </tbody>
            </table>

            <br><br>

            <h1>{"List of access controls for current user"|localize:settings}</h1><br>

            <table class="gridTable">
             <thead>
                <tr>
                    <th scope="col" style="width: 300px;">{"Key"|localize:messages}</th>
                    <th scope="col">{"Value"|localize:messages}</th>
                </tr>
             </thead>

             <tbody>
               {foreach from=$acl_list key=k item=v}
                <tr>
                    <td>{$k}</td>
                    <td>{$v}</td>
                </tr>
               {/foreach}
             </tbody>
            </table>

        </div>