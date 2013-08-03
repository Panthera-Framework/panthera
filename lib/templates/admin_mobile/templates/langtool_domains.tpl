    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=langtool&cat=admin');">{function="localize('Languages', 'langtool')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Manage domains', 'langtool')"}</a></li>
      </ul>
    </nav>

    <div class="content">
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
             {$j=0}
             {loop="$domains"}
              {$j=$j+1}
              <li class="list-item-single-line" id="domain_{$j}">
                <button class="btn-small" style="float: right;" onclick="$('#option_{$j}').slideToggle();">{function="localize('Options')"}</button>
                <a href="#" onclick="navigateTo('?display=langtool&cat=admin&action=view_domain&locale={$locale}&domain={$value}');" data-ignore="true">
                    <img src="{$PANTHERA_URL}/images/admin/flags/{$locale}.png" width="auto" height="15px" style="vertical-align: middle;">
                    <span style="vertical-align: middle;" id="value_{$j}">{$value}</span>
                </a>
              </li>

              <div id="option_{$j}" style="display: none;">
                  <br>
                  <button class="btn-small" onclick="renameDomain('{$value}', '{$locale}', '{$j}');" style="float: right;">{function="localize('Rename')"}</button>
                  <input type="text" id="domain_new_name_{$j}" value="{$value}" placeholder="{function="localize('New name')"}" class="input-text inline" autocomplete="off" style="max-width: calc(100% - 130px);">
                  <br>
                  <button class="btn-block" onclick="removeDomain('{$value}', '{$locale}', '{$j}');">{function="localize('Remove')"}</button>
                  <br><br>
              </div>
             {/loop}

             <br><br>

             <label>{function="localize('Add domain', 'langtool')"}</label>
             <button class="btn-small" onclick="createDomain('{$locale}');" style="float: right;">{function="localize('Add')"}</button>
             <input type="text" class="input-text inline" id="domain_name" style="max-width: calc(100% - 112spx);">

             <br><br>

            </ul>
        </ul>
     </div>

    <!-- JS code -->
     <script type="text/javascript">

     /**
      * Remove domain
      *
      * @author Mateusz Warzyński
      */

     function removeDomain(name, locale, n)
     {
        panthera.jsonPOST({ url: '?display=langtool&cat=admin&action=domains&subaction=remove_domain&domain_name='+name+'&locale='+locale, data: '', success: function (response) {
                if (response.status == "success")
                {
                    $('#option_'+n).hide();
                    $('#domain_'+n).hide();
                }
            }
        });

        return false;
     }

     /**
      * Rename domain
      *
      * @author Mateusz Warzyński
      */

     function renameDomain(name, locale, n)
     {
        var newname = $("#domain_new_name_"+n).val();
        panthera.jsonPOST({ url: '?display=langtool&cat=admin&action=domains&subaction=rename_domain&domain_name='+name+'&locale='+locale+'&new_domain_name='+newname, data: '', success: function (response) {
                if (response.status == "success")
                    $('#value_'+n).html(newname);
            }
        });

        return false;
     }

     /**
      * Create domain
      *
      * @author Mateusz Warzyński
      */

     function createDomain(locale)
     {
        var name = $("#domain_name").val();
        panthera.jsonPOST({ url: '?display=langtool&cat=admin&action=domains&subaction=add_domain&domain_name='+name+'&locale='+locale, data: '', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=langtool&cat=admin&action=domains&locale='+locale);
            }
        });

        return false;
     }

     </script>
    <!-- End of JS code -->
