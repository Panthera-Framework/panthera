{$site_header}

{include="ui.titlebar"}
<div class="ajax-content" style="text-align: center;">
    <div style="margin: 0 auto; display: inline-block; width: 80%;">
        <h1>{function="localize('Pages index', 'ajaxpages')"}</h1>
    </div><br>
    <table style="display: inline-block;">
            <thead>
                <tr>
                    <th>
                        <b>{function="localize('Location', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Directory', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Page name', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Modification time', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Full path', 'ajaxpages')"}</b>
                    </th>
                </tr>
            </thead>
            <tbody>
                 {loop="$pages"}
                    <tr>
                        <td>{$value.location}</td>
                        <td>{$value.directory}</td>
                        <td><a href="{$value.link}&cat=admin" class="ajax_link">{$value.name}</a></td>
                        <td>{$value.modtime}</td>
                        <td><small>{$value.path}</small></td>
                    </tr>
                 {/loop}
            </tbody>
    </table>
</div>
