{"comments"|localizeDomain}
<script type="text/javascript">

$('#edit_comment_form').submit(function () {
    loader = new panthera.ajaxLoader($('#loader_comment'));
    panthera.jsonPOST({ data: '#edit_comment_form', spinner: loader, messageBox: 'userinfoBox'});
    return false;
});

</script>

    <div class="titlebar">{"Edit comment"|localize:comments}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
      <div id="loader_comment" style="position: relative;">
        <form action="?display=comments&action=save" method="POST" id="edit_comment_form">
         <table class="gridTable">

          <thead>
              <tr>
                  <th scope="col" class="rounded-company">{"Key"|localize:messages}</th>
                  <th>{"Value"|localize:messages}</th>
              </tr>
          </thead>

          <tfoot>
              <tr>
                  <td colspan="2" class="rounded-foot-left"><em><input type="button" value="{"Back"|localize:messages}" onclick="navigateTo('?display=comments&action=show_comments&cmtid={$content_id}'); return false;"/> <input type="submit" value="{"Save"|localize:messages}"></em></td>
              </tr>
          </tfoot>

          <tbody>
              <tr>
                  <td>{"Title"|localize:comments}</td>
                  <td><input type="text" name="title" value="{$title}"></td>
              </tr>
              <tr>
                  <td>{"Content"|localize:comments}</td>
                  <td><textarea type="text" name="content">{$content}</textarea></td>
              </tr>
          </tbody>
         </table>
         <input type='text' name='content_id' value='{$content_id}' style="display: none;">
         <input type='text' name='id' value='{$id}' style="display: none;">
        </form>
      </div>
    </div>