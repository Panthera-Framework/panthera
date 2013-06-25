        <div class="titlebar">{"Gallery"|localize} - {"Site without gallery, is like man without d..."|localize}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
            <h1>Not implemented yet!</h1>
        </div>

<!--
<script type="text/javascript">
function editGallery(id, titleOfCategory)
{
    checkbox = { header: "{"Visibility"|localize}", type: "checkbox", name: "visibility", value: "visibility"}

    if ($('#visibility_'+id).html() == "{"Visible"|localize:gallery}")
        checkbox.checked = "checked"

    $.msgBox({ type: "prompt", opacity: 0.6,
      title: "{"Editing category"|localize}",
      inputs: [
      { header: "{"Editing category"|localize}", type: "text", name: "new_title", value: titleOfCategory}, checkbox],
      buttons: [
      { value: "{"Save"|localize:messages}" }, { value:"{"Cancel"|localize:messages}" }],
      success: function (result, values) {
        if (result == "{"Cancel"|localize:messages}")
            return false; // kurcze nie działa, jakoś trzeba inaczej zatrzymać to okienko przed zamykaniem, sam się tym zajmę
                        // Jak zatrzymać okienko przed zamykaniem? ;c /M.

        // var v = result + " has been clicked\n";

        $(values).each(function (index, input) {
            /*
            v += input.name + " : " + input.value +
            (input.checked != null ? ("  checked: " + input.checked) : "") + "\n"; // tu sprawdź wartośći i wywołaj ajax, potem podmień dane w tabelce według tego co zwróci serwer z jsonu
            */

            if (input.name == 'new_title') {
                  v = input.name + "=" + input.value;
                  new_title = input.value;
            }

            if (input.checked == 'checked' && input.name == 'visibility')
                  v += "&visibility=show";
            if (input.checked != 'checked' && input.name == 'visibility')
                  v += "&visibility=hide";
        });


        $.ajax({
             url: '{$AJAX_URL}?display=gallery&action=edit_category&ctgid='+id+'&'+v,
             data: '',
             async: false,
             success: function (response) {

                  if (response.status == "success") {

                      jQuery('#gallery_title_'+id).html(response.title);

                      if (response.visibility == 'show') {
                              $('#hide_btn_'+id).val('{"Hide"|localize:gallery}');
                              $('#galleryCategory_row_'+id).css("background", "");
                              $('#visibility_'+id).html('{"Visible"|localize:gallery}');
                      }

                      if (response.visibility == 'hide') {
                              $('#galleryCategory_row_'+id).css("background", "rgba(172, 172, 172, 0.5)");
                              $('#visibility_'+id).html('{"Hidden"|localize:gallery}');
                              $('#hide_btn_'+id).val('{"Show"|localize:gallery}');
                      }

                      jQuery('#change_category_error').hide();
                      jQuery('#change_category_success').slideDown();
                      setTimeout('jQuery(\'#change_category_success\').slideUp();', 5000);

                  } else {
                      jQuery('#change_category_success').hide();
                      jQuery('#change_category_error').slideDown();
                      jQuery('#change_category_error').html(response.error);
                      setTimeout('jQuery(\'#change_category_error\').slideUp();', 5000);
                  }
             },
            dataType: 'json'
        });
      }
    });
}

function addGallery()
{
    $.msgBox({ type: "prompt", opacity: 0.6,
      title: "{"Adding category"|localize}",
      inputs: [
      { header: "{"Title"|localize}", type: "text", name: "new_title"},
      { header: "{"Visibility"|localize}", type: "checkbox", name: "visibility", value: "visibility"}],
      buttons: [
      { value: "{"Save"|localize:messages}" }, { value:"{"Cancel"|localize:messages}" }],
      success: function (result, values) {
        if (result == "{"Cancel"|localize}")
            return false; // kurcze nie działa, jakoś trzeba inaczej zatrzymać to okienko przed zamykaniem, sam się tym zajmę
                        // Jak zatrzymać okienko przed zamykaniem? ;c /M.

        // var v = result + " has been clicked\n";

        $(values).each(function (index, input) {
            /*
            v += input.name + " : " + input.value +
            (input.checked != null ? ("  checked: " + input.checked) : "") + "\n"; // tu sprawdź wartośći i wywołaj ajax, potem podmień dane w tabelce według tego co zwróci serwer z jsonu
            */

            if (input.name == 'new_title') {
                  v = input.name + "=" + input.value;
                  new_title = input.value;
            }

            if (input.checked == 'checked' && input.name == 'visibility')
                  v += "&visibility=1";
            if (input.checked != 'checked' && input.name == 'visibility')
                  v += "&visibility=0";
        });


        $.ajax({
             url: '{$AJAX_URL}?display=gallery&action=add_category&'+v,
             data: '',
             async: false,
             success: function (response) {

                  if (response.status == "success") {
                        navigateTo('?display=gallery');
                  }
             },
            dataType: 'json'
        });
      }
    });
}

</script>

<div class="paHeader">
      <div class="paTitle">{"Gallery"|localize:gallery}</div>
</div>

<div class="paLine"></div>

