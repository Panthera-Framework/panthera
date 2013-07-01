<script type="text/javascript">
$('#sliderCode').tinycarousel({ controls: true, pager: true, animation: true });

function editGallery(id, titleOfCategory)
{
    checkbox = { header: "{"Visibility"|localize:gallery}", type: "checkbox", name: "visibility", value: "visibility"}

    if ($('#visibility_'+id).html() == "{"Visible"|localize:gallery}")
        checkbox.checked = "checked"

    $.msgBox({ type: "prompt", opacity: 0.6,
      title: "{"Editing category"|localize}",
      inputs: [
      { header: "{"Editing category"|localize:gallery}", type: "text", name: "new_title", value: titleOfCategory}, checkbox],
      buttons: [
      { value: "{"Save"|localize:messages}" }, { value:"{"Cancel"|localize:messages}" }],
      success: function (result, values) {
        if (result == "{"Cancel"|localize:messages}")
            return false;

        $(values).each(function (index, input) {

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
      { header: "{"Title"|localize:messages}", type: "text", name: "new_title"},
      { header: "{"Visibility"|localize:messages}", type: "checkbox", name: "visibility", value: "visibility"}],
      buttons: [
      { value: "{"Save"|localize:messages}" }, { value:"{"Cancel"|localize:messages}" }],
      success: function (result, values) {
        if (result == "{"Cancel"|localize}")
            return false; 

        $(values).each(function (index, input) {

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
             url: '{$AJAX_URL}?display=gallery&action=add_category&filter={$category_filter}&'+v,
             data: '',
             async: false,
             success: function (response) {

                  if (response.status == "success") {
                        navigateTo('?display=gallery&filter={$category_filter}');
                  }
             },
            dataType: 'json'
        });
      }
    });
}

</script>

        <div class="titlebar">{"Gallery"|localize:messages}{include file="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
        <div class="msgSuccess" style="display: none;" id="change_category_success">{"Category has been successfully changed"|localize:gallery}</div>
        <div class="msgError" style="display: none;" id="change_category_error"></div>

        <div id="all_categories_window">
            <table class="gridTable">
            <thead>
                <tr>
            	<th>&nbsp;</th>
                  <th>{"Title"|localize:gallery}</th>
                  <th>{"Created by"|localize:gallery}</th>
                  <th>{"Language"|localize:gallery}</th>
                  <th>{"Created"|localize:gallery}</th>
                  <th>{"Visibility"|localize:gallery}</th>
                  <th>{"Options"|localize:messages}</th>
                </tr>
            </thead>

            <tfoot>
            	<tr>
                	<td colspan="8"><em>{"Gallery categories"|localize:gallery}<input type="button" value="{"Add category"|localize:gallery}" style="float: right;" onclick="addGallery();" >
                    <input type="button" value="{"View permissions"|localize:gallery}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_view_galleryItem', 1024, 'upload_popup');" style="float: right;">
                    <input type="button" value="{"Edit permissions"|localize:gallery}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_edit_galleryItem', 1024, 'upload_popup');" style="float: right;"></em></td>
                </tr>
            </tfoot>
            <tbody>
                {foreach from=$category_list key=k item=i}
                <tr id="galleryCategory_row_{$i->id}" {if $i->visibility eq 0} style="background: rgba(172, 172, 172, 0.5);"{/if}>
                  <td><img src="{$i->thumb_url|pantheraUrl}" width="50" height="50"></td>
                  <td><a href="?display=gallery&action=display_category&ctgid={$i->id}" class="ajax_link" id="gallery_title_{$i->id}">{$i->title}</a></td>
                  <td>{$i->author_login}</td>
                  <td>{$i->language}</td>
                  <td>{$i->created}</td>

                  <td>
                      {if $i->visibility eq 1}
                      <input type="button" value="{"Hide"|localize:messages}" onclick="toggleGalleryVisibility({$i->id});" id="hide_btn_{$i->id}">
                      {elseif $i->visibility eq 0}
                      <input type="button" value="{"Show"|localize:messages}" onclick="toggleGalleryVisibility({$i->id});" id="hide_btn_{$i->id}">
                      {/if}
                  </td>
                  <td>
                      <input type="button" value="{"Edit"|localize:messages}" onclick="editGallery({$i->id}, '{$i->title|addslashes}');">
                      <input type="button" value="{"Delete"|localize:messages}" onclick="removeGalleryCategory({$i->id}); return false;">
                      <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=gallery_manage_cat_{$i->id}', 1024, 'upload_popup');"></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        </div>

    </div>
</article>



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
                            navigateTo('?display=gallery&filter={$category_filter}');
                        }

                    },
                    dataType: 'json'
                   });
            }
        }
    });


}
</script>
