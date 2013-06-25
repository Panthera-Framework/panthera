<script>$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});</script>

{if $action eq 'list'}
<table class="gridTable">
<tbody>
{foreach from=$functions key=k item=v}
    {if $v.type eq 'method'}
        <tr><td>{"method"|localize}</td><td>&nbsp; &rarr; {$v.name}( {$v.params} )</td><td><a href="#" onclick="navigateTo('?display=browsefile&path={$v.filename}&start={$v.startline}&end={$v.endline}&back_btn={"?display=debhook"|base64_encode}'); return false;">{$v.declaration}</a></td></tr>
    {elseif $v.type eq 'class'}
        <tr class="roundedTdHiglighted"><td><b>{"class"|localize}</b></td><td colspan="2">class <b>{$v.name}</b><!-- (<a href="?display=debhook&view={$v.name}">{"Details"|localize}</a>)--></td></tr>
    {elseif $v.type eq 'function'}
        <tr><td>{"function"|localize}</td><td><b>function</b> {$v.name}( {$v.params} )</td><td><a href="#" onclick="navigateTo('?display=browsefile&path={$v.filename}&start={$v.startline}&end={$v.endline}&back_btn={"?display=debhook"|base64_encode}'); return false;">{$v.declaration}</a></td></tr>
    {/if}
{/foreach}
</tbody>
</table>
{else}

<script type="text/javascript">
var searchInitialized = false;

function loadFunctionsList(url)
{
    searchInitialized = false;

    $.ajax({
      type: "GET",
      url: url,
      data: '',
      success: function (response) {
          jQuery('#functions_window').html(response);
          jQuery('#functions_window').slideDown();
      },
      dataType: 'html'
    });
}
/*
jQuery('#function_search_box').change(function () {
    if (searchInitialized == false)
        window.setTimeout('loadFunctionsList("?display=debhook&action=list&search='+jQuery('#function_search_box').val()+'");', 1500);
});*/

jQuery('#functions_window_trigger').click(function () {
    if (jQuery('#functions_window').html().length < 10)
        loadFunctionsList('?display=debhook&action=list');

    return false;
});
</script>

    <div class="titlebar">{"Plugins debugger"|localize:debhook} - {"Internal Panthera Plugins debugger, you can see all hooked functions list here"|localize:debhook}</div>

    <br>
    <div class="msgSuccess" id="userinfoBox_success"></div>
    <div class="msgError" id="userinfoBox_failed"></div>

    <div class="grid-1">
      <table class="gridTable">

        <thead>
            <tr><th colspan="3"><b>{"Hooked functions"|localize:debhook}</b></th></tr>
         </thead>

         <tfoot>
            <tr>
                <td colspan="3" class="rounded-foot-left"><em>Panthera - debhook <input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_see_debhook', 1024, 'upload_popup');" style="float: right;">&nbsp;<input type="button" value="{"Back"|localize}" onclick="navigateTo('{navigation::getBackButton()}');" style="float: right; margin-right: 7px;"> </em></td>
            </tr>
        </tfoot>

        <tbody>
            {foreach from=$hooks key=k item=v}
            <tr><td>{$v.hook}</td><td>{$v.function}( {$v.params} )</td><td><a href="#" onclick="navigateTo('{$AJAX_URL}?display=browsefile&path={$v.filename}&start={$v.startline}&end={$v.endline}&back_btn={"?display=debhook"|base64_encode}'); return false;">{$v.declaration}</a></td></tr>
            {/foreach}

            <!--<tr>
              <th colspan="3"><a href="#" id="functions_window_trigger" class="tableTitleA">
                 <b>{"Declared functions and classes"|localize}</b></a> <!--<input type="text" value="" id="function_search_box" style="float: right;"> ->
              </th>
            </tr>-->
        </tbody>
       </table>

       <span id="functions_window" style="display: none;"></span>
     </div>
{/if}

