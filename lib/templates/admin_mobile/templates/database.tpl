    {include="header.tpl"}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=debug">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Database management', 'database')"}</a></li>
      </ul>
    </nav>
    
    <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
                <li class="list-divider">{function="localize('Connection informations', 'database')"}:</li>
                
                {loop="$sql_attributes"}
                <li class="list-item-two-lines">
                        <div>
                            <h3>{$value.value}</h3>
                            <p>{$value.name}</p>
                        </div>
                </li>
                {/loop}
                
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
    {include="footer.tpl"}
