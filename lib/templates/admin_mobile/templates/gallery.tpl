    {include="header.tpl"}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash" data-transition="push">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Gallery')"}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="gallery" class="tab-item active">
                <ul class="list inset">
                   <li class="list-divider">{function="localize('Gallery categories', 'gallery')"}</li>

                  {loop="$category_list"}
                   <li class="list-item-single-line">
                      <div class="inline">
                        <a href="?display=gallery&action=display_category&ctgid={$value->id}"><img src="{$value->thumb_url|pantheraUrl}" width="40px" height="40px" style="vertical-align: middle;"></a>
                        <input type="text" placeholder="{function="localize('Name', 'mailing')"}" value='{$value->title}' id="value_{$value->id}" class="input-text inline" style="border-bottom: 0px; max-width: calc(100% - 162px);" onfocus="jQuery('#save_{$value->id}').show();">
                        <button class="btn-small" style="float: right; display: none;" id="save_{$value->id}" onclick="saveCategory('{$value->id}');">{function="localize('Save', 'messages')"}</button>
                        <!-- <button class="btn-small" style="float:right;">{function="localize('Edit', 'gallery')"}</button> -->
                      </div>
                     </a>
                   </li>
                  {/loop}
                </ul>
            </li>
        </ul>

      </div>
    </div>

    <script>

    /**
      * Save name of category
      *
      * @author Mateusz Warzyński
      */

    function saveCategory(id)
    {
        var name = $('#value_'+id).val();

        panthera.jsonPOST({ url: '{$AJAX_URL}?display=gallery&action=edit_category&ctgid='+id+'&new_title='+name, data: '', success: function (response) {
                if (response.status == "success") {
                    jQuery('#save_'+id).animate({ height:'toggle'});
                }
            }
        });
    }
    </script>

    {include="footer.tpl"}
