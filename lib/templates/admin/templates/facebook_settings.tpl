{$site_header}
<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

var spinner = new panthera.ajaxLoader($('#facebookSettings'));

/**
  * Save facebook variables to config overlay
  *
  * @author Mateusz Warzyński
  */
    
function saveFacebook()
{
    appID = $('#appID_value').val();
    Secret = $('#secret_value').val();
    
    panthera.jsonPOST({ url: '?display=facebook&cat=admin&action=settings&subaction=save', data: 'appid='+appID+'&secret='+Secret, spinner: spinner, success: function (response) {
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

	{include="ui.titlebar"}
    
    <div class="grid-1" style="position: relative;" id="facebookSettings">
         <table class="gridTable">

            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Key')"}</th>
                    <th colspan="2">{function="localize('Value')"}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {function="localize('App configuration', 'facebook')"}
                    	<a href="#" onclick="saveFacebook();">
                        	<img src="{$PANTHERA_URL}/images/admin/ui/save.png" style="max-height: 50px; float: right;" alt="{function="localize('Save')"}">
                    	</a>
                    </em></td>
                </tr>
            </tfoot>
            
            <tbody>
                <tr>
                    <td>AppID</td>
                    <td><input type="text" id="appID_value" value="{$appid}" style="width: 500px;"></td>
                </tr>
                
                <tr>
                    <td>Secret</td>
                    <td><input type="text" id="secret_value" value="{$secret}" style="width: 500px;"></td>
                </tr>
            </tbody>
         </table>
      </div>