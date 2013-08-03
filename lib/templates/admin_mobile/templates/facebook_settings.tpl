    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Facebook', 'facebook')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
        <label>{function="localize('App configuration', 'facebook')"}</label>
        <input type="text" class="input-text" id="appID_value" placeholder="AppID" value="{$appid}" onfocus="this.value = ''">
        <input type="text" class="input-text" id="secret_value" placeholder="Secret" value="{$secret}" onfocus="this.value = ''"><br><br>
        <button class="btn-block" onclick="saveFacebook();" id="save_button">{function="localize('Save')"}</button>
    </div>

   <!-- JS code -->
    <script type="text/javascript">

    /**
      * Save facebook variables to config overlay
      *
      * @author Mateusz Warzyński
      */

    function saveFacebook()
    {
        appID = $('#appID_value').val();
        Secret = $('#secret_value').val();

        panthera.jsonPOST({ url: '?display=facebook&cat=admin&action=settings&subaction=save', data: 'appid='+appID+'&secret='+Secret, success: function (response) {
                if (response.status == "success")
                {
                   jQuery('#save_button').attr("disabled", "disabled");
                   jQuery('#save_button').animate({ height:'toggle'});
                   setTimeout("jQuery('#save_button').removeAttr('disabled');", 2500);
                   setTimeout("jQuery('#save_button').animate({ height:'toggle' });", 2500);
                }
            }
        });
        return false;
    }
    </script>
   <!-- End of JS code -->
