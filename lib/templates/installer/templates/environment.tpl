{include="buttons"}

<style type="text/css">
    .content .table thead p {
        font-size: 11px;
    }

    .content .table tbody tr {
        padding-left: 7px;
    }
    
    .content .table tbody tr td {
        padding: 9px;
        border-bottom: 2px solid #56687b;
    }

</style>

<div class="header">
        <h1>{function="localize('Requirements and technical details', 'installer')"}</h1>
        <div style="margin-left: 5px;"><span>{function="localize('The list below describes required modules and their versions that are needed to correctly install and run this application', 'installer')"}.</span></div>
</div>

<div class="content" style="margin-left: 0px;">
    <table class="table" style="width: 80%; margin: 0 auto; margin-top: 50px; margin-bottom: 50px; border: none; border-spacing: 0px;">
        <thead>
            <tr>
                <td><p>{function="localize('Requirement', 'installer')"}</p></td>
                <td><p>{function="localize('Installed', 'installer')"}</p></td>
                <td><p>{function="localize('Required', 'installer')"}</p></td>
            </tr>
        </thead>
    
        <tbody>
            {loop="$requirements"}
            <tr style="font-size: 13px; {if="$value.passed == False"}background: fff;{elseif="$value.passed === 'optional'"}background: #ffd4d4;{else}background: #e7ffd4;{/if}">
                <td><i>{$key}</i></td>
                <td>{$value.installed}</td>
                <td>{$value.required}</td>
            </tr>
            {/loop}
        </tbody>
    </table>
</div>