    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=gallery&cat=admin&action=display_category&unique={$unique}');" data-transition="push">{loop="$category_list"}{if="$value->id == $gallery_id"} {$value->title} {/if} {/loop}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Editing gallery image', 'gallery')"}</a></li>
      </ul>
    </nav>

    <div class="content">
       <form action="?display=gallery&cat=admin&action=edit_item_form&subaction=edit_item&id={$id}" method="POST" id="change_item_form">
        <ul>
            <li id="gallery_edititem" class="tab-item active">
                <ul class="list">
                    <li class="list-divider">{function="localize('Information', 'gallery')"}</li>
                    <input type="text" name="title" placeholder="{function="localize('Title', 'gallery')"}" value="{$title}" class="input-text" autocomplete="off">
                    <input type="text" name="description" placeholder="{function="localize('Decription', 'gallery')"}" value="{$description}" class="input-text" autocomplete="off">

                    <li class="list-divider">{function="localize('Visibility', 'gallery')"}</li>
                    <li class="list-item-single-line selectable">
                        <a id="visibility_text" onclick="toggleItemVisibility();">
                            {if="$visibility == 1"} {function="localize('True')"} {else} {function="localize('False')"} {/if}
                        </a>
                            <input type="hidden" name="visibility" id="visibility_value" {if="$visibility == 1"} value='1' {else} value='0' {/if}>
                    </li>

                    <li class="list-divider">{function="localize('Category', 'gallery')"}</li>
                    <select name="gallery_id">
                   {loop="$category_list"}
                        <option value="{$value->id}" {if="$value->id == $gallery_id"} selected='selected'{/if}>{$value->title} ({$value->language})</option>
                   {/loop}
                    </select>

                    <br><br>

                    <input type="submit" class="btn-block" value="{function="localize('Save')"}" id="save_button">

                    <li class="list-divider">{function="localize('Picture', 'gallery')"}</li>
                    <li>
                        <div style="width: 100%; max-height: 100%;">
                            <center><img src="{$link}" style="max-width: 100%;"></center>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
        <input type="hidden" name="link" value="{$link}">
        <input type="hidden" name="upload_id" value="{$upload_id}">
        <input type="hidden" name="gallery_id" value="{$gallery_id}">
       </form>
    </div>

    <!-- JS code -->
    <script type="text/javascript">
    $(document).ready(function () {
        /**
          * Save gallery form
          *
          * @author Mateusz Warzyński
          */


        $("#change_item_form").submit(function () {
            panthera.jsonPOST({ data: '#change_item_form', async: true, url: '?display=gallery&cat=admin&action=edit_item_form&subaction=edit_item&id={$id}',
                success: function (response) {
                    if (response.status == "success")
                        $('#save_button').val('Saved!');
                        setTimeout("jQuery('#save_button').val('Save');", 2500);
                }
            });

            return false;
        });
    });

    /**
      * Toggle Visibility bool value
      *
      * @author Mateusz Warzyński
      */

    function toggleItemVisibility()
    {
        value = $('#visibility_value').val();

        if (value == 1)
        {
            $('#visibility_text').text("{function="localize('False')"}");
            $('#visibility_value').val('0');
        }

        if (value == 0)
        {
            $('#visibility_text').text("{function="localize('True')"}");
            $('#visibility_value').val('1');
        }
    }
    </script>
    <!-- End of JS code -->