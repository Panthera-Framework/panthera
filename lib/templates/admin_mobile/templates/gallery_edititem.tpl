    {include 'header.tpl'}
    
    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=gallery&action=display_category&ctgid={$gallery_id}" data-transition="push">{"Gallery category"|localize}</a></li>
        <li class="active"><a data-ignore="true">{"Editing gallery image"|localize:gallery}</a></li>
      </ul>
    </nav>

    <div class="content">
       <form action="?display=gallery&action=edit_item_form&subaction=edit_item&id={$id}" method="POST" id="change_item_form">
        <ul>
            <li id="mailing" class="tab-item active">
                <ul class="list">
                    <li class="list-divider">{"Information"|localize:gallery}</li>
                    <input type="text" name="title" placeholder="{"Title"|localize:gallery}" value="{$title}" class="input-text" autocomplete="off">
                    <input type="text" name="description" placeholder="{"Decription"|localize:gallery}" value="{$description}" class="input-text" autocomplete="off">
                    
                    <li class="list-divider">{"Visibility"|localize:gallery}</li>
                    <li class="list-item-single-line selectable">
                        <a id="visibility_text" onclick="toggleItemVisibility();">
                            {if $visibility eq "1"} {"True"|localize} {else} {"False"|localize} {/if}
                        </a>
                            <input type="hidden" name="visibility" id="visibility_value" {if $visibility eq "1"} value="1" {else} value="0" {/if}>
                    </li>
                    <input type="submit" class="btn-block" value="{"Save"|localize}" id="save_button">
                    
                    <li class="list-divider">{"Picture"|localize:gallery}</li>
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
    
    <script type="text/javascript">
    $(document).ready(function () {
        /**
          * Save mail form
          *
          * @author Mateusz Warzyński
          */
        
        
        $("#change_item_form").submit(function () {
            panthera.jsonPOST({ data: '#change_item_form', async: true, url: '{$AJAX_URL}?display=gallery&action=edit_item_form&subaction=edit_item&id={$id}',
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
            $('#visibility_text').text("{"False"|localize}");
            $('#visibility_value').val('0');
        }
                        
        if (value == 0)
        {
            $('#visibility_text').text("{"True"|localize}");
            $('#visibility_value').val('1');
        }
    }
    </script>
    
    {include 'footer.tpl'}