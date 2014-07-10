{$site_header}

<script type="text/javascript">
/**
 * Installing and removing packages
 *
 * @author Damian Kęska
 */

function managePackage(packageName, type)
{
    panthera.jsonPOST({ url: '?display=leopard&cat=admin&action=manage', data: 'package='+packageName+'&job='+type, async: true, success: function (response) {
            $('#consoleLog').html(response.log);
            updatePackagesList(response.packages);
            
            if (type == 'install')
                $('#packageInstalled').html('<input type="button" value="{function="localize('Remove', 'leopard')"}" onclick="managePackage(\'_currentUploaded\', \'uninstall\');">');
            else
                $('#packageInstalled').html('<input type="button" value="{function="localize('Install', 'leopard')"}" onclick="managePackage(\'_currentUploaded\', \'install\');">');
        }
    });
}

/**
 * Update list of packages
 *
 * @author Damian Kęska
 */

function updatePackagesList(list)
{
    $('.packageFromList').remove();

    i=0;
    for (pkg in list)
    {
        i++;
        $('#installedPackagesList').append('<tr class="packageFromList"><td>'+pkg+'-'+list[pkg].info.version+'-'+list[pkg].info.release+'</td><td><input type="button" value="{function="localize('Remove', 'leopard')"}" onclick="managePackage(\''+pkg+'\', \'uninstall\')"></td></tr>');
    }
    
    if (i > 0)
        $('#noPackages').hide();
    else
    	$('#noPackages').show();
}

/**
 * Check if its a directory or a source code repository
 *
 * @author Damian Kęska
 */

function checkRepositoryUrl()
{
    url = $('#repositoryUrl').val().toLowerCase();
    
    if (url.substr(-4) == '.git' || url.substr(0, 7) == 'http://' || url.substr(0, 8) == 'https://' || url.substr(0, 6) == 'ssh://')
    {
        $('#repositoryUrl').css({'width': '64%'});
        $('#repositoryBranch').css({'width': '30%'});
        $('#repositoryBranch').show('slow');
    } else {
        $('#repositoryUrl').css({'width': '95%'});
        $('#repositoryBranch').hide('slow');
    }
    
    
}
</script>

{include="ui.titlebar"}

<div id="topContent">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Build package', 'leopard')"}" onclick="panthera.popup.toggle('element:#buildPackage')">
        <input type="button" value="{function="localize('Upload package', 'leopard')"}" onclick="panthera.popup.toggle('element:#uploadPackage')">
    </div>
</div>

<!-- Build package -->
<div id="buildPackage" style="display: none;">
   <form action="?display=leopard&cat=admin&action=create" method="POST" id="buildPackageForm">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        
        <tfoot>
            <tr>
                <td colspan="3" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                    <input type="submit" value="{function="localize('Build and download', 'leopard')"}" style="float: right; margin-right: 30px;">
                </td>
            </tr>
        </tfoot>
        
        <tbody>
          <!--<tr>
            <th>{function="localize('Name', 'leopard')"}:</th>
            <th><input type="text" name="name" style="width: 95%;" value="{$buildName}"></th>
          </tr>-->
          
          <tr>
            <th>{function="localize('Directory or repository to build from', 'leopard')"}:</th>
            <th>
                <input type="text" name="directory" id="repositoryUrl" value="{if="isset($buildPath)"}{$buildPath}{else}{$SITE_DIR}/example{/if}" style="width: 95%;">
                <input type="text" name="branch" id="repositoryBranch" style="display: none;" value="{if="isset($buildBranch)"}{$buildBranch}{else}master{/if}">
            </th>
          </tr>
          
          <tr>
            <th>{function="localize('Mode', 'leopard')"}:</th>
            <th><input type="radio" name="buildMode" value="justBuild"{if="$buildMode == 'justBuild'"} checked{/if}> {function="localize('Download', 'leopard')"} <input type="radio" name="buildMode" value="install" {if="$buildMode == 'install'"} checked{/if}> {function="localize('Install', 'leopard')"} <input type="radio" name="buildMode" value="reinstall" {if="$buildMode == 'reinstall'"} checked{/if}> {function="localize('Reinstall', 'leopard')"}</th>
          </tr>
          
        </tbody>
    </table>
   </form>
   
   <script type="text/javascript">
   $('#buildPackageForm').submit(function () {
        panthera.jsonPOST({ data: '#buildPackageForm', async: true, success: function (response) {
                if (response.status == "success")
                {
                    if (response.url)
                        window.location = response.url;
                    
                    updatePackagesList(response.packages);
                } else {
                    if (response.message != undefined)
                        w2alert(response.message, '{function="localize('Error', 'leopard')"}');
                }
                
                $('#consoleLog').html(response.log);
            }
        });
        
        return false;
    });
    
    panthera.inputTimeout({ element: '#repositoryUrl', interval: 200, callback: checkRepositoryUrl });
    </script>
