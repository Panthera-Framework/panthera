    {include 'header.tpl'}

    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=dash" data-transition="push">{"Dash"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"Gallery"|localize}</a></li>
      </ul>
    </nav>

    <div class="content inset">
      <div class="slider tab-slider">

        <ul>
            <li id="gallery" class="tab-item active">
                <ul class="list inset">
                   <li class="list-divider">{"Gallery categories"|localize:gallery}</li>

                  {foreach from=$category_list key=k item=i}
                   <li class="list-item-single-line">
                      <div class="inline">
                        <a href="?display=gallery&action=display_category&ctgid={$i->id}"><img src="{$i->thumb_url|pantheraUrl}" width="40px" height="40px" style="vertical-align: middle;"></a>
                        <input type="text" placeholder="{"Name"|localize:mailing}" value='{$i->title}' id="value_{$i->id}" class="input-text inline" style="border-bottom: 0px; max-width: calc(100% - 162px);" onfocus="jQuery('#save_{$i->id}').show();">
                        <button class="btn-small" style="float: right; display: none;" id="save_{$i->id}" onclick="saveCategory('{$i->id}');">{"Save"|localize:messages}</button>
                        <!-- <button class="btn-small" style="float:right;">{"Edit"|localize:gallery}</button> -->
                      </div>
                     </a>
                   </li>
                  {/foreach}
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

    {include 'footer.tpl'}
