{include="buttons"}

<script type="text/javascript">
    $(document).ready(function() {
        window.setTimeout("navigateTo('?_nextstep=True');", 1200);
    });
</script>

<div class="header">
        <h1>{function="localize('Configuring', 'installer')"}</h1>
        <div style="margin-left: 5px;"><span>{function="localize('Installer is now configuring base modules', 'installer')"}</span></div>
</div>

<div class="content" style="margin-left: 0px; padding: 0;">
    <div style="margin: 0 auto; margin-top: 10%; width: 85%; color: white; text-align: center;">
        {$spinnerStepMessage} <br><br><img src="{$PANTHERA_URL}/images/installer/ajax-loader.gif">
    </div>
    
    {if="isset($spinnerStepTable)"}
    <table class="table" style="width: 40%; margin: 0 auto; margin-top: 30px; margin-bottom: 60px;">
        <tbody>
            {$i=0}
            {loop="$spinnerStepTable"}
            <tr>
                {$i=$i+1}
                {$v=$value}
                
                {if="is_array($value)"}
                {loop="$value"}
                <td class="greenColumn" style="color: black; border: 0px; background: {if="$i % 2"}#ffffca;{else}#e7ffd4;{/if}">
                    {$value}
                </td>
                {/loop}
                {else}
                <td class="greenColumn" style="color: black; border: 0px; background: {if="$i % 2"}#ffffca;{else}#e7ffd4;{/if}">
                    {$value}
                </td>
                {/if}
            </tr>
            {/loop}
        </tbody>
    </table>
    {/if}
</div>