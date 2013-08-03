    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin');" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Mailing', 'mailing')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="mailing" class="tab-item active">
                <ul class="list inset">
                  {loop="$mail_attributes"}
                   <li class="list-divider">{function="localize($value.name, 'mailing')"}</li>
                   <li class="list-item-single-line">
                     {if="is_bool($value.value)"}
                      <a id="id_{$value.record_name}" onclick="togglePhpMail('{$value.record_name}'); $('#button_{$value.record_name}').slideDown();" style="display: inline;">{if="$value.value == True"} {function="localize('True')"} {else} {function="localize('False')"} {/if}</a>
                      <input type="text" id="value_{$value.record_name}" {if="$value.value == True"} value='1' {else} value='0' {/if} style="display: none;">
                     {elseif="$value.record_name == 'mailing_password'"}
                      <input type="password" placeholder="{function="localize($value.name, 'mailing')"}" value='{$value.value}' id="value_{$value.record_name}" class="input-text inline" onfocus="this.value = ''; $('#button_{$value.record_name}').slideDown();">
                     {else}
                      <input type="text" placeholder="{function="localize($value.name, 'mailing')"}" value='{$value.value}' id="value_{$value.record_name}" class="input-text inline" onfocus="$('#button_{$value.record_name}').slideDown();">
                     {/if}
                      <button class="btn-small" onclick="saveVariable('{$value.record_name}');" style="float: right; display: none;" id="button_{$value.record_name}">{function="localize('Save')"}</button>
                   </li>
                  {/loop}

                  <br><br>

                  <li class="list-divider">{function="localize('Send an e-mail', 'mailing')"}</li>
                   <form action="{$AJAX_URL}?display=mailing&cat=admin&action=send" method="POST" id="mail_form">
                    <input type="text" name="subject" placeholder="{function="localize('Subject', 'mailing')"}" class="input-text" autocomplete="off">
                    <input type="text" name="recipients" placeholder="{function="localize('Recipients', 'mailing')"}" class="input-text" autocomplete="off">
                    <input type="email" name="from" placeholder="{function="localize('From', 'mailing')"}" class="input-text" autocomplete="off" value="{$last_from}">
                    <input type="text" name="body" placeholder="{function="localize('Content', 'mailing')"}" class="input-text" autocomplete="off">
                    <input type="submit" class="btn-block" value="{function="localize('Send', 'mailing')"}" id="send_button">
                   </form>
               </ul>
            </li>
        </ul>

      </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">
    $(document).ready(function(){

        $('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

        /**
          * Send mail mobile
          *
          * @author Mateusz Warzyński
          */

        $('#mail_form').submit(function () {
            panthera.jsonPOST({ data: '#mail_form', success: function (response) {
                    if (response.status == "success") {
                        $("#send_button").attr("disabled", "disabled");
                        $("#send_button").animate({ height:'toggle'});
                        setTimeout("$('#send_button').removeAttr('disabled');", 2500);
                        setTimeout("$('#send_button').animate({ height:'toggle' });", 2500);
                    }
                }
            });
            return false;
        });
    });

    /**
      * Save mailing variable
      *
      * @author Mateusz Warzyński
      */

    function saveVariable(id)
    {
        value = jQuery('#value_'+id).val();

        panthera.jsonPOST({ url: '?display=conftool&cat=admin&action=change', data: 'id='+id+'&value='+value, success: function (response) {
                if (response.status == "success") {
                    jQuery('#button_'+id).attr("disabled", "disabled");
                    jQuery('#button_'+id).animate({ height:'toggle'});
                    setTimeout("jQuery('#button_"+id+"').removeAttr('disabled');", 2500);
                    setTimeout("jQuery('#button_"+id+"').animate({ height:'toggle' });", 2500);
                }
            }
        });

        return false;

    }

    /**
      * Toggle PHP_MAIL bool value
      *
      * @author Mateusz Warzyński
      */

    function togglePhpMail(value)
    {
        variable = $('#value_'+value).val();

        if (variable == 1)
        {
            $('#value_'+value).val('0');
            $('#id_'+value).text("{function="localize('False')"}");
        }

        if (variable == 0)
        {
            $('#value_'+value).val('1');
            $('#id_'+value).text("{function="localize('True')"}");
        }
    }

    </script>
   <!-- End of JS code -->