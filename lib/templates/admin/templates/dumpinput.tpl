{include="ui.titlebar"}

<div class="ajax-content">
    <div style="display: inline-block; margin: 0 auto; color: black; width: 100%;">
        <h2>$_COOKIE</h2>
        <table><tbody><tr><td style="border: 1px solid #B8BCBF; word-break: break-all; ">
            {$cookie}<br><br>
        </td></tr></tbody></table>


        <h2>$panthera->session->cookies</h2>
        <table><tbody><tr><td style="border: 1px solid #B8BCBF; word-break: break-all;">
            {$pantheraCookie}<br>
            <b>{function="localize('Examples of usage', 'debug')"}:</b>
            <i>
            <br>&nbsp;$panthera -> session -> cookies -> exists('Created');
            <br>&nbsp;$panthera -> session -> cookies -> set('Name', 'Damien', time()+60);
            <br>&nbsp;$panthera -> session -> cookies -> get('Name');
            <br>&nbsp;$panthera -> session -> cookies -> remove('Name');
            <br>&nbsp;$panthera -> session -> cookies -> getAll();</i>
        </td></tr></tbody></table>


        <h2>$panthera->session</h2>
        <table><tbody><tr><td style="border: 1px solid #B8BCBF; word-break: break-all;">
            {$pantheraSession}<br>
            <b>{function="localize('Examples of usage', 'debug')"}:</b>
            <i>
            <br>&nbsp;$panthera -> session -> exists('Name');
            <br>&nbsp;$panthera -> session -> set('Name', 'Damien');
            <br>&nbsp;$panthera -> session -> get('Name');
            <br>&nbsp;$panthera -> session -> remove('Name');
            <br>&nbsp;$panthera -> session -> getAll();</i>
        </td></tr></tbody></table>

        <h2>$_SESSION</h2>
        <table><tbody><tr><td style="border: 1px solid #B8BCBF; word-break: break-all;">
            {$SESSION}<br><br>
        </td></tr></tbody></table>


        <h2>$_GET</h2>
        <table><tbody><tr><td style="border: 1px solid #B8BCBF; word-break: break-all;">
            {$GET}<br><br>
        </td></tr></tbody></table>


        <h2>$_POST</h2>
        <table><tbody><tr><td style="border: 1px solid #B8BCBF; word-break: break-all;">
            {$POST}<br><br>
        </td></tr></tbody></table>


        <h2>$_SERVER</h2>
        <table><tbody><tr><td style="border: 1px solid #B8BCBF; word-break: break-all;">
            {$SERVER}<br><br>
        </td></tr></tbody></table>
    </div>
</div>
