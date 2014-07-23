<style>
.checkboxColumn {
	width: 20px;
	padding-right: 0px;
}
</style>

<div style="text-align: center;">
	<form action="?{function="Tools::getQueryString('GET', 'action=saveRecipients', '_')"}" method="POST" id="newsletterRecipients">
		<div style="margin: 0 auto; display: inline-block; text-align: center; min-width: 50%;">
			<table style="margin: 0 auto; min-width: 100%;">
			    <thead>
				    <tr>
				    	<th colspan="2"><h3><b>{function="localize('Gender', 'users')"}</b></h3></th>
				    </tr>
			    </thead>
			            
			    <tbody class="bgTable">
			    {if="$genders"}{loop="$genders"}
			    	<tr><td class="checkboxColumn"><input type="checkbox" name="gender[]" value="{$key}"></td><td><i>{function="localize($key, 'users')"} {if="$value"}({$value}){/if}</i></td></tr>{/loop}{else}
			    	<tr><td class="checkboxColumn" colspan="2">{function="localize('No data found', 'newsletter')"}</td></tr>{/if}
			    
			    </tbody>
			</table>
			
			
			<table style="margin: 0 auto; min-width: 100%; margin-top: 35px;">
				<thead>
			    	<tr>
			    		<th colspan="2"><h3><b>{function="localize('Group', 'users')"}</b></h3></th>
			    	</tr>
			    </thead>
			
			
			    <tbody class="bgTable">
			    	{if="$groups"}{loop="$groups"}
			    	<tr><td class="checkboxColumn"><input type="checkbox" name="group[]" value="{$key}"> </td><td><i>{$value['name']} {if="$value['description']"}({$value['description']}){/if} {if="$value['count']"}({$value['count']}){/if}</i></td></tr>{/loop}{else}
			    	<tr><td class="checkboxColumn" colspan="2">{function="localize('No data found', 'newsletter')"}</td></tr>{/if}
			    </tbody>
			</table>
			
			
			<table style="margin: 0 auto; min-width: 100%; margin-top: 35px;">
				<thead>
			    	<tr>
			    		<th colspan="2"><h3><b>{function="localize('Premium', 'premium')"}</b></h3></th>
			    	</tr>
			    </thead>
			
			    <tbody class="bgTable">
			    	<tr><td class="checkboxColumn"><input type="checkbox" name="premium[]" value="premium"></td><td>{function="localize('Premium account', 'premium')"} ({$premium['premium']})</td></tr>
			    	<tr><td class="checkboxColumn"><input type="checkbox" name="premium[]" value="free"></td><td>{function="localize('Free account', 'premium')"} ({$premium['free']})</td></tr>
			    </tbody>
			</table>
			
			
			<table style="margin: 0 auto; min-width: 100%; margin-top: 35px;">
				<thead>
					<tr>
			    		<th colspan="3"><h3><b>{function="localize('City', 'users')"}</b></h3></th>
			    	</tr>
			   	</thead>
			   	
			   	<tbody class="bgTable">
			   	{if="$cities"}{loop="$cities"}
			    	<tr><td class="checkboxColumn"><input type="checkbox" name="city[]" value="{$key}"> </td><td><i>{$key} ({$value})</i></td></tr>{/loop}{else}
			    	<tr><td class="checkboxColumn" colspan="3">{function="localize('No data found', 'newsletter')"}</td></tr>{/if}
			    </tbody>
			</table>
			
			<div style="margin-top: 30px;"></div>
			<div style="float: left;"><input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close();"></div>
			<div style="float: right;"><input type="submit" value="{function="localize('Select')"}"></div>
		</div>
	</form>
	
	<script type="text/javascript">
	$('#newsletterRecipients').submit(function () {
		panthera.jsonPOST({data: '#newsletterRecipients', success: function (response) {
			if (response.status == 'success')
			{
				$('#recipientsData').val(response.json);
				panthera.popup.close();
			}
		}});
	
		return false;
	});
	
	if ($('#recipientsData').length && $('#recipientsData').val())
	{
		json = JSON.parse($('#recipientsData').val());
		
			for(section in json)
			{
				console.log('Entering section '+section);
				eval('items = json.'+section);
			
				for (item in items)
				{
					console.log('Adding item '+items[item]);
					$('input[name="'+section+'[]"][value="'+items[item]+'"]').attr('checked', true);
				}
			}
	}
	</script>
</div>