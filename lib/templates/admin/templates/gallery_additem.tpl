<script type="text/javascript">
/**
  * Add new item to gallery
  *
  * @author Damian Kęska
  */

$(document).ready(function () {
    var progress = new panthera.ajaxLoader($('#add_item_form'));

    $('#add_item_form').submit(function () {
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=gallery&action=add_item&subaction=add', spinner: progress, async: true, messageBox: userinfoBox, success: function (response) {
                if (response.status == "success")
                    navigateTo('_ajax.php?display=gallery&action=display_category&ctgid='+response.ctgid);
            }
        });
    });
}

function sliderChangeImage(src)
{
    jQuery('#image_slider_box').slideDown();

    jQuery('#image_slider').fadeOut('slow', function () {
        jQuery('#image_slider').attr('src', src);
        jQuery('#image_slider').fadeIn();
    });
}

function upload_file_callback(link, mime, type, directory, id, description, author)
{
    if(type != 'image')
    {
        alert('{"Selected file is not a image"|localize}');
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
        margin-top: 50px;
    }

    .buttons_right {
        float: right;
    }
</style>

  <div class="titlebar">{"Adding gallery image"|localize:gallery}{include file="_navigation_panel.tpl"}</div>
  <br>
  <div class="msgSuccess" id="userinfoBox_success"></div>
  <div class="msgError" id="userinfoBox_failed"></div>

 <form action="?display=gallery&action=add_item&subaction=add" method="POST" id="add_item_form">
  <table class="gridTable">

    <tfoot>
        <tr>
            <td colspan="2" class="rounded-foot-left"><em><input type="button" value="{"Back"|localize:gallery}" onclick="navigateTo('?display=gallery&action=display_category&ctgid={$category_id}'); return false;"/> <input type="submit" value="{"Add"|localize:gallery}"></em></td>
        </tr>
    </tfoot>

    <tbody>
        <tr>
            <td>{"Title"|localize:gallery}</td>
            <td><input type="text" name="title" style="width: 500px;"></td>
        </tr>

        <tr>
            <td>{"Description"|localize:gallery}</td>
            <td><input type="text" name="description" id="description" style="width: 500px;"></td>
        </tr>

        <tr>
            <td>{"File"|localize:gallery}</td>
            <td><input type="text" name="link" style="width: 500px;" id="upload_file" disabled> <input type="button" value="{"Upload file"|localize}" onclick="createPopup('_ajax.php?display=upload&popup=true&callback=upload_file_callback', 1300, 550);"><input type="hidden" name="upload_id" id="upload_id"></td>
        </tr>

        <tr>
            <td>{"Visibility"|localize:gallery}</td>
            <td>
                  <input type="checkbox" name="visibility" value="1">
            </td>
        </tr>

        <tr>
            <td>{"Category"|localize:gallery}</td>
            <td>
                  <select name="gallery_id">
                   {foreach from=$category_list key=k item=i}
                        <option value="{$i->id}" {if $i->id eq $category_id} selected="selected"{/if}>{$i->title}</option>
                   {/foreach}
                  </select>
            </td>
        </tr>

    </tbody>
   </table>
  </form>

  <div style="text-align: center; display: none;" id="image_slider_box">
        <img id="image_slider" style="max-width: 800px; max-height: 600px; min-height: 12em;   display: table-cell;   vertical-align: middle; display: block;   margin-left: auto;   margin-right: auto; ">
  </div>
