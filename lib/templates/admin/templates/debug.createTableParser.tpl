{if="!$popup"}
{include="ui.titlebar"}
{/if}

<script type="text/javascript">
$(document).ready(function() {
	$('#editForm').submit(function() {
		panthera.jsonPOST({data: '#editForm', url: '?display=debug.createTableParser&cat=admin&ajax', success: function (response) {
			if (response.status == 'success')
			{
				$('#result').val(response.resultText);
			}
		}});
		
		return false;
	});
});
</script>

<script src="{$PANTHERA_URL}/js/jquery-linedtextarea.js" type="text/javascript"></script>
<link href="{$PANTHERA_URL}/css/jquery-linedtextarea.css" type="text/css" rel="stylesheet">

<form action="{$AJAX_URL}?display=debug.createTableParser&cat=admin" method="POST" id="editForm">
    <div id="topContent" style="min-height: 50px;">
        <div class="searchBarButtonArea">
        
            <input type="button" value=" {function="localize('Serialize')"} " onclick="$('#responseType').val('serialize'); $('#editForm').submit();" style="float: right; margin-right: 15px;">
            <input type="button" value=" {function="localize('print_r')"} " onclick="$('#responseType').val('print_r'); $('#editForm').submit();" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('var_dump')"} " onclick="$('#responseType').val('var_dump'); $('#editForm').submit();" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('var_export')"} " onclick="$('#responseType').val('var_export'); $('#editForm').submit();" style="float: right; margin-right: 15px;"> 
            <input type="button" value=" {function="localize('json')"} " onclick="$('#responseType').val('json'); $('#editForm').submit();" style="float: right; margin-right: 15px;"> 
        </div>
    </div>
    
    <div class="ajax-content" style="text-align: center;">
        <div style="display: inline-table; margin: 0 auto;">
                <table>
                    <thead>
                        <tr>
                            <th scope="col" colspan="5" style="width: 250px;"><b>{function="localize('Paste CREATE TABLE statement here and select output format on buttons above', 'debug')"}</i></th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr id="jsonedit_result_tr" style='{if="$popup"}display: none;{/if}'>
                            <td style="padding: 0px;"><textarea id="input" name="input" style="width: 98%; height: 250px;">{if="isset($content) and $content"}{$content}{/if}</textarea></td>
                        </tr>
                        
                        <tr>
                            <td style="padding: 0px; min-width: 750px;"><textarea name="result" id="result" style="width: 100%; height: 300px;">{if="isset($code) and $code"}{$code}{/if}</textarea></td>
                        </tr>
                    </tbody>
                </table>
                
            <input type="hidden" name="responseType" id="responseType" value="">
            
           {if="$popup"}
            <div style="padding-top: 15px;">
                <div style="display: inline-block; float: left; margin-left: 10px;">
                    <input type="button" value="{function="localize('Close')"}" onclick="panthera.popup.close()">
                </div>
            
                <input type="button" value=" {function="localize('Serialize')"} " onclick="jsonEditSave('');" style="margin-right: 45px; float: right;">
            </div>
           {/if}
        </div>
    </div>
</form>

<script type="text/javascript">
$("#result").linedtextarea();
</script>
