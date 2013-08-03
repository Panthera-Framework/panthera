    <nav class="tab-fixed">
      <ul class="tab-inner">
        <li><a href="#" onclick="navigateTo('?display=menuedit&cat=admin');">{function="localize('Menu editor', 'menuedit')"}</a></li>
        <li class="active"><a data-ignore="true">{function="localize('Adding category', 'menuedit')"}</a></li>
      </ul>
    </nav>

    <div class="content">
     <div class="slider tab-slider">
        <ul>
          <li id="dash" class="tab-item active">
           <form id="add_category_form" method="POST" action="?display=menuedit&cat=admin&action=add_category">
            <ul class="list inset">
                <input type="text" name="category_type_name" placeholder="{function="localize('Type name', 'menuedit')"}" class="input-text" autocomplete="off">
                <input type="text" name="category_title" placeholder="{function="localize('Title', 'menuedit')"}" class="input-text" autocomplete="off">
                <input type="text" name="category_description" placeholder="{function="localize('Description', 'menuedit')"}" class="input-text" autocomplete="off">

               <br><br>

               <button class="btn-block" type="submit">{function="localize('Add')"}</button>

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

    $('#add_category_form').submit(function () {
        panthera.jsonPOST({ data: '#add_category_form', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=menuedit&cat=admin');
            }
        });

        return false;

    });
    </script>
   <!-- End of JS code -->