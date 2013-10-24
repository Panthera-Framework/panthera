{include="buttons"}

<script type="text/javascript">
function selectDatabaseType ()
{
    
    // sqlite -> file, mysql -> server,
    
    var dbType = $("#selectDatabase").val();
    
    if (dbType == 'sqlite') {
        var dbName = 'file';
    }
    
    if (dbType == 'mysql') {
        var dbName = 'server';
    }
    
    $('#dbSocket').html(dbName);
    
    if (dbType == 'sqlite')
    {
        $('.serverBasedDB').hide('slow', function () { $('.fileBasedDB').show(); });
    } else {
        $('.fileBasedDB').hide('slow', function () { $('.serverBasedDB').show(); });
    }
    
    $('#tablesListTable').hide();
}

$(document).ready (function () {
    {if="$databaseSockets[$databaseSettings['db_socket']] == 'file'"}
         $('.serverBasedDB').hide();
         $('.fileBasedDB').show();
    {/if}
    
    {if="$databaseSockets[$databaseSettings['db_socket']] == 'server'"}
         $('.fileBasedDB').hide();
         $('.serverBasedDB').show();
    {/if}
    
    /*panthera.inputTimeout({ element: '#dbPrefix', interval: 900, callback: function () { databaseCheck(); } });
    panthera.inputTimeout({ element: '#dbFile', interval: 900, callback: function () { databaseCheck(); } });
    panthera.inputTimeout({ element: '#dbHost', interval: 900, callback: function () { databaseCheck(); } });
    panthera.inputTimeout({ element: '#dbUser', interval: 900, callback: function () { databaseCheck(); } });
    panthera.inputTimeout({ element: '#dbPassword', interval: 900, callback: function () { databaseCheck(); } });
    panthera.inputTimeout({ element: '#dbName', interval: 900, callback: function () { databaseCheck(); } });*/
});

customNextBtn = true;
var collisionsSelection = false;

/**
  * Check database for collisions and provide ways to solve collisions
  *
  * @author Damian Kęska
  */

function databaseCheck()
{
    data = 'db_prefix='+$('#dbPrefix').val()+'&db_file='+$('#dbFile').val()+'&db_socket='+$('#dbSocket').html()+'&db_username='+$('#dbUser').val()+'&db_password='+$('#dbPassword').val()+'&db_host='+$('#dbHost').val()+'&db_name='+$('#dbName').val();
    
    if (collisionsSelection == true)
    {
        data = data + '&collisionsSelection='+$('input:radio[name=collisionsSelection]:checked').val();
    }
    
    panthera.jsonPOST( { url: '?', data: data, async: true, success: function (response) {
        
            $("#mysql_checkTable").show();
            
            if (response.status == 'success')
            {
                $('#installer-controll-nextBtn').attr('disabled', false);
                $('.databaseError').hide();
                
                console.log(response.tables.length);
                
                errors = 0;
                
                if (response.tables)
                {
                    $('#tablesListTable').slideDown('slow');
                    $('#tablesList').html('');
                    
                    for (table in response.tables)
                    {
                        tdStyle = '';
                    
                        if (response.tables[table] == true)
                        {
                            errors++;
                            tdStyle = 'font-weight: 700;';
                        }
                        $('#tablesList').append('<tr style="font-size: 13px; background: #ffd4d4;"><td><i>'+table+'</i></td></tr>');
                        // $('#tablesList').append('<tr><td style="width: 90%; '+tdStyle+'"><i>'+table+'</i></td><td style="width: 10%;"><!--<input type="button" class="button redButton" value="{function="localize('Drop', 'installer')"}" onclick="customNextBtn = false; navigateTo(\'?_stepbackward=True\');">--></td></tr>');
                    }
                }
                
                if (errors > 0)
                {
                    $('#installer-controll-nextBtn').attr('disabled', true);
                    collisionsSelection = true;
                    
                    $('#tablesList').append('<tr><td><p>{function="localize('Detected', 'installer')"} '+errors+' {function="localize('collisions', 'installer')"}. {function="localize('What to do?', 'installer')"}</p></td></tr>');
                    $('#tablesList').append('<tr><td><p><input type="radio" name="collisionsSelection" value="leaveExisting"> {function="localize('Leave all tables without changes, but create missing ones', 'installer')"}</p></td></tr>');
                    $('#tablesList').append('<tr><td><p><input type="radio" name="collisionsSelection" value="backupAndDrop"> {function="localize('Make a backup and drop old tables', 'installer')"}</p></td></tr>');
                    $('#tablesList').append('<tr><td><p><input type="radio" name="collisionsSelection" value="simplyDrop"> {function="localize('Just drop the old tables and create new', 'installer')"}</p></td></tr>');
                } else
                    collisionsSelection = false;
                
            } else {
                $('#installer-controll-nextBtn').attr('disabled', true);
                $('.databaseError').show();
                $('#databaseError').html(response.message);
                
                if (response.field != '')
                {
                    $('#dbFileCreate').hide();
                
                    if (response.field == 'db_file')
                    {
                        $('#dbFileCreate').show();
                    }
                }
            }
        } 
    });
}

function createNewFile()
{
    panthera.jsonPOST( { url: '?', data: 'createDBFile='+$('#dbFile').val(), async: true, success: function (response) {
            if (response.status == 'success')
            {
                $('#dbFileCreate').hide();
                customNextBtn = false;
                $('#installer-controll-nextBtn').attr('disabled', false);
                $('.databaseError').hide();
            }
        } 
    });
}

$(document).bind('onCheckBtn', function () { 
    databaseCheck();
});

$(document).bind('onNextBtn', function () { 
    panthera.jsonPOST( { url: '?', data: 'save=True', async: false, success: function (response) {
            if (response.status == 'success')
            {
                navigateTo('?_nextstep=True');
            }
        }
    });
});
</script>

<style>
#installer-controll-checkBtn {
    display: inline;
}
</style>

<div class="header">
        <h1>{function="localize('Database connection', 'installer')"}.</h1>
        <div style="margin-left: 5px;"><span>{function="localize('Please fill the details of your server', 'installer')"}.</span></div>
</div>

<div class="content">
    
    <table style="border: 0;">
      <tbody>
          <tr>
              <td><p><b>{function="localize('Database type', 'installer')"}:</b></td>
              <td style="padding-left: 10px;">
                    <select style="width: 212px;" onchange="selectDatabaseType();" id="selectDatabase">
                       {loop="$databaseSockets"}
                        <option {if="$databaseSettings.db_socket == $key"} selected {/if}>{$key}</option>
                       {/loop}
                    </select>
              </td>
          </tr>
          
          <tr>
              <td><p><b>{function="localize('Prefix', 'installer')"}:</p></b></td>
              <td  style="padding-left: 10px;"><input type="text" id="dbPrefix" style="width: 50%;" placeholder="pa_" value="{if="isset($databaseSettings['db_prefix'])"}{$databaseSettings.db_prefix}{/if}"></td>
          </tr>
          
          <tr class="fileBasedDB">
              <td><p><b>{function="localize('File name', 'installer')"}:</p></b></td>
              <td  style="padding-left: 10px;"><input type="text" id="dbFile" style="width: 50%;" placeholder="eg. db (/content/database/db.sqlite3)" value="{if="isset($databaseSettings['db_file'])"}{$databaseSettings.db_file}{/if}"> 
                <span id="dbFileCreate" style="margin-left: 15px; display: none;"><small>{function="localize('Database file does not exists', 'installer')"}. <a href="#" onclick="createNewFile()"><b>{function="localize('Create new file?', 'installer')"}</b></a></small></span></td>
          </tr>
          
          <tr class="serverBasedDB">
              <td><p><b>{function="localize('Host', 'installer')"}:</p></b></td>
              <td style="padding-left: 10px;"><input type="text" id="dbHost" style="width: 50%;" placeholder="localhost" value="{if="isset($databaseSettings['db_host'])"}{$databaseSettings.db_host}{/if}"></td> 
          </tr>
          
          <tr class="serverBasedDB">
              <td><p><b>{function="localize('Login', 'installer')"}:</p></b></td>
              <td style="padding-left: 10px;"><input type="text" id="dbUser" style="width: 50%;" placeholder="panthera" value="{if="isset($databaseSettings['db_username'])"}{$databaseSettings.db_username}{/if}"></td> 
          </tr>
          
          <tr class="serverBasedDB">
              <td><p><b>{function="localize('Password', 'installer')"}:</p></b></td>
              <td style="padding-left: 10px;"><input type="password" id="dbPassword" style="width: 50%;" value="{if="isset($databaseSettings['db_password'])"}{$databaseSettings.db_password}{/if}"></td> 
          </tr>
          
          <tr class="serverBasedDB">
              <td><p><b>{function="localize('Database name', 'installer')"}:</p></b></td>
              <td style="padding-left: 10px;"><input type="text" id="dbName" style="width: 50%;" placeholder="my_site" value="{if="isset($databaseSettings['db_name'])"}{$databaseSettings.db_name}{/if}"></td> 
          </tr>
          
          <tr class="databaseError" style="display: none;">
              <td colspan="3" id="databaseError" style="color: red;"></td>
          </tr>
      </tbody>
     </table>
     
     <div id="mysql_checkTable" style="display: none;">
         <table class="table" style="width: 80%; margin: 0 auto; margin-top: 50px; margin-bottom: 50px; border: none; border-spacing: 0px;" id="tablesListTable">
            <thead>
              <tr><td colspan="1"><p>{function="localize('Existing tables in selected database', 'installer')"}:</p></td></tr>
            </thead>
    
            <tbody id="tablesList">
            </tbody>
         </table>
     </div> 
    
</div>