    {include="header.tpl"}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Facebook', 'facebook')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
     <div class="slider tab-slider">
        <ul>
            <ul class="list inset">
                                
             <li class="list-divider">{function="localize('Facebook integration.', 'facebook')"}</li>

              <li class="list-item-two-lines">
                  <div>
                    <h3>{$user.id}</h3>
                    <p>{function="localize('ID', 'facebook')"}</p>
                  </div>
              </li>

              <li class="list-item-two-lines">
                <div>
                    <h3>{$user.name}</h3>
                    <p>{function="localize('Name', 'facebook')"}</p>
                </div>
              </li>

              <li class="list-item-two-lines">
                <div>
                    <h3><img src="http://graph.facebook.com/{$user.id}/picture?width=200&height=200" height="28px" width="auto"></h3>
                    <p>{function="localize('Avatar', 'facebook')"}</p>
                </div>
              </li>

              <li class="list-item-two-lines">
                <a href="{$user.link}">
                    <h3>{$user.link}</h3>
                    <p>{function="localize('Link', 'facebook')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{function="localize($user.gender, 'facebook')"}</h3>
                    <p>{function="localize('Gender', 'facebook')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                    <h3>{$user.timezone}</h3>
                    <p>{function="localize('Timezone', 'facebook')"}</p>
              </li>

              <li class="list-item-two-lines">
                <a href="#" data-ignore="true">
                    <h3>{$user.locale}</h3>
                    <p>{function="localize('Locale', 'facebook')"}</p>
                </a>
              </li>

              <br>
              
              <button class="btn-block" onclick="synchronizeData();">{function="localize('Synchronize', 'facebook')"}</button>

            </ul>
        </ul>
     </div>

    </div>
    
    <script type="text/javascript">

    /**
      * Save information from Facebook about user to database
      *
      * @author Mateusz Warzyński
      */
    
    function synchronizeData()
    {
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=facebook&action=synchronize', data: '', messageBox: 'userinfoBox'});
        return false;
    }
    </script>
    
    {include="footer.tpl"}
