{$site_header}

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    <div class="searchBarButtonArea">
    	{if="$category"}
    	<form action="?display=advertisements&cat=admin&block={$category->placename}" method="POST" id="removeBlockForm">
    		<input type="hidden" name="removeBlock" value="{$category->placename}">
    		<input type="submit" value="{function="localize('Remove this advertisement block')"}" style="float: right;">
    	</form>
    	
    	<input type="button" value="{function="localize('Add new advertisement', 'advertisements')"}" style="float: right;" onclick="panthera.popup.toggle('?display=advertisements&cat=admin&action=newAd');">
    	{/if}
    </div>
</div>

<script type="text/javascript">
$('#newBlockForm').submit(function () {
	panthera.jsonPOST({data: '#newBlockForm', success: function(response) {
		if (response.status == 'success') { navigateTo(window.location.href); }
	}});
	return false;
});

{if="$category"}
$('#removeBlockForm').submit(function () {
	panthera.confirmBox.create('{function="localize('Are you sure you want delete this block category?', 'advertisements')"}', function (responseText) {
        if (responseText == 'Yes')
        {
			panthera.jsonPOST({data: '#removeBlockForm', success: function(response) {
				if (response.status == 'success') { navigateTo(window.location.href); }
			}});
			
		}
	});
	
	return false;
});
{/if}
</script>

<div class="blockMenu">
	<li>
		<form action="?display=advertisements&cat=admin" method="POST" id="newBlockForm">
			<input type="text" name="blockName" placeholder="{function="localize('Create a new advertisement block', 'advertisements')"}"> 
			<input type="submit" value="{function="localize('Create', 'advertisements')"}">
		</form>
	</li>
	
	{if="$categories"}
	{loop="$categories"}
	<li>
		<a href="?display=advertisements&cat=admin&block={$value->placename}" class="ajax_link"{if="$value->description||debugTools::isDebugging()"} title="{if="debugTools::isDebugging()"}ID: {$value->placename} {/if}{$value->description}"{/if}>
			{if="$category and $category->placename == $value -> placename"}<b>{$value->title}</b>{else}{$value->title}{/if}
		</a>
	</li>
	{/loop}
	{else}
		<li>{function="localize('There are no any advertisement blocks', 'advertisements')"}</li>
	{/if}
</div>

<div class="ajax-content">
    <table style="min-width: 360px;">
    	<thead>
        	<tr>
            	<th>
                	<b>{function="localize('Position', 'advertisements')"}</b>
                </th>
                    
                <th>
                    <b>{function="localize('Name', 'advertisements')"}</b>
                </th>
                    
                <th>
                    <b>{function="localize('Expires', 'advertisements')"}</b>
                </th>
            </tr>
        </thead>
        
        <tbody class="hovered">
        	{if="$items"}
           	{loop="$items"}
           	<tr{if="Tools::dateExpired($value->expires)"} style="opacity: 0.4;"{/if}>
           		<td>{$value->position}</td>
           		<td><a style="cursor: pointer;" onclick="panthera.popup.toggle('?display=advertisements&cat=admin&action=editAd&adId={$value->adid}');">{$value->name}</a></td>
           		<td>{if="Tools::dateExpired($value->expires)"}<b>{$value->expires} ({function="localize('expired', 'advertisements')"})</b>{else}{$value->expires}{/if}</td>
           	</tr>
           	{/loop}
           	{else}
           	<tr>
           		<td colspan="3" style="text-align: center;">{function="localize('No any advertisements found to be placed in this block', 'advertisements')"}</td>
           	</tr>
           	{/if}
        </tbody>
    </table>
</div>
