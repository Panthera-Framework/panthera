    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=database">{function="localize('Database management', 'database')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Database backup', 'database')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="gallery" class="tab-item active">
                <ul class="list inset">
                    <li class="list-divider">{function="localize('Avaliable dumps', 'database')"}</li>

                   {loop="$dumps"}
                    <li class="list-item-two-lines">
                     <a href="{$AJAX_URL}?display=sqldump&get={$value.name}&_bypass_x_requested_with">
                        <h3>{$value.name}</h3>
                        <p>{$value.size} / {$value.date}</p>
                     </a>
                    </li>
                   {/loop}

                   <button class="btn-block" onclick="makeDump();">{function="localize('Create backup', 'database')"}</button>

                </ul>
            </li>
        </ul>
      </div>
    </div>

    <!-- JS code -->
    <script>
    /**
      * Make dump
      *
      * @author Mateusz Warzyński
      */

    function makeDump()
    {
        panthera.jsonPOST({ url: '?display=sqldump', data: 'dump=True', success: function (response) {
                if (response.status == "success")
                    window.location = "?display=sqldump";
            }
        });
        return false;
    }

    </script>
    <!-- End of JS code -->