    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=menuedit&cat=admin');">{function="localize('Menu editor', 'menuedit')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Category', 'menuedit')"}</a></li>
      </ul>
    </nav>

    <div class="content">
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
                <li class="list-divider">{function="localize('List of items', 'menuedit')"}</li>

               {loop="$menus"}
                <li class="list-item-two-lines selectable" id="category_{$value.id}">
                    <button class="btn-small" style="float: right;" onclick="removeItem({$value.id});">{function="localize('Delete')"}</button>
                    <a onclick="navigateTo('?display=menuedit&cat=admin&action=item&id={$value.id}');" data-ignore="true">
                        <h3>{$value.title}</h3>
                        <p>{$value.link_original} ({$value.language})</p>
                    </a>
                </li>
               {/loop}

               <br><br>

               <button class="btn-block" onclick="navigateTo('?display=menuedit&cat=admin&action=new_item&category={$category}');">{function="localize('Add new link', 'menuedit')"}</button>

            </ul>
          </li>
        </ul>
     </div>

   <!-- JS code -->
    <script type="text/javascript">

     /**
      * Remove menu item from database
      *
      * @author Mateusz Warzyński
      */

    function removeItem(id)
    {
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=menuedit&cat=admin&action=remove_item&item_id='+id, data: '', success: function (response) {
                if (response.status == "success") {
                    $('#category_'+id).slideUp();
                    $('#category_'+id).remove();
                }
            }
        });

        return false;
    }
    </script>
   <!-- End of JS code -->