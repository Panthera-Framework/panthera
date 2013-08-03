    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin');" data-transition="push">{function="localize('Dash')"}</a></li>
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
                   <li class="list-item-single-line" style="height: 60px;" id="li_{$value->id}">
                      <div class="inline">
                        <a href="#" onclick="navigateTo('?display=gallery&cat=admin&action=display_category&unique={$value->unique}{if="$category_filter_complete"}&filter={$category_filter_complete}{/if}');"><img src="{$value->thumb_url|pantheraUrl}" width="40px" height="40px" style="vertical-align: middle;"></a>
                        <input type="text" placeholder="{function="localize('Name', 'mailing')"}" value='{$value->title}' id="value_{$value->id}" class="input-text inline" {if="$value->visibility == 0"} style="border-bottom: 0px; color: #858585;" {else}  style="border-bottom: 0px;" {/if} onfocus="jQuery('#options_{$value->id}').animate({ height:'toggle'});">
                        <!-- <button class="btn-small" style="float:right;">{function="localize('Edit', 'gallery')"}</button> -->
                      </div>
                     </a>
                   </li>

                   <div id="options_{$value->id}" style="display: none;">
                        <button class="btn-small" style="float: right;" onclick="removeCategory('{$value->id}');">{function="localize('Remove', 'messages')"}</button>
                       {if="$value->visibility == 0"}
                        <button class="btn-small" style="float: right; margin-right: 7px;" onclick="toggleGalleryVisibility({$value->id});" id="hide_btn_{$value->id}">{function="localize('Show', 'messages')"}</button>
                       {elseif="$value->visibility == 1"}
                        <button class="btn-small" style="float: right; margin-right: 7px;" onclick="toggleGalleryVisibility({$value->id});" id="hide_btn_{$value->id}">{function="localize('Hide', 'messages')"}</button>
                       {/if}
                        <button class="btn-small" style="float: right; margin-right: 7px;" onclick="saveCategory('{$value->id}');">{function="localize('Save', 'messages')"}</button>
                        <br><br>
                   </div>

                  {/loop}

                  <br>
                  <button class="btn-small" onclick="$('#add_category').slideToggle(); return false;" style="float: right;">{function="localize('Add category', 'gallery')"}</button>
                </ul>
              <div id="add_category" style="display: none;">
                <ul class="list inset">
                    <li class="list-divider">{function="localize('Add category', 'gallery')"}</li>

                    <li class="list-item-single-line">
                        <div>
                            <input type="text" class="input-text inline" id="title_of_new_category" placeholder="{function="localize('Title', 'gallery')"}" style="border-bottom: 0px; max-width: 100%; font-size: 16px;">
                        </div>
                    </li>

                    <li class="list-item-single-line">
                        <a onclick="toggleVisibility();" style="color: white;">
                            {function="localize('Publish', 'gallery')"}:&nbsp;<span id="visibility">{function="localize('True')"}</span>
                                <input type="text" style="display: none;" value="1" id="visibility_of_new_category">
                        </a>
                    </li>
                    <button class="btn-block" onclick="addCategory()">{function="localize('Add', 'messages')"}</button>
                </ul>
              </div>

            </li>
        </ul>
      </div>
    </div>

   <!-- JS code -->
    <script type="text/javascript">
        /**
          * Save name of category
          *
          * @author Mateusz Warzyński
          */

        function saveCategory(id)
        {
            var name = $('#value_'+id).val();

            panthera.jsonPOST({ url: '?display=gallery&cat=admin&action=edit_category&ctgid='+id+'&new_title='+name, data: '', success: function (response) {
                    if (response.status == "success") {
                        jQuery('#options_'+id).animate({ height:'toggle'});
                    }
                }
            });
        };

        /**
          * Add new category
          *
          * @author Mateusz Warzyński
          */

        function addCategory()
        {
            var title = $('#title_of_new_category').val();
            var visibility = $('#visibility_of_new_category').val();

            panthera.jsonPOST({ url: '?display=gallery&cat=admin&action=add_category&visibility='+visibility+'&new_title='+title, data: '', success: function (response) {
                    if (response.status == "success") {
                        navigateTo('?display=gallery&cat=admin');
                    }
                }
            });
        };

        /**
          * Toggle value of input (visibility)
          *
          * @author Mateusz Warzyński
          */

        function toggleVisibility()
        {
            visibility = $('#visibility_of_new_category').val();

            if (visibility == 1)
            {
                $('#visibility_of_new_category').val('0');
                $('#visibility').text('{function="localize('False')"}');
            }

            if (visibility == 0)
            {
                $('#visibility_of_new_category').val('1');
                $('#visibility').text('{function="localize('True')"}');
            }
        };

        /**
          * Remove category
          *
          * @author Mateusz Warzyński
          */

        function removeCategory(id)
        {
            panthera.jsonPOST({  url: '?display=gallery&cat=admin&action=delete_category&id='+id, data: '', success: function (response) {
                    if (response.status == "success") {
                        $('#li_'+id).animate({ height:'toggle'});
                        $('#options_'+id).animate({ height:'toggle'});
                    }
                }
            });
        };

        /**
          * Toggle visibility by button
          *
          * @author Mateusz Warzyński
          */

        function toggleGalleryVisibility(id)
        {
            panthera.jsonPOST({  url: '?display=gallery&cat=admin&action=toggle_gallery_visibility&ctgid='+id, data: '', success: function (response) {
                    if (response.visible == true)
                    {
                        $('#hide_btn_'+id).text('{function="localize('Hide', 'gallery')"}');
                        $('#value_'+id).css("color", "");
                        $('#options_'+id).animate({ height:'toggle'});
                    } else {
                        $('#value_'+id).css("color", "#858585");
                        $('#hide_btn_'+id).text('{function="localize('Show', 'gallery')"}');
                        $('#options_'+id).animate({ height:'toggle'});
                    }
                }
            });
        };
    </script>
   <!-- End of JS code -->