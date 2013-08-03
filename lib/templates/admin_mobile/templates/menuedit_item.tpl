    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=menuedit&cat=admin&action=category&category={$cat_type}');">{function="localize('Category', 'menuedit')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Editing item', 'menuedit')"}</a></li>
      </ul>
    </nav>

    <div class="content">

     <!-- Content -->
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
           <form id="save_form" method="POST" action="?display=menuedit&cat=admin&action=save_item">
            <ul class="list inset">
                <input type="text" name="item_title" placeholder="{function="localize('Title', 'menuedit')"}" value="{$item_title}" class="input-text" autocomplete="off">
                <input type="text" name="item_link" placeholder="{function="localize('Link', 'messages')"}" value="{$item_link}" class="input-text" autocomplete="off">


                <label>{function="localize('Language', 'menuedit')"}</label>
                <select name="item_language">
                      {loop="$item_language"}
                          <option value="{$key}"{if="$value == True"} selected{/if}>{$key}</option>
                      {/loop}
                </select>

                <br><br>

                <label>{function="localize('Optional', 'menuedit')"}</label>
                <input type="text" name="item_url_id" placeholder="{function="localize('SEO friendly name', 'menuedit')"}" value="{$item_url_id}" class="input-text" autocomplete="off">
                <input type="text" name="item_tooltip" placeholder="{function="localize('Tooltip', 'menuedit')"}" value="{$item_tooltip}" class="input-text" autocomplete="off">
                <input type="text" name="item_icon" placeholder="{function="localize('Icon', 'menuedit')"}" value="{$item_icon}" class="input-text" autocomplete="off">
                <input type="text" name="item_attributes" placeholder="{function="localize('Attributes', 'menuedit')"}" value="{$item_attributes}" class="input-text" autocomplete="off">

                <input type="hidden" name="item_id" value="{$item_id}">
                <input type="hidden" id="cat_type" name="cat_type" value="{$cat_type}">

               <br><br>

               <button class="btn-block" type="submit" id="save_button">{function="localize('Save')"}</button>

            </ul>
           </form>
          </li>
        </ul>
     </div>

   <!-- JS code -->
    <script type="text/javascript">

    /**
      * Save changes to database (item)
      *
      * @author Mateusz Warzyński
      */

    $('#save_form').submit(function () {
        panthera.jsonPOST({ data: '#save_form', success: function (response) {
                if (response.status == "success") {
                    $('#save_button').slideToggle();
                    setTimeout("jQuery('#save_button').slideToggle();", 2500);
                }
            }
        });

        return false;

    });
    </script>
   <!-- End of JS code -->