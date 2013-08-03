    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=dash&cat=admin&menu=settings');">{function="localize('Dash')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Menu editor', 'menuedit')"}</a></li>
      </ul>
    </nav>

    <div class="content">
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
                <li class="list-divider">{function="localize('List of categories', 'menuedit')"}</li>

               {loop="$menu_categories"}
                <li class="list-item-two-lines selectable" id="category_{$value.id}">
                    <button class="btn-small" style="float: right;" onclick="removeMenuCategory({$value.id});">{function="localize('Delete')"}</button>
                    <a href="#" onclick="navigateTo('?display=menuedit&cat=admin&action=category&category={$value.name}');" data-ignore="true">
                        <h3>{$value.name}</h3>
                        <p>{$value.title} ({$value.description})</p>
                    </a>
                </li>
               {/loop}

               <br><br>

               <button class="btn-block" onclick="navigateTo('?display=menuedit&cat=admin&action=new_category');">{function="localize('Add new menu category', 'menuedit')"}</button>

            </ul>
          </li>
        </ul>
     </div>

   <!-- JS code -->
    <script type="text/javascript">

    /**
      * Remove menu category
      *
      * @author Mateusz Warzyński
      */

    function removeMenuCategory(id)
    {
        panthera.jsonPOST({ url: '?display=menuedit&cat=admin&action=remove_category&category_id='+id, data: '', messageBox: 'userinfoBox', success: function (response) {
                if (response.status == "success")
                    $('#category_'+id).slideToggle();
            }
        });
        return false;
    }

    </script>
   <!-- End of JS code -->