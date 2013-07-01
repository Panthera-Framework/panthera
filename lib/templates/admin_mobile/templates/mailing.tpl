    {include 'header.tpl'}
    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash" data-transition="push">{"Dash"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"Mailing"|localize:mailing}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">
      
        <ul>
            <li id="mailing" class="tab-item active">
                <ul class="list inset">
                  {foreach from=$mail_attributes key=k item=v}
                   <li class="list-divider">{"$v.name"|localize:mailing}</li>
                   <li class="list-item-single-line">
                     {if is_bool($v.value)}
                      <a id="php_mail" onclick="togglePhpMail();" style="display: inline;">{if $v.value eq "1"} {"True"|localize} {else} {"False"|localize} {/if}</a>
                      <input type="text" id="value_{$v.record_name}" {if $v.value eq "1"} value="1" {else} value="0" {/if} style="display: none;">
                     {elseif $v.record_name eq "mailing_password"}
                      <input type="password" placeholder="{"$v.name"|localize:mailing}" value='{$v.value}' id="value_{$v.record_name}" class="input-text inline" onfocus="this.value = ''">
                     {else}
                      <input type="text" placeholder="{"$v.name"|localize:mailing}" value='{$v.value}' id="value_{$v.record_name}" class="input-text inline">
                     {/if}
                      <button class="btn-small" onclick="saveVariable('{$v.record_name}');" style="float: right;" id="button_{$v.record_name}">{"Save"|localize}</button>
                   </li>
                  {/foreach}
                  
                  <br><br>
                 
                  <li class="list-divider">{"Send an e-mail"|localize:mailing}</li>
                   <form action="{$AJAX_URL}?display=mailing&action=send" method="POST" id="mail_form">
                    <input type="text" name="subject" placeholder="{"Subject"|localize:mailing}" class="input-text" autocomplete="off">
                    <input type="text" name="recipients" placeholder="{"Recipients"|localize:mailing}" class="input-text" autocomplete="off">
                    <input type="email" name="from" placeholder="{"From"|localize:mailing}" class="input-text" autocomplete="off" value="{$last_from}">
                    <input type="text" name="body" placeholder="{"Content"|localize:mailing}" class="input-text" autocomplete="off">
                    <input type="submit" class="btn-block" value="{"Send"|localize:mailing}" id="send_button">
                   </form>
               </ul>    
            </li>
        </ul>
        
      </div>
    </div>

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

        panthera.jsonPOST({ url: '?display=conftool&action=change', data: 'id='+id+'&value='+value, success: function (response) {
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
    
    function togglePhpMail()
    {
        variable = $('#value_mailing_use_php').val();
        
        if (variable == 1)
        {
            $("#value_mailing_use_php").val('0');
            $('#php_mail').text("{"False"|localize}");
        }
        
        if (variable == 0)
        {
            $("#value_mailing_use_php").val('1');
            $('#php_mail').text("{"True"|localize}");
        }
    }

    </script>

    {include 'footer.tpl'}
