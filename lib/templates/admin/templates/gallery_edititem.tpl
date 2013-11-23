{$site_header}

<script type="text/javascript">

var progress = new panthera.ajaxLoader($('#change_item_form'));

$(document).ready(function () {
    $("#change_item_form").submit(function () {
        panthera.jsonPOST({ data: '#change_item_form', async: true, url: '{$AJAX_URL}?display=gallery&cat=admin&action=edit_item_form&subaction=edit_item&id={$id}', messageBox: 'w2ui', spinner: progress,
            success: function (response) {
                if (response.status == "success")
                    navigateTo('{$AJAX_URL}?display=gallery&cat=admin&action=displayCategory&unique={$unique}&language={$language_item}');
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

function upload_file_callback(link, mime, type, directory, id, description, author, name)
{
    if(type != 'image')
    {
        alert('{function="localize('Selected file is not a image')"}');
        return false;
    }

    $('#upload_file').val(link);
    $('#upload_id').val(id);
    $('#description').val(description);
    $('#title').val(name);
    sliderChangeImage(link);
}

sliderChangeImage('{$link}');
</script>
<style type="text/css">

#image_slider_box {
    margin-top: 0px;
}

.buttons_right {
    float: right;
}
</style>

{include="ui.titlebar"}

<form action="?display=gallery&cat=admin&action=edit_item_form&subaction=edit_item&id={$id}" method="POST" id="change_item_form">

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=gallery&cat=admin&action=displayCategory&unique={$unique}&language={$language_item}'); return false;" style="float: left; margin-left: 10px;">
        <input type="submit" value="{function="localize('Save')"}">
    </div>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    
    <div style="text-align: center; margin-top: -30px; margin-left: -100px; margin-right: -100px; background: #56687b; padding-bottom: 30px;" id="image_slider_box">
        <img id="image_slider" style="max-width: 99.9%; max-height: 200px; min-height: 12em;   display: table-cell;   vertical-align: middle; display: block;   margin-left: auto;   margin-right: auto; ">
    </div>

    <table style="display: inline-block; margin-top: 15px;">
        <thead>
            <tr>
                <th style="width: 250px;" colspan="2">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{function="localize('Title', 'gallery')"}</td>
                <td><input type="text" name="title" id="title" style="width: 500px;" value="{$title}"></td>
            </tr>
            <tr>
                <td>{function="localize('Description', 'gallery')"}</td>
                <td><input type="text" name="description" id="description" style="width: 500px;" value="{$description}"></td>
            </tr>
            <tr>
                <td>{function="localize('File', 'gallery')"}</td>
                <td><input type="text" name="link" value="{$link}" style="width: 500px;" id="upload_file" disabled> <input type="button" value="{function="localize('Upload file', 'gallery')"}" onclick="createPopup('_ajax.php?display=upload&cat=admin&directory=gallery&popup=true&callback=upload_file_callback', 1300, 550);"><input type="hidden" name="upload_id" id="upload_id" value="{$upload_id}"></td>
            </tr>
            <tr>
                <td>{function="localize('Visibility', 'gallery')"}</td>
                <td>
                    <input type="checkbox" name="visibility" {if="$visibility == '1'"}checked='checked' {/if}value="1">
                </td>
            </tr>
            <tr>
                <td>{function="localize('Category', 'gallery')"}</td>
                <td>
                    <select name="gallery_id">
                    {loop="$category_list"}
                    <option value="{$value->id}" {if="$value->id == $gallery_id"} selected='selected'{/if}>{$value->title} ({$value->language})</option>
                    {/loop}
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</form>
