    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Database management', 'database')"}</a></li>
      </ul>
    </nav>

    <div class="content">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
                <li class="list-divider">{function="localize('Connection informations', 'database')"}:</li>

                {loop="$sql_attributes"}
                <li class="list-item-multi-lines">
                        <div>
                            <p>{$value.value}</p>
                            <p style="font-size: 12px; color: #bbb;">{$value.name}</p>
                        </div>
                </li>
                {/loop}

                <button class="btn-block" onclick="navigateTo('?display=sqldump&cat=admin');">{function="localize('Manage backups', 'database')"}</button>

                <br><br>

                <li class="list-divider">Panthera - {function="localize('database driver configuration', 'database')"}:</li>

                {loop="$panthera_attributes"}
                <li class="list-item-two-lines">
                        <div>
                            <h3>{$value.value}</h3>
                            <p>{$value.name}</p>
                        </div>
                </li>
                {/loop}
            </ul>
        </ul>
     </div>
    </div>
