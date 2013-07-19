    {include="header.tpl"}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('User account', 'settings')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">

             <li class="list-divider">{function="localize('Panel with informations about user.', 'settings')"}</li>

              <li class="list-item-two-lines">
                    <h3>{$user_login}</h3>
                    <p>{function="localize('Login', 'settings')"}</p>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>*******</h3>
                    <p>{function="localize('Password', 'settings')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3><img src="{$profile_picture}" height="28px" width="auto"></h3>
                    <p>{function="localize('Avatar', 'settings')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$full_name|ucfirst}</h3>
                    <p>{function="localize('Full name', 'settings')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$primary_group}</h3>
                    <p>{function="localize('Primary group', 'settings')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                    <h3>{$joined}</h3>
                    <p>{function="localize('Joined', 'settings')"}</p>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$language|ucfirst}</h3>
                    <p>{function="localize('Language', 'settings')"}</p>
                </a>
              </li>

              <br><br>



              <li class="list-item-single-line">
                <a href="?display=settings&action=users" data-ignore="true">
                    <button class="btn-block">{function="localize('Back')"}</button>
                </a>
              </li>

            </ul>
        </ul>
     </div>

    </div>
    {include="footer.tpl"}
