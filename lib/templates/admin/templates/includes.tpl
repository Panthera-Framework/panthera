{$site_header}

{include="ui.titlebar"}
<div class="ajax-content" style="text-align: center;">
    <div style="margin: 0 auto; display: inline-block;">
        <h1>{function="localize('Included files', 'includes')"}</h1>
        <br><br>
        <table>
            <thead>
                <tr>
                    <tr><th colspan="2"><b>{function="localize('Files', 'includes')"}:</b></th></tr>
                </tr>
            </thead>
            <tbody>
                {$back_btn=base64_encode('?display=includes&cat=admin')}
                {loop="$files"}
                    <tr><td><a href="#" onclick="navigateTo('?display=browsefile&cat=admin&path={$value}&back_btn={$back_btn}'); return false;">{$value}</a></td></tr>
                {/loop}
            </tbody>
        </table>
    </div>
</div>