<article>
    <div class="text-section">
        <ul class="states">
            <li class="succes" style="display: none;" id="change_category_success">{"Category has been successfully changed!"|localize:gallery}</li>
            <li class="error" style="display: none;" id="change_category_error"></li>
        </ul>

        <div id="all_categories_window">
            <table id="rounded-corner" summary="" style="width: 95%;">
            <thead>
                <tr>
            	<th scope="col" class="rounded-q1">{"Thumbnail"|localize:gallery}</th>
                  <th scope="col" class="rounded-company">{"Title"|localize:gallery}</th>
                  <th scope="col" class="rounded-q1">{"Author full name"|localize:gallery}</th>
                  <th scope="col" class="rounded-q1">{"Author login"|localize:gallery}</th>
                  <th scope="col" class="rounded-q1">{"Language"|localize:gallery}</th>
                  <th scope="col" class="rounded-q1">{"Created"|localize:gallery}</th>
                  <th scope="col" class="rounded-q1">{"Visibility"|localize:gallery}</th>
                  <th scope="col" class="rounded-q1">{"Options"|localize:gallery}</th>
                </tr>
            </thead>

            <tfoot>
            	<tr>
                	<td colspan="8" class="rounded-foot-left"><em>{"Gallery categories"|localize:gallery}<input type="button" value="{"Add category"|localize:gallery}" style="float: right;" onclick="addGallery();" >
                    <input type="button" value="{"View permissions"|localize:gallery}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_view_galleryItem', 1024, 'upload_popup');" style="float: right;">
                    <input type="button" value="{"Edit permissions"|localize:gallery}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_edit_galleryItem', 1024, 'upload_popup');" style="float: right;"></em></td>
                </tr>
            </tfoot>
            <tbody>
                {foreach from=$category_list key=k item=i}
                <tr id="galleryCategory_row_{$i->id}" {if $i->visibility eq 0} style="background: rgba(172, 172, 172, 0.5);"{/if}>
                  <td><img src="{$i->thumb_url|pantheraUrl}" width="50" height="50"></td>
                  <td><a href="?display=gallery&action=display_category&ctgid={$i->id}" class="ajax_link" id="gallery_title_{$i->id}">{$i->title}</a></td>
                  <td>{$i->author_full_name}</td>
                  <td>{$i->author_login}</td>
                  <td>{$i->language}</td>
                  <td>{$i->created}</td>

                  {if $i->visibility eq 1}
                  <td><span id="visibility_{$i->id}">{"Visible"|localize:gallery}</span></td>
                  <td><input type="button" value="{"Hide"|localize:gallery}" onclick="toggleGalleryVisibility({$i->id});" id="hide_btn_{$i->id}">

                  {elseif $i->visibility eq 0}
                  <td><span id="visibility_{$i->id}">{"Hidden"|localize:gallery}</span></td>
                  <td><input type="button" value="{"Show"|localize:gallery}" onclick="toggleGalleryVisibility({$i->id});" id="hide_btn_{$i->id}">
                  {/if}
                  <input type="button" value="{"Edit"|localize:gallery}" onclick="editGallery({$i->id}, '{$i->title|addslashes}');">
                  <input type="button" value="{"Delete"|localize:gallery}" onclick="removeGalleryCategory({$i->id}); return false;">
                  <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=gallery_manage_cat_{$i->id}', 1024, 'upload_popup');"></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        </div>

    </div>
</article> -->



<!--
<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

function toggleGalleryVisibility(id)
{
    $.ajax({
            url: '{$AJAX_URL}?display=gallery&action=toggle_gallery_visibility&ctgid='+id,
            data: '',
            async: false,
            success: function (response) {

                if (response.status == "success")
                {
                    if (response.visible == true)
                    {
                        $('#hide_btn_'+id).val('{"Hide"|localize:gallery}');
                        $('#galleryCategory_row_'+id).css("background", "");
                        $('#visibility_'+id).html('{"Visible"|localize:gallery}');
                    } else {
                        $('#galleryCategory_row_'+id).css("background", "rgba(172, 172, 172, 0.5)");
                        $('#visibility_'+id).html('{"Hidden"|localize:gallery}');
                        $('#hide_btn_'+id).val('{"Show"|localize:gallery}');
                    }
                }

            },
            dataType: 'json'
           });
}

function removeGalleryItem(id)
{
    $.msgBox({
        title: "{"Are you sure?"|localize}",
        content: "{"Do you really want to delete this item?"|localize}",
        type: "confirm",
        autoClose: true,
        opacity: 0.6,
        buttons: [{ value: "{"Yes"|localize:messages}" }, { value: "{"No"|localize:messages}" }, { value: "{"Cancel"|localize:messages}"}],
        success: function (result) {
            if (result == "{"Yes"|localize:messages}") {
                $.ajax({
                    url: '{$AJAX_URL}?display=gallery&action=delete_item&image_id='+id,
                    data: '',
                    async: false,
                    success: function (response) {

                        if (response.status == "success")
                        {
                            jQuery('#item_row_'+id).remove();
                        }

                    },
                    dataType: 'json'
                   });
            }
        }
    });


}

function removeGalleryCategory(id)
{
    $.msgBox({
        title: "{"Are you sure?"|localize}",
        content: "{"Do you really want to delete this category?"|localize}",
        type: "confirm",
        autoClose: true,
        opacity: 0.6,
        buttons: [{ value: "{"Yes"|localize:messages}" }, { value: "{"No"|localize:messages}" }, { value: "{"Cancel"|localize:messages}"}],
        success: function (result) {
            if (result == "{"Yes"|localize:messages}") {
                $.ajax({
                    url: '{$AJAX_URL}?display=gallery&action=delete_category&id='+id,
                    data: '',
                    async: false,
                    success: function (response) {

                        if (response.status == "success")
                        {
                            navigateTo('?display=gallery');
                        }

                    },
                    dataType: 'json'
                   });
            }
        }
    });


}
</script>
-->