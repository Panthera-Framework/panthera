{include="buttons"}
<h1>{function="localize('Requirements and technical details', 'installer')"}</h1>

<span class="description">{function="localize('The list below describes required modules and their versions that are needed to correctly install and run this application', 'installer')"}.</span>

<table class="table" style="width: 80%; margin-left: 10px; margin: 0 auto; margin-top: 30px; margin-bottom: 30px;">
    <thead>
        <tr>
            <td><b>{function="localize('Requirement', 'installer')"}</b></td>
            <td><b>{function="localize('Installed', 'installer')"}</b></td>
            <td><b>{function="localize('Required', 'installer')"}</b></td>
        </tr>
    </thead>

    <tbody>
        {loop="$requirements"}
        <tr style="{if="$value.passed == False"}background: #FFD0D0;{elseif="$value.passed === 'optional'"}background: #FFFFD2;{else}background: rgba(223, 253, 230, 0.46);{/if}">
            <td><i>{$key}</i></td>
            <td>{$value.installed}</td>
            <td>{$value.required}</td>
        </tr>
        {/loop}
    </tbody>
</table>
