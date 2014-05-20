{include="ui.titlebar"}

<script type="text/javascript">
$(document).ready(function() {
	$('#editForm').submit(function() {
		panthera.jsonPOST({ data: '#editForm', success: function (response) {
			if (response.status == 'success')
			{
				$('#aArray').val(response.a);
				$('#bArray').val(response.b);
			}
		}});
		
		return false;
	});
});
</script>

<form action="{$AJAX_URL}?display=mergephps&cat=admin" method="POST" id="editForm">
    <div id="topContent" style="min-height: 50px;">
        <div class="searchBarButtonArea">
            <input type="submit" value=" {function="localize('Merge')"} "> 
        </div>
    </div>

    <div class="ajax-content" style="text-align: center;">
    	<div class="tipBlock" style="width: 45%;">
	        <div class="tipBlockInside">
	            {function="localize('Did you know you can merge multiple arrays one by one? Simply merge A into B, the B will be a result, then replace A with C to merge A with B and with C.', 'debug')"}
	        </div>
	    </div>
    
        <div style="display: inline-table; margin: 0 auto;">
        	<table>
            	<thead>
                    <tr>
                        <th style="width: 100%;"><b>{function="localize('Enter JSON codes or serialized arrays to merge into one, second window is the result window', 'debug')"}</i></th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                    	<td style="width: 100%; padding-left: 0px; padding-right: 13px;"><textarea style="width: 100%; min-width: 600px; height: 300px;" name="a" id="aArray">{$a}</textarea></td>
                    </tr>
                    
                    <tr>
                    	<td style="width: 100%; padding-left: 0px; padding-right: 13px;"><textarea style="width: 100%; height: 300px;" name="b" id="bArray">{$b}</textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</form>

