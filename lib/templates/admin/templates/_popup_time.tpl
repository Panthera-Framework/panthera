<h2 class="popupHeading">{"Pick a time"|localize:popups}</h2>

{if $type == "countSeconds"}
<script type="text/javascript">
function _popup_pickDate()
{
    n = parseInt($('#_popup_time_number').val());
    
    if (n < 1)
    {
        $('#_popup_time_failed').html('{"Input must be higher than zero"|localize:popups}');
        $('#_popup_time_failed').show('slow');
        return false;
    }
    
    $('#_popup_time_failed').hide('slow');
    
    result = 0;

    switch ($('#_popup_time_type').val())
    {
        case 'y':
            result = 60*60*24*365*n;
        break;
        
        case 'm':
            result = 60*60*24*30*n;
        break;
        
        case 'w':
            result = 60*60*24*7*n;
        break;
        
        case 'd':
            result = 60*60*24*n;
        break;
        
        case 'g':
            result = 60*60*n;
        break;
        
        case 'i':
            result = 60*n;
        break;
        
        case 's':
            result = n;
        break;
    }
    
    callback = eval("{$callback}");

    if (typeof callback == 'function')
    {
        callback(result);
        $().w2popup('close');
    }
}
</script>

<div class="msgError" id="_popup_time_failed"></div>

<table class="gridTable">
    <tfoot>
        <tr>
            <td colspan="5" class="rounded-foot-left">
                <em>Panthera - time</em>
            </td>
        </tr>
    </tfoot>
            
    <tbody id="user_list_tbody">
    <tr>
        <td style="border-right: 0px;">
            <input type="number" id="_popup_time_number" value="1" min="1" style="width: 100%;">
        </td>
        
        <td>
            <select id="_popup_time_type">
                <option value="y">{"years"|localize:popups}</option>
                <option value="m">{"months"|localize:popups}</option>
                <option value="w">{"weeks"|localize:popups}</option>
                <option value="d">{"days"|localize:popups}</option>
                <option value="g">{"hours"|localize:popups}</option>
                <option value="i" selected>{"minutes"|localize:popups}</option>
                <option value="s">{"seconds"|localize:popups}</option>
            </select>
            
            <input type="button" value="{"Pick"|localize:popups}" onclick="_popup_pickDate();">
        </td>
    </tr>

    </tbody>
</table>
{/if}
