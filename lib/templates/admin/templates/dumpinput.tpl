<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>

    <div class="titlebar">Dump input - {function="localize('Get all input variables listed', 'debug')"}{include="_navigation_panel"}</div>

    <div class="grid-1">
        <h2>$_COOKIE</h2>
        <div class="blueLog">
            {$cookie}<br><br>
        </div>

        <h2>$panthera->session->cookies</h2>
        <div class="blueLog">
            {$pantheraCookie}<br>

            <b>{function="localize('Examples of usage', 'debug')"}:</b>
            <i>
            <br>&nbsp;$panthera -> session -> cookies -> exists('Created');
            <br>&nbsp;$panthera -> session -> cookies -> set('Name', 'Damien', time()+60);
            <br>&nbsp;$panthera -> session -> cookies -> get('Name');
            <br>&nbsp;$panthera -> session -> cookies -> remove('Name');
            <br>&nbsp;$panthera -> session -> cookies -> getAll();</i>
        </div>

        <h2>$panthera->session</h2>
        <div class="blueLog">
            {$pantheraSession}<br>

            <b>{function="localize('Examples of usage', 'debug')"}:</b>
            <i>
            <br>&nbsp;$panthera -> session -> exists('Name');
            <br>&nbsp;$panthera -> session -> set('Name', 'Damien');
            <br>&nbsp;$panthera -> session -> get('Name');
            <br>&nbsp;$panthera -> session -> remove('Name');
            <br>&nbsp;$panthera -> session -> getAll();</i>
        </div>

        <h2>$_SESSION</h2>
        <div class="blueLog">
            {$SESSION}<br><br>
        </div>

        <h2>$_GET</h2>
        <div class="blueLog">
            {$GET}<br><br>
        </div>

        <h2>$_POST</h2>
        <div class="blueLog">
            {$POST}<br><br>
        </div>

        <h2>$_SERVER</h2>
        <div class="blueLog">
            {$SERVER}<br><br>
        </div>
    </div>

