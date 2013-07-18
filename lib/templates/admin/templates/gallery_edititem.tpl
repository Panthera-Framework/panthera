<script type="text/javascript">
var progress = new panthera.ajaxLoader($('#change_item_form'));

$(document).ready(function () {
    $("#change_item_form").submit(function () {
        panthera.jsonPOST({ data: '#change_item_form', async: true, url: '{$AJAX_URL}?display=gallery&action=edit_item_form&subaction=edit_item&id={$id}', messageBox: 'userinfoBox', spinner: progress,
            success: function (response) {
                if (response.status == "success")
                    navigateTo('{$AJAX_URL}?display=gallery&action=display_category&unique={$unique}&language={$language}');
            }
        });

        return false;
    });
});

function sliderChangeImage(src)
{
    $('#image_slider_box').slideDown();

    $('#image_slider').fadeOut('slow', function () {
        $('#image_slider').attr('src', src);
        $('#image_slider').fadeIn();
    });
}

function upload_file_callback(link, mime, type, directory, id, description, author)
{
    if(type != 'image')
    {
        alert('{function="localize('Selected file is not a image')"}');
        return false;
    }

    $('#upload_file').val(link);
    $('#upload_id').val(id);
    $('#description').val(description);
    sliderChangeImage(link);
}

$('#image_slider').click(function () {
    $('#image_slider_box').slideUp();
});

sliderChangeImage('{$link}');
</script>
<style type="text/css">

#box {
    text-align: left;
    width: 700px;
    margin: 30px auto 0 auto;
    margin-top: 0px;
    background: #edfbff;
    overflow: hidden;
    -webkit-box-shadow: #191919 0px 2px 10px;
    -moz-box-shadow: #191919 0px 3px 10px;
    box-shadow: #191919 0px 3px 10px;
}

#image_slider_box {
    margin-top: 20px;
}

.buttons_right {
    float: right;
}
</style>

<article>
  <div class="titlebar">{function="localize('Editing gallery image', 'gallery')"}{include="_navigation_panel.tpl"}</div>
  <br>
  <div class="msgSuccess" id="userinfoBox_success"></div>
  <div class="msgError" id="userinfoBox_failed"></div>

 <form action="?display=gallery&action=edit_item_form&subaction=edit_item&id={$id}" method="POST" id="change_item_form">
  <table class="gridTable">

    <thead>
        <tr>
            <th style="width: 250px;" colspan="2">&nbsp;</th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="2"><em><input type="button" value="{function="localize('Back', 'messages')"}" onclick="navigateTo('?display=gallery&action=display_category&unique={$unique}'); return false;"/> <input type="submit" value="{function="localize('Save', 'messages')"}"></em></td>
        </tr>
    </tfoot>

    <tbody>
        <tr>
            <td>{function="localize('Title', 'gallery')"}</td>
            <td><input type="text" name="title" style="width: 500px;" value="{$title}"></td>
        </tr>

        <tr>
            <td>{function="localize('Description', 'gallery')"}</td>
            <td><input type="text" name="description" id="description" style="width: 500px;" value="{$description}"></td>
        </tr>

        <tr>
            <td>{function="localize('File', 'gallery')"}</td>
            <td><input type="text" name="link" value="{$link}" style="width: 500px;" id="upload_file" disabled> <input type="button" value="{function="localize('Upload file', 'gallery')"}" onclick="createPopup('_ajax.php?display=upload&popup=true&callback=upload_file_callback', 1300, 550);"><input type="hidden" name="upload_id" id="upload_id" value="{$upload_id}"></td>
        </tr>

        <tr>
            <td>{function="localize('Visibility', 'gallery')"}</td>
            <td>
                  <input type="checkbox" name="visibility" {if="$visibility == '1'}checked='checked' {/if"}value="1">
            </td>
        </tr>

        <tr>
            <td>{function="localize('Category', 'gallery')"}</td>
            <td>
                  <select name="gallery_id">
                   {loop="$category_list"}
                        <option value="{$i->id}" {if="$i->id == $gallery_id} selected='selected'{/if}>{$i->title} ({$i->language"})</option>
                   {/loop}
                  </select>
            </td>
        </tr>

    </tbody>
   </table>
  </form>
  <div style="text-align: center; display: none;" id="image_slider_box">
        <img id="image_slider" style="max-width: 800px; max-height: 600px; min-height: 12em;   display: table-cell;   vertical-align: middle; display: block;   margin-left: auto;   margin-right: auto; ">
  </div>
 </div>
</article>
