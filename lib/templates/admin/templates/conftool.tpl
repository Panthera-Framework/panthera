<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

/**
  * Save variable to database
  *
  * @author Mateusz Warzyński
  */


function saveVariable(id)
{
    value = jQuery('#value_'+id).val();

    panthera.jsonPOST({ url: '{$AJAX_URL}?display=conftool&action=change', data: 'id='+id+'&value='+value, messageBox: 'userinfoBox', success: function (response) {
            if (response.status == "success")
            {
               jQuery('#button_'+id).attr("disabled", "disabled");
               jQuery('#button_'+id).animate({ height:'toggle'});
               setTimeout("jQuery('#button_"+id+"').removeAttr('disabled');", 2500);
               setTimeout("jQuery('#button_"+id+"').animate({ height:'toggle' });", 2500);
            }
        }
    });

    return false;

}

</script>

        <div class="titlebar">{"Configuration editor"|localize:conftool} - {"Administration tool for developers and administrators."|localize:conftool}{include file="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
         <table class="gridTable">

            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">{"Key"|localize}</th>
                    <th colspan="2">{"Value"|localize}</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2" class="rounded-foot-left"><em>Panthera - {"Configuration editor"|localize:conftool} <input type="button" value="{"Back"|localize}" onclick="navigateTo('{navigation::getBackButton()}');" style="float: right;"> <input type="button" value="{"Manage permissions"|localize}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_update_config_overlay', 1024, 'upload_popup');" style="float: right; margin-right: 7px;"></em></td>
                </tr>
            </tfoot>

            <tbody>
              {foreach from=$a key=k item=i}
                <tr>
                    <td>

                        <small>{$i[0]|localize:type}</small> &nbsp;<b>{if isset($i[2])}{$i[2]}{else}{$k}{/if}</b>

                    </td>
                    <td>
                        {if $i[0] == 'bool'}
                            <select id="value_{$k}" style="width: 500px;"><option value="0">{"No"|localize}</option><option value="1"{if $i[1] eq "1"} selected{/if}>{"Yes"|localize}</option></select>
                        {else}
                        
                            {if $i[0] == 'int'}
                                {$type = "number"}
                            {else}
                                {$type = "text"}
                            {/if}
                            
                            <input type="{$type}" value='{$i[1]}' id="value_{$k}" style="width: 500px;">
                        {/if}
                        <input type="button" value="{"Save"|localize:messages}" id="button_{$k}" onclick="saveVariable('{$k}');">
                        <span style="font-color: red;"><div id="errmsg_{$k}" style="display: none;"></div></span>
                    </td>
                </tr>
              {/foreach}
            </tbody>
           </table>

      </div>
</article>

