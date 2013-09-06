{$site_header}

<script type="text/javascript">
$('#sliderCode').tinycarousel({ controls: true, pager: true, animation: true });

function editGallery(id, titleOfCategory)
{
    checkbox = { header: "{function="localize('Visibility', 'gallery')"}", type: "checkbox", name: "visibility", value: "visibility"}

    if ($('#visibility_'+id).html() == "{function="localize('Visible', 'gallery')"}")
        checkbox.checked = "checked"

    $.msgBox({ type: "prompt", opacity: 0.6,
      title: "{function="localize('Editing category')"}",
      inputs: [
      { header: "{function="localize('Editing category', 'gallery')"}", type: "text", name: "new_title", value: titleOfCategory}, checkbox],
      buttons: [
      { value: "{function="localize('Save', 'messages')"}" }, { value:"{function="localize('Cancel', 'messages')"}" }],
      success: function (result, values) { if (result == '{function="localize('Cancel', 'messages')"}') { return false; }

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
             url: '{$AJAX_URL}?display=gallery&cat=admin&action=edit_category&ctgid='+id+'&'+v,
             data: '',
             async: false,
             success: function (response) {

                  if (response.status == "success") {

                      jQuery('#gallery_title_'+id).html(response.title);

                      if (response.visibility == 'show') {
                              $('#hide_btn_'+id).val('{function="localize('Hide', 'gallery')"}');
                              $('#galleryCategory_row_'+id).css("background", "");
                              $('#visibility_'+id).html('{function="localize('Visible', 'gallery')"}');
                      }

                      if (response.visibility == 'hide') {
                              $('#galleryCategory_row_'+id).css("background", "rgba(172, 172, 172, 0.5)");
                              $('#visibility_'+id).html('{function="localize('Hidden', 'gallery')"}');
                              $('#hide_btn_'+id).val('{function="localize('Show', 'gallery')"}');
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
      title: "{function="localize('Adding category', 'gallery')"}",
      inputs: [
      { header: "{function="localize('Title', 'gallery')"}", type: "text", name: "new_title"},
      { header: "{function="localize('Publish', 'gallery')"}", type: "checkbox", name: "visibility", value: "visibility"}],
      buttons: [
      { value: "{function="localize('Add category', 'gallery')"}" }, { value:"{function="localize('Cancel', 'messages')"}" }],
      success: function (result, values) { if (result == '{function="localize('Cancel', 'messages')"}') { return false; }

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
             url: '{$AJAX_URL}?display=gallery&cat=admin&action=add_category&filter={$category_filter}&'+v,
             data: '',
             async: false,
             success: function (response) {

                  if (response.status == "success") {
                        navigateTo('?display=gallery&cat=admin&filter={$category_filter}');
                  }
             },
            dataType: 'json'
        });
      }
    });
}

</script>
		
		{include="ui.titlebar"}

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
        <div class="msgSuccess" style="display: none;" id="change_category_success">{function="localize('Category has been successfully changed', 'gallery')"}</div>
        <div class="msgError" style="display: none;" id="change_category_error"></div>

        <div id="all_categories_window">
            <table class="gridTable">
            <thead>
                <tr>
                  <th>{function="localize('Title', 'gallery')"}</th>
                  <th>{function="localize('Created', 'gallery')"}</th>
                  <th>{function="localize('Language', 'gallery')"}</th>
                  <th>&nbsp;</th>
                </tr>
            </thead>

            <tfoot>
            	<tr>
                	<td colspan="8"><em>{function="localize('Gallery categories', 'gallery')"}<input type="button" value="{function="localize('Add category', 'gallery')"}" style="float: right;" onclick="addGallery();" >
                    <input type="button" value="{function="localize('View permissions', 'gallery')"}" id="permissionsButton" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_view_galleryItem', 1024, 'upload_popup');" style="float: right;">
                    <input type="button" value="{function="localize('Edit permissions', 'gallery')"}" id="permissionsButton" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_edit_galleryItem', 1024, 'upload_popup');" style="float: right;"></em></td>
                </tr>
            </tfoot>
            <tbody>
                {loop="$category_list"}
                <tr id="galleryCategory_row_{$value->id}" {if="$value>visibility == 0"} style="background: rgba(172, 172, 172, 0.5);"{/if}>
                  <td><a href="?display=gallery&cat=admin&action=display_category&unique={$value->unique}{if="$category_filter_complete"}&filter={$category_filter_complete}{/if}" class='ajax_link' id='gallery_title_{$value->id}'>
                        <img src="{$value->thumb_url|pantheraUrl}" width="50" height="50"> {$value->title}
                      </a></td>
                  <td>{$value->created} {function="localize('by')"} {$value->author_login}</td>
                  <td>{$value->language}</td>
                  <td>
                      {if="$value->visibility == 1"}
                      <input type="button" value="{function="localize('Hide', 'messages')"}" onclick="toggleGalleryVisibility({$value->id});" id="hide_btn_{$value->id}">
                      {elseif="$value->visibility == 0"}
                      <input type="button" value="{function="localize('Show', 'messages')"}" onclick="toggleGalleryVisibility({$value->id});" id="hide_btn_{$value->id}">
                      {/if}
                      <input type="button" value="{function="localize('Edit', 'messages')"}" onclick="editGallery({$value->id}, '{$value->title|addslashes}');">
                      <input type="button" value="{function="localize('Delete', 'messages')"}" onclick="removeGalleryCategory({$value->id}); return false;">
                      <input type="button" value="{function="localize('Manage permissions', 'messages')"}" id="permissionsButton" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=gallery_manage_cat_{$value->id}', 1024, 'upload_popup');"></td>
                </tr>
                {/loop}
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
            url: '{$AJAX_URL}?display=gallery&cat=admin&action=toggle_gallery_visibility&ctgid='+id,
            data: '',
            async: false,
            success: function (response) {

                if (response.status == "success")
                {
                    if (response.visible == true)
                    {
                        $('#hide_btn_'+id).val('{function="localize('Hide', 'gallery')"}');
                        $('#galleryCategory_row_'+id).css("background", "");
                        $('#visibility_'+id).html('{function="localize('Visible', 'gallery')"}');
                    } else {
                        $('#galleryCategory_row_'+id).css("background", "rgba(172, 172, 172, 0.5)");
                        $('#visibility_'+id).html('{function="localize('Hidden', 'gallery')"}');
                        $('#hide_btn_'+id).val('{function="localize('Show', 'gallery')"}');
                    }
                }

            },
            dataType: 'json'
           });
}

function removeGalleryCategory(id)
{
    $.msgBox({
        title: "{function="localize('Are you sure?')"}",
        content: "{function="localize('Do you really want to delete this category?')"}",
        type: "confirm",
        autoClose: true,
        opacity: 0.6,
        buttons: [{ value: "{function="localize('Yes', 'messages')"}" }, { value: "{function="localize('No', 'messages')"}" }, { value: "{function="localize('Cancel', 'messages')"}"}],
        success: function (result) { if (result == '{function="localize('Yes', 'messages')"}') {
                $.ajax({
                    url: '{$AJAX_URL}?display=gallery&cat=admin&action=delete_category&id='+id,
                    data: '',
                    async: false,
                    success: function (response) {

                        if (response.status == "success")
                        {
                            navigateTo('?display=gallery&cat=admin&filter={$category_filter}');
                        }

                    },
                    dataType: 'json'
                   });
            }
        }
    });


}
</script>
