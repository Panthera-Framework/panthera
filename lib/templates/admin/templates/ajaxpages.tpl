{$site_header}

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
</div>

<div class="ajax-content" style="text-align: center;">
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
                        <b>{function="localize('Objective interface', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Modification time', 'ajaxpages')"}</b>
                    </th>
                    
                    <th>
                        <b>{function="localize('Full path', 'ajaxpages')"}</b>
                    </th>
                </tr>
            </thead>
            <tbody class="hovered">
                 {if="$pages"}
                 {loop="$pages"}
                    <tr>
                        <td>{$value.location}</td>
                        <td>{$value.directory}</td>
                        <td><a href="{$value.link}&cat=admin" class="ajax_link">{$value.name}</a></td>
                        <td>{if="$value.objective"}{function="localize('Yes')"}{else}{function="localize('No')"}{/if}</td>
                        <td>{$value.modtime}</td>
                        <td><small>{$value.path}</small></td>
                    </tr>
                 {/loop}
                 {else}
                    <tr>
                        <td colspan="6" style="text-align: center;">{function="localize('No data to display', 'admin')"}</td>
                    </tr>
                 {/if}
            </tbody>
    </table>
</div>
