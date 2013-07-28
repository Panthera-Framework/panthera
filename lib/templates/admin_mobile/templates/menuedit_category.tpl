{include="header.tpl"}

   <!-- Content -->
    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="?display=menuedit">{function="localize('Menu editor', 'menuedit')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Category', 'menuedit')"}</a></li>
      </ul>
    </nav>
    
    <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
            <ul class="list inset">
                <li class="list-divider">{function="localize('List of items', 'menuedit')"}</li>
                
               {loop="$menus"}
                <li class="list-item-two-lines selectable" id="category_{$value.id}">
                    <button class="btn-small" style="float: right;" onclick="removeItem({$value.id});">{function="localize('Delete')"}</button>
                    <a href="?display=menuedit&action=item&id={$value.id}" data-ignore="true">
                        <h3>{$value.title}</h3>
                        <p>{$value.link_original} ({$value.language})</p>
                    </a>
                </li>
               {/loop}
               
               <br><br>
               
               <button class="btn-block" onclick="window.location = '?display=menuedit&action=new_item&cat={$category}'">{function="localize('Add new link', 'menuedit')"}</button>
               
            </ul>
          </li>
        </ul>
     </div>
   <!-- End of content -->

   <!-- JS code -->
    <script type="text/javascript">
     
     /**
      * Remove menu item from database
      *
      * @author Mateusz Warzyński
      */
    
    function removeItem(id)
    {
        panthera.jsonPOST({ url: '?display=menuedit&action=remove_item&item_id='+id, data: '', success: function (response) {
                if (response.status == "success")
                {
                    $('#item_'+id).slideToggle();
                    jQuery('#item_'+id).remove();
                }
            }
        });
    
        return false;
    }
    </script>
   <!-- End of JS code -->
   
{include="footer.tpl"}