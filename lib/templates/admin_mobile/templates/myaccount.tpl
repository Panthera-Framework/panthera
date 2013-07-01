    {include 'header.tpl'}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash" data-transition="push">{"Dash"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"User account"|localize:settings}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">

             <li class="list-divider">{"Panel with informations about user."|localize:settings}</li>

              <li class="list-item-two-lines">
                    <h3>{$user_login}</h3>
                    <p>{"Login"|localize:settings}</p>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>*******</h3>
                    <p>{"Password"|localize:settings}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3><img src="{$profile_picture}" height="28px" width="auto"></h3>
                    <p>{"Avatar"|localize:settings}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$full_name|ucfirst}</h3>
                    <p>{"Full name"|localize:settings}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$primary_group}</h3>
                    <p>{"Primary group"|localize:settings}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                    <h3>{$joined}</h3>
                    <p>{"Joined"|localize:settings}</p>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$language|ucfirst}</h3>
                    <p>{"Language"|localize:settings}</p>
                </a>
              </li>

              <br><br>
              
              
              
              <li class="list-item-single-line">
                <a href="?display=settings&action=users" data-ignore="true">
                    <button class="btn-block">{"Back"|localize}</button>
                </a>
              </li>

            </ul>
        </ul>
     </div>

    </div>
    {include 'footer.tpl'}