</div>

<!-- Upload package -->
<div id="uploadPackage" style="display: none;">
    <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
        <tbody>
          <tr class="packageDetails" style="display: none;">
            <th>{function="localize('Name', 'leopard')"}:</th>
            <th id="packageName">test-1.0-1</th>
          </tr>
          
          <tr class="packageDetails" style="display: none;">
            <th>{function="localize('Description', 'leopard')"}:</th>
            <th id="packageDescription">Test package</th>
          </tr>
          
          <tr class="packageDetails" style="display: none;">
            <th>{function="localize('Author', 'leopard')"}:</th>
            <th id="packageAuthor">Mateusz Warzyński</th>
          </tr>
          
          <tr class="packageDetails" style="display: none;">
            <th>{function="localize('Website', 'leopard')"}:</th>
            <th id="packageWebsite">http://google.com</th>
          </tr>
          
          <tr class="packageDetails" style="display: none;">
            <th>{function="localize('Installed', 'leopard')"}:</th>
            <th id="packageInstalled"><input type="button" value="Uninstall"></th>
          </tr>
          
          <tr>
            <th>{function="localize('Select a file', 'leopard')"}:</th>
            <th><form action="?display=leopard&cat=admin&action=upload" method="POST" enctype="multipart/form-data" id="uploadFileForm"><input type="file" name="packageFile"> <input type="submit" value="{function="localize('Send', 'leopard')"}"></form></th>
          </tr>
        </tbody>
    </table>
    
    <script type="text/javascript">
    /**
      * Package upload
      *
      * @author Damian Kęska
      */

    $('#uploadFileForm').submit(function () {
        
        panthera.jsonPOST({ data: '#uploadFileForm', async: true, success: function (response) {
                if (response.status == "success")
                {
                    $('.packageDetails').show();
                    $('#packageName').html(response.name+"-"+response.version+"-"+response.release);
                    $('#packageAuthor').html(response.author);
                    
                    if (response.website.length > 0)
                        $('#packageWebsite').html(response.website);
                        
                    if (response.description.length > 0)
                        $('#packageDescription').html(response.description);
                    
                    $('#consoleLog').html(response.log);
                    
                    if (response.installed)
                        $('#packageInstalled').html('<input type="button" value="{function="localize('Remove', 'leopard')"}" onclick="managePackage(\'_currentUploaded\', \'uninstall\');">');
                    else
                        $('#packageInstalled').html('<input type="button" value="{function="localize('Install', 'leopard')"}" onclick="managePackage(\'_currentUploaded\', \'install\');">');
                        
                } else {
                    if (typeof response.log !== undefined)
                    {
                        $('#consoleLog').html(response.log);
                    }
                }
            }
        });
        
        return false;
    });
    </script>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block; margin-bottom: 25px;">
        <thead>
            <tr><th colspan="2"><b>{function="localize('Installed packages', 'leopard')"}</b></th></tr>
        </thead>

        <br>

        <tbody id="installedPackagesList">
           
                <tr class="noPackages" {if="count($installedPackages) < 1"}style="display: none;"{/if}>
                    <td style="min-width: 400px;" colspan="2">{function="localize('There are no any installed packages', 'leopard')"}</td>
                </tr>
            {loop="$installedPackages"}
                <tr class="packageFromList">
                    <td style="min-width: 250px;">{$key}-{$value.info.version}-{$value.info.release}</td>
                    <td><input type="button" value="{function="localize('Remove', 'leopard')"}" onclick="managePackage('{$key}', 'uninstall')"></td>
                </tr>
            {/loop}

        </tbody>
    </table>
    
    <table style="display: inline-block; width: 50%;">
		<thead>
			<th>{function="localize('Leopard output', 'leopard')"}</th>
		</thead>
		
		<tbody>
			<tr>
				<td id="consoleLog" style="background-color: black; color: white; font-family: Terminal; font-size: 11px; padding-top: 2px;">
					{$consoleOutput}
				</td>
			</tr>
		</tbody>
	</table>
</div>