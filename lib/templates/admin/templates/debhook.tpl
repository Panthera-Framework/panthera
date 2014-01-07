{$site_header}

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
</div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
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
        $('#function_search_box').change(function () {
            if (searchInitialized == false)
                window.setTimeout('loadFunctionsList("?display=debhook&cat=admin&action=list&search='+jQuery('#function_search_box').val()+'");', 1500);
        });*/
        
        $('#functions_window_trigger').click(function () {
            if (jQuery('#functions_window').html().length < 10)
                loadFunctionsList('?display=debhook&cat=admin&action=list');
        
            return false;
        });
      </script>
      
      <table style="width: 100%; margin-bottom: 25px;">
            <thead>
                <tr>
                    <th colspan="3">{function="localize('Hooked functions', 'debhook')"}</th>
                </tr>
            </thead>
            
            <tbody class="hovered">
                {$where="?display=debhook&cat=admin"}
                {loop="$hooks"}
                <tr><td>{$value.hook}</td><td>{$value.function}( {$value.params} )</td><td><a href="#" onclick="navigateTo('{$AJAX_URL}?display=browsefile&cat=admin&path={$value.filename}&start={$value.startline}&end={$value.endline}&back_btn={$where|base64_encode}'); return false;">{$value.declaration}</a></td></tr>
                {/loop}
    
                <!--<tr>
                  <th colspan="3"><a href="#" id="functions_window_trigger" class="tableTitleA">
                     <b>{function="localize('Declared functions and classes')"}</b></a> <!--<input type="text" value="" id="function_search_box" style="float: right;"> ->
                  </th>
                </tr>-->
            </tbody>
      </table>
      
      <table style="width: 100%;">
         <thead>
            <tr>
                <th colspan="3">{function="localize('List of defined functions and classes', 'debhook')"}</th>
            </tr>
         </thead>
      
        <tbody class="hovered">
         {if="$functions"}
         {loop="$functions"}
            {if="$value.type == 'method'"}
                <tr>
                    <td>{function="localize('method')"}</td>
                    <td>&nbsp; &rarr; {$value.name}( {$value.params} )</td>
                    <td>
                        <a href="#" onclick="navigateTo('?display=browsefile&cat=admin&path={$value.filename}&start={$value.startline}&end={$value.endline}&back_btn={"?display=debhook&cat=admin"|base64_encode}'); return false;">{$value.declaration}</a>
                    </td>
                </tr>
            {elseif="$value.type == 'class'"}
                <tr class="roundedTdHiglighted"><td><b>{function="localize('class')"}</b></td><td colspan="2">class <b>{$value.name}</b><!-- (<a href="?display=debhook&cat=admin&view={$value.name}">{function="localize('Details')"}</a>)--></td></tr>
            {elseif="$value.type == 'function'"}
                <tr>
                    <td>{function="localize('function')"}</td>
                    <td><b>function</b> {$value.name}( {$value.params} )</td>
                    <td><a href="#" onclick="navigateTo('?display=browsefile&cat=admin&path={$value.filename}&start={$value.startline}&end={$value.endline}&back_btn={"?display=debhook&cat=admin"|base64_encode}'); return false;">{$value.declaration}</a>
                    </td>
                </tr>
            {/if}
         {/loop}
         {else}
            <tr><td colspan="3">{function="localize('No functions and/or classes found that matches search criteria')"}</td></tr>
         {/if}
        </tbody>
      </table>
</div>
