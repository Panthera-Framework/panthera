{elseif $action eq 'edit_item'}
<script type="text/javascript">
jQuery("#change_item_form").ajaxForm({
    url: '{$AJAX_URL}?display=gallery&action=edit_item_form&subaction=edit_item&id={$id}', type: 'post', dataType: 'json', success: function (response) {

        // alert(response);

        if (response.status == "success")
        {
                navigateTo('{$AJAX_URL}?display=gallery&action=display_category&ctgid='+response.ctgid);
                jQuery('#change_item_error').hide();
                jQuery('#change_item_success').slideDown();
                setTimeout('jQuery(\'#change_item_success\').slideUp();', 5000);
        } else {
                jQuery('#change_item_success').hide();
                jQuery('#change_item_error').slideDown();
                jQuery('#change_item_error').html(response.message);
        }
    }
});

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

jQuery('#image_slider').click(function () {
    jQuery('#image_slider_box').slideUp();
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
    background: rgba(210, 227, 255, 0.37);
}

.buttons_right {
    float: right;
}
</style>

<article>
<div class="paHeader">
      <div class="paTitle">{"Editing gallery image"|localize:gallery}</div>
</div>

<div class="paLine"></div>

 <div class="text-section">
  <ul class="states">
       <li class="succes" style="display: none;" id="change_item_success">{"Item has been successfully changed!"|localize:gallery}</li>
       <li class="error" style="display: none;" id="change_item_error"></li>
  </ul><br>

 <form action="?display=gallery&action=edit_item_form&subaction=edit_item&id={$id}" method="POST" id="change_item_form">
  <table id="rounded-corner" summary="" style="width: 100%; padding: 0px; margin: 0px;">

    <thead>
        <tr>
            <th scope="col" class="rounded-company" style="width: 250px;" colspan="2">&nbsp;</th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="2" class="rounded-foot-left"><em><input type="button" value="{"Back"|localize:gallery}" onclick="navigateTo('?display=gallery&action=display_category&ctgid={$gallery_id}'); return false;"/> <input type="submit" value="{"Save"|localize:messages}"></em></td>
        </tr>
    </tfoot>

    <tbody>
        <tr>
            <td>{"Title"|localize:gallery}</td>
            <td><input type="text" name="title" style="width: 500px;" value="{$title}"></td>
        </tr>

        <tr>
            <td>{"Description"|localize:gallery}</td>
            <td><input type="text" name="description" id="description" style="width: 500px;" value="{$description}"></td>
        </tr>

        <tr>
            <td>{"File"|localize:gallery}</td>
            <td><input type="text" name="link" value="{$link}" style="width: 500px;" id="upload_file" disabled> <input type="button" value="{"Upload file"|localize}" onclick="createPopup('_ajax.php?display=upload&popup=true&callback=upload_file_callback', 1024, 'upload_popup');"><input type="hidden" name="upload_id" id="upload_id" value="{$upload_id}"></td>
        </tr>

        <tr>
            <td>{"Visibility"|localize:gallery}</td>
            <td>
                  <input type="checkbox" name="visibility" {if $visibility eq '1'}checked="checked" {/if}value="1">
            </td>
        </tr>

        <tr>
            <td>{"Category"|localize:gallery}</td>
            <td>
                  <select name="gallery_id">
                   {foreach from=$category_list key=k item=i}
                        <option value="{$i->id}" {if $i->id eq $gallery_id} selected="selected"{/if}>{$i->title}</option>
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
 </div>
</article>