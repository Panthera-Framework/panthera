    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin');" data-transition="push">{function="localize('Dash')"}</a></li>
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

              <img src="http://graph.facebook.com/{$user.id}/picture?width=200&height=200" height="200px" width="200px">

              <li class="list-item-two-lines">
                <a href="{$user.link}">
                    <h3>{$user.link}</h3>
                    <p>{function="localize('Link', 'facebook')"}</p>
                </a>
              </li>

              <li class="list-item-two-lines">
                <div>
                    <h3>{function="localize($user.gender, 'facebook')"}</h3>
                    <p>{function="localize('Gender', 'facebook')"}</p>
                </div>
              </li>

              <li class="list-item-two-lines">
                <div>
                    <h3>{$user.timezone}</h3>
                    <p>{function="localize('Timezone', 'facebook')"}</p>
                </div>
              </li>

              <li class="list-item-two-lines">
                <div>
                    <h3>{$user.locale}</h3>
                    <p>{function="localize('Locale', 'facebook')"}</p>
                </div>
              </li>

              <br>

              <button class="btn-block" onclick="synchronizeData();">{function="localize('Synchronize', 'facebook')"}</button>

            </ul>
        </ul>
     </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">

    /**
      * Save information from Facebook about user to database
      *
      * @author Mateusz Warzyński
      */

    function synchronizeData()
    {
        panthera.jsonPOST({ url: '?display=facebook&cat=admin&action=synchronize', data: ''});
        return false;
    }
    </script>
   <!-- End of JS code -->