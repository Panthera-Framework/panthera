<script type="text/javascript">
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

var uploadSpinner = new panthera.ajaxLoader($('#packageInfoWindow'));
var buildSpinner = new panthera.ajaxLoader($('#buildPackageWindow'));
var packageSpinner = new panthera.ajaxLoader($('#packageInfoWindow'));

/**
  * Installing and removing packages
  *
  * @author Damian Kęska
  */

function managePackage(packageName, type)
{
    panthera.jsonPOST({ url: '?display=leopard&cat=admin&action=manage', data: 'package='+packageName+'&job='+type, async: true, spinner: packageSpinner, success: function (response) {
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
        list[pkg].info.name;
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

$(document).ready(function () {
    /**
      * Package upload
      *
      * @author Damian Kęska
      */

    $('#uploadFileForm').submit(function () {
        
        panthera.jsonPOST({ data: '#uploadFileForm', async: true, spinner: uploadSpinner, success: function (response) {
                if (response.status == "success")
                {
                    $('.uploadPackageTR').show();
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
    
    $('#buildPackageForm').submit(function () {
        panthera.jsonPOST({ data: '#buildPackageForm', async: true, spinner: buildSpinner, success: function (response) {
                if (response.status == "success")
                {
                    window.location = response.url;
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

});
</script>

<div class="titlebar">{function="localize('Packages management', 'leopard')"}{include="_navigation_panel.tpl"}</div>

<div class="grid-2" id="installedPackagesWindow" style="position: relative;">
          <div class="title-grid">{function="localize('Installed packages', 'leopard')"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{function="localize('All installed packages in this system', 'leopard')"}</small></td>
                    </tr>
                </tfoot>
            
                <tbody id="installedPackagesList">
                    <tr id="noPackages" {if="count($installedPackages) > 0"}style="display: none;"{/if}>
                        <td colspan="5" style="text-align: center;">{function="localize('No installed packages', 'leopard')"}</td>
                    </tr>
                
                    {loop="$installedPackages"}
                    <tr class="packageFromList">
                        <td>{$key}-{$value.info.version}-{$value.info.release}</td><td><input type="button" value="{function="localize('Remove', 'leopard')"}" onclick="managePackage('{$key}', 'uninstall')"></td>
                    </tr>
                    {/loop}
                </tbody>
            </table>
         </div>
</div>
       

<div class="grid-2" id="buildPackageWindow" style="position: relative;">
      <div class="title-grid">{function="localize('Build package', 'leopard')"}<span></span></div>
      <div class="content-table-grid">
        <form action="?display=leopard&cat=admin&action=create" method="POST" id="buildPackageForm">
          <table class="insideGridTable">
            <tr>
                <td>{function="localize('Name', 'leopard')"}:</td><td><input type="text" name="name" style="width: 95%;" value="{$buildName}"></td>
            </tr>
            
            <tr>
                <td>{function="localize('Directory or repository to build from', 'leopard')"}:</td><td><input type="text" name="directory" id="repositoryUrl" value="{if="isset($buildPath)"}{$buildPath}{else}{$SITE_DIR}/example{/if}" style="width: 95%;"> <input type="text" name="branch" id="repositoryBranch" style="display: none;" value="{if="isset($buildBranch)"}{$buildBranch}{else}master{/if}"></td>
            </tr>
            
            <tr>
                <td>&nbsp;</td><td><input type="submit" value="{function="localize('Build and download', 'leopard')"}"></td>
            </tr>
          </table>
        </form>
      </div>
</div>

<div class="grid-2" id="consoleOutputWindow" style="position: relative;">
      <div class="title-grid">{function="localize('Console output', 'leopard')"}<span></span></div>
      <div class="content-table-grid">
          <div class="consoleOutput" id="consoleLog">{$consoleOutput}</div>
      </div>
</div>

<div class="grid-2" id="packageInfoWindow" style="position: relative;">
      <div class="title-grid">{function="localize('Upload package', 'leopard')"}<span></span></div>
      <div class="content-table-grid">
          <table class="insideGridTable">
            <tr class="uploadPackageTR" style="display: none;">
                <td>{function="localize('Name', 'leopard')"}:</td><td id="packageName">test-1.0-1</td>
            </tr>
            
            <tr class="uploadPackageTR" style="display: none;">
                <td>{function="localize('Description', 'leopard')"}:</td><td id="packageDescription">Test package</td>
            </tr>
            
            <tr class="uploadPackageTR" style="display: none;">
                <td>{function="localize('Author', 'leopard')"}:</td><td id="packageAuthor">Damian Kęska</td>
            </tr>
            
            <tr class="uploadPackageTR" style="display: none;">
                <td>{function="localize('Website', 'leopard')"}:</td><td id="packageWebsite">http://panthera.kablownia.org</td>
            </tr>
            
            <tr class="uploadPackageTR" style="display: none;">
                <td>{function="localize('Installed', 'leopard')"}:</td><td id="packageInstalled"><input type="button" value="Uninstall"></td>
            </tr>
            
            <tr>
                <td>{function="localize('Select a file', 'leopard')"}:</td>
                <td><form action="?display=leopard&cat=admin&action=upload" method="POST" enctype="multipart/form-data" id="uploadFileForm"><input type="file" name="packageFile"> <input type="submit" value="{function="localize('Send', 'leopard')"}"></form></td>
            </tr>
          </table>
      </div>
</div>
