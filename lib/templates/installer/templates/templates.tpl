{include="buttons"}
<h1>{function="localize('Templates', 'installer')"}</h1>

<script type="text/javascript">
function createNewTemplate()
{
    navigateTo('?action=createNewTemplate&name='+$('#createNew').val());
}
</script>

<span class="description">
    {function="localize('Every website needs a layout and a template describing it. You need to create a default template for your application, and optionaly make views for mobile devices.', 'installer')"}
</span>

<table class="table" style="margin: 0 auto; width: 80%; margin-top: 30px;">
    <thead>
        <tr>
            <td><b>{function="localize('Avaliable templates to use', 'installer')"}</b></td>
            <td><b>{function="localize('Mobile version', 'installer')"}</b></td>
            <td><b>{function="localize('Tablet version', 'installer')"}</b></td>
        </tr>
    </thead>
    
    <tbody>
        {loop="$templates"}
        <tr>
            <td><input type="radio" name="selectedTemplate"{if="$value['active'] == True"} checked{else} onclick="navigateTo('?action=setDefaultTemplate&name={$key}')"{/if}> {$key}</td>
            {if="$value['mobile'] == False and $value['tablet'] == False"}
            <td colspan="2">{function="localize('Create view', 'installer')"}: <br><li><a href="?action=createView&view=mobile&template={$key}">{function="localize('mobile', 'installer')"}</a></li> <li><a href="?action=createView&view=tablet&template={$key}">{function="localize('tablet', 'installer')"}</a></li> <li><a href="?action=createView&view=mobile,tablet&template={$key}">{function="localize('same view for mobile and tablet devices', 'installer')"}</a></li></td>
            {else}
            <td>
                {if="$value['mobile'] == True"}
                    {function="localize('Avaliable', 'installer')"}
                {else}
                    <a href="?action=createView&view=mobile&template={$key}">{function="localize('Create mobile view', 'installer')"}</a>{if="$value['tablet'] == True"} {function="localize('or', 'installer')"} <a href="?action=connectView&from=tablet&to=mobile&template={$key}">{function="localize('connect with tablet view', 'installer')"}</a>
                    {/if}
                {/if}
            </td>
            <td>
                {if="$value['tablet'] == True"}
                    {function="localize('Avaliable', 'installer')"}
                    {else}
                    <a href="?action=createView&view=tablet&template={$key}">{function="localize('Create tablet view', 'installer')"}</a>{if="$value['mobile'] == True"} {function="localize('or', 'installer')"} <a href="?action=connectView&from=tablet&to=mobile&template={$key}">{function="localize('connect with mobile view', 'installer')"}</a>
                    {/if}
                {/if}</td>
            {/if}
        {/loop}
    
        <tr>
            <td><input type="text" name="createNew" id="createNew" placeholder="default" style="width: 60%;"> <input type="button" class="submitButton" onclick="createNewTemplate()" value="{function="localize('Create new', 'installer')"}" style="width: 30%; max-width: 100px;"></td>
            <td><input type="checkbox" name="newMobileView" disabled></td>
            <td><input type="checkbox" name="newTabletView" disabled></td>
        </tr>
    </tbody>
</table>
