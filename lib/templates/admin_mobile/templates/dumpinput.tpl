    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a onclick="navigateTo('?display=debug&cat=admin');">{function="localize('Debugging center')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Dumpinput', 'debug')"}</a></li>
      </ul>
    </nav>


    <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">


                <li class="list-divider">$_COOKIE</li>
                <li class="list-item-multi-line">
                        <p>{$cookie}</p>
                </li>

                <li class="list-divider">$panthera->session->cookies</li>
                <li class="list-item-multi-line">
                        <p>{$pantheraCookie}<br>
                        <i>
                            <br>&nbsp;$panthera -> session -> cookies -> exists('Created');
                            <br>&nbsp;$panthera -> session -> cookies -> set('Name', 'Mathew', time()+60);
                            <br>&nbsp;$panthera -> session -> cookies -> get('Name');
                            <br>&nbsp;$panthera -> session -> cookies -> remove('Name');
                            <br>&nbsp;$panthera -> session -> cookies -> getAll();
                        </i>
                        </p>
                </li>

                <li class="list-divider">$panthera->session</li>
                <li class="list-item-multi-line">
                        <p>{$pantheraSession}<br>
                        <b>{function="localize('Examples of usage', 'debug')"}:</b>
                        <i>
                            <br>&nbsp;$panthera -> session -> exists('Name');
                            <br>&nbsp;$panthera -> session -> set('Name', 'Mathew');
                            <br>&nbsp;$panthera -> session -> get('Name');
                            <br>&nbsp;$panthera -> session -> remove('Name');
                            <br>&nbsp;$panthera -> session -> getAll();
                        </i>
                        </p>
                </li>

                <li class="list-divider">$_SESSION</li>
                <li class="list-item-multi-line">
                        <p>{$SESSION}</p>
                </li>

                <li class="list-divider">$_GET<li>
                <li class="list-item-multi-line">
                        <p>{$GET}</p>
                </li>

                <li class="list-divider">$_POST<li>
                <li class="list-item-multi-line">
                        <p>{$POST}</p>
                </li>

                <li class="list-divider">$_SERVER<li>
                <li class="list-item-multi-line">
                        <p>{$SERVER}</p>
                </li>

            </ul>
        </ul>
     </div>