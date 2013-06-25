{if $action eq 'display_category'}
<script type="text/javascript" src="{$PANTHERA_URL}/js/slidesjs/jquery.slides.min.js"></script>
<style type="text/css">

#box {
    text-align: left;
    width: 1200px;
    margin: 30px auto 0 auto;
    margin-top: 0px;
    background: #edfbff;
    overflow: hidden;
    -webkit-box-shadow: #191919 0px 2px 10px;
    -moz-box-shadow: #191919 0px 3px 10px;
    box-shadow: #191919 0px 3px 10px;
}

#image_slider_box {
    background: rgba(199, 229, 255, 0.5);
}

.buttons_right {
    float: right;
}

</style>

<script type="text/javascript">
function sliderChangeImage(src)
{
    jQuery('#image_slider_box').slideDown();

    jQuery('#image_slider').fadeOut('slow', function () {
        jQuery('#image_slider').attr('src', src);
        jQuery('#image_slider').fadeIn();
    });
}

jQuery('#image_slider').click(function () {
    jQuery('#image_slider_box').slideUp();
});

function toggleItemVisibility(id)
{
    $.ajax({
            url: '{$AJAX_URL}?display=gallery&action=toggle_item_visibility&itid='+id,
            data: '',
            async: false,
            success: function (response) {

                if (response.status == "success")
                {
                    if (response.visible == true)
                    {
                        $('#item_row_'+id).css("background", "");
                        $('#visibility_'+id).html('{"Visible"|localize:gallery}');
                        $('#hide_btn_'+id).val('{"Hide"|localize:gallery}');
                    } else {
                        $('#item_row_'+id).css("background", "rgba(172, 172, 172, 0.5)");
                        $('#visibility_'+id).html('{"Hidden"|localize:gallery}');
                        $('#hide_btn_'+id).val('{"Show"|localize:gallery}');
                    }
                }

            },
            dataType: 'json'
           });
}

function setAsCategoryThumb(id, ctgid)
{
    $.ajax({
            url: '{$AJAX_URL}?display=gallery&action=set_category_thumb&itid='+id+'&ctgid='+ctgid,
            data: '',
            async: false,
            success: function (response) {

                if (response.status == "success")
                {
                    jQuery('#button_'+id).attr("disabled", "disabled");
                    jQuery('#button_'+id).animate({ height:'toggle'});
                    setTimeout("jQuery('#button_"+id+"').removeAttr('disabled');", 2500);
                    setTimeout("jQuery('#button_"+id+"').animate({ height:'toggle' });", 2500);
                }

            },
            dataType: 'json'
           });
}

</script>

<div class="paHeader">
      <div class="paTitle">{"Gallery"|localize:gallery}</div>
</div>

<div class="paLine"></div>

<div id="box" style="margin-bottom: -3px;">
    <div style="text-align: center; display: none;" id="image_slider_box">
        <img id="image_slider" style="max-width: 800px; max-height: 600px; min-height: 12em;   display: table-cell;   vertical-align: middle; display: block;   margin-left: auto;   margin-right: auto; ">
    </div>

<table id="rounded-corner" summary="" style="width: 100%; padding: 0px; margin: 0px;">
    <thead>
        <tr>
            <th scope="col" class="rounded-company" style="width: 55px;"></th>
            <th>{"Title"|localize:gallery}</th>
            <th>{"Description"|localize:gallery}</th>
            <th>{"Created"|localize:gallery}</th>
            <th>{"Visibility"|localize:gallery}</th>
            <th>{"Options"|localize:gallery}</th>
            <th></th>
        </tr>
    </thead>

    <tfoot>
        <tr>
            <td colspan="7" class="rounded-foot-left"><em><div class="buttons_right"><input type="button" value="Wróć" onclick="navigateTo('?display=gallery'); return false;"><input type="button" value="Usuń kategorię" onclick="removeGalleryCategory({$category_id}); return false;"> <input type="button" value="{"Add new"|localize:gallery}" onclick="navigateTo('?display=gallery&action=add_item&ctgid={$category_id}');"></div>{"Image gallery"|localize:gallery}: {$category_title}</em></td>
        </tr>
    </tfoot>
    <tbody>
    {foreach from=$item_list key=k item=i}
        <tr id="item_row_{$i->id}" {if $i->visibility eq 0}style="background: rgba(172, 172, 172, 0.5);"{/if}>
            <td><a href="#" onclick="sliderChangeImage('{$i->link|pantheraUrl}'); return false;"><img src="{$i->getThumbnail('200', True)}" width="50" height="50"></a></td>
            <td>{$i->title}</td>
            <td>{$i->description}</td>
            <td>{$i->created}</td>

            <td>
              {if $i->visibility eq 1}
                  <span id="visibility_{$i->id}">{"Visible"|localize:gallery}</span>
              {else}
                  <span id="visibility_{$i->id}">{"Hidden"|localize:gallery}</span>
              {/if}
            </td>

            <td>
              {if $i->visibility eq 1}
                 <input type="button" value="{"Hide"|localize:gallery}" onclick="toggleItemVisibility({$i->id});" id="hide_btn_{$i->id}">
              {else}
                 <input type="button" value="{"Show"|localize:gallery}" onclick="toggleItemVisibility({$i->id});" id="hide_btn_{$i->id}">
              {/if}

                 <input type="button" value="{"Edit"|localize:gallery}" onclick="navigateTo('?display=gallery&action=edit_item_form&itid={$i->id}');">
                 <input type="button" value="{"Delete"|localize:gallery}" onclick="removeGalleryItem({$i->id}); return false;">
                 <input type="button" id="button_{$i->id}" value="{"Thumbnail"|localize:gallery}" onclick="setAsCategoryThumb({$i->id}, {$category_id}); return false;">
                 <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=gallery_manage_img_{$i->id}', 1024, 'upload_popup');">
            </td>

            <td>
        </tr>
    {/foreach}
    </tbody>
</table>

</div>