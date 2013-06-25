<div class="titlebar">{"Informations about system"|localize:settings} - {"Panel with main settings and informations about Panthera"|localize:settings}</div>

        <div class="grid-1">
            <h1>{"Developer informations"|localize:settings}</h2><br>

            <div id="grid" style="width: 100%; height: 400px;"></div>

            <br>
            <h1>{"List of access controls for current user"|localize:settings}</h1>
            <br>

            <div id="grid_acl" style="width: 100%; height: 250px;"></div>

            <script type="text/javascript">
            // data tables
            $(document).ready(function() {
                $('#grid').w2grid({
                    name: 'grid',
                    sortData: [ { field: 'key', direction: 'asc' } ],
                    columns: [
                        { field: 'key', caption: '{"Key"|localize}', size: '20%', sortable: true },
                        { field: 'value', caption: '{"Value"|localize}', size: '80%', sortable: false },
                    ],
                    records: [
                        {$i=1}
                        {foreach from=$settings_list key=k item=v}
                        {$i=$i+1}
                        { recid: {$i}, key: '{$k}', value: '{$v}' },
                        {/foreach}
                    ]
                });

                $('#grid_acl').w2grid({
                    name: 'grid_acl',
                    sortData: [ { field: 'key', direction: 'asc' } ],
                    columns: [
                        { field: 'key', caption: '{"Key"|localize}', size: '20%', sortable: true },
                        { field: 'value', caption: '{"Value"|localize}', size: '80%', sortable: false },
                    ],
                    records: [
                        {$i=1}
                        {foreach from=$acl_list key=k item=v}
                        {$i=$i+1}
                        { recid: {$i}, key: '{$k}', value: '{$v}' },
                        {/foreach}
                    ]
                });
            });


            function onAjaxUnload()
            {
                $().w2destroy('grid');
                $().w2destroy('grid_acl');
            }
        </script>

        </div>