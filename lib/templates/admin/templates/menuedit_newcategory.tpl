<script type="text/javascript">

/**
  * Add menu category
  *
  * @author Mateusz Warzyński
  */

$('#add_category_form').submit(function () {
    panthera.jsonPOST({ data: '#add_category_form', messageBox: 'w2ui', success: function (response) {
            if (response.status == "success")
                navigateTo('?display=menuedit&cat=admin');
        }
    });

    return false;

});

</script>

    {include="ui.titlebar"}

    <div class="grid-1">
      <form id="add_category_form" method="POST" action="?display=menuedit&cat=admin&action=add_category">
        <table class="gridTable">
            <thead>
                  <tr>
                      <th scope="col" class="rounded-company" style="width: 250px;">&nbsp;</th>
                      <th>&nbsp;</th>
                  </tr>
            </thead>

            <tfoot>
                  <tr>
                      <td colspan="7" class="rounded-foot-left"><em>Panthera menuedit - {function="localize('Adding category', 'menuedit')"}</em><span>
                      <input type="submit" value="" style="background-image: url({$PANTHERA_URL}/images/admin/list-add.png); background-position:  0px 0px; background-repeat: no-repeat; width: 50px; height: 50px; float: right;">
                  </tr>
            </tfoot>

            <tbody>
                  <tr>
                      <td>{function="localize('Type name', 'menuedit')"}</td>
                      <td><input type="text" name="category_type_name" style="width: 99%;"></td>
                  </tr>
                  <tr>
                      <td>{function="localize('Title', 'menuedit')"}</td>
                      <td><input type="text" name="category_title" style="width: 99%;"></td>
                  </tr>
                  <tr>
                      <td>{function="localize('Description', 'menuedit')"}</td>
                      <td><input type="text" name="category_description" style="width: 99%;"></td>
                  </tr>
              </tbody>
        </table>
        <input type="hidden" name="category_parent" value="0">
        <input type="hidden" name="category_elements" value="0">
      </form>
    </div>
