{include="buttons"}

<script type="text/javascript">
    function saveCacheSettings()
    {
        panthera.jsonPOST( { data: '#saveCacheSettingsForm', success: function (response) {
                if (response.status == 'success')
                {
                    $('#installer-controll-nextBtn').attr('disabled', false);
                }
            }
        });
    }

    $(document).ready(function () {
        panthera.inputTimeout({ element: '#cache', interval: 900, callback: function () { saveCacheSettings(); } });
        panthera.inputTimeout({ element: '#varCache', interval: 900, callback: function () { saveCacheSettings(); } });
    });
</script>

<h1>{function="localize('Cache settings')"}</h1>

<span class="description">{function="localize('Please spent a minute on setting caching settings, correct settings can improve your application performance even on 2-3 times. You can handle more users without need to buy better hardware.', 'installer')"}</span>

<form action="?" method="POST" id="saveCacheSettingsForm">
<table class="table" style="width: 90%; margin: 0 auto; margin-top: 30px;">
    <tr>
        <td>
            <b>cache</b><br><small><i>{function="localize('Needs to be really fast, huge amounts of data are stored here. Set only in-memory caching methods here - APC, XCache, Memcached', 'cache')"}</i></small>
        </td>
        
        <td>
            <select name="cache" id="cache">
                <option value=""></option>
                {loop="$cache_list"}
                    {if="$value == True"}
                    <option {if="$cache == $key"} selected {/if}>{$key}</option>
                    {/if}
                {/loop}
            </select>
        </td>
    </tr>
    
    <tr>
        <td>
            <b>varCache</b><br><small><i>{function="localize('Used to store simple variables, this can be a database cache, but if any in-memory cache is avaliable, select it', 'cache')"}</i></small>
        </td>
        
        <td>
            <select name="varCache" id="varCache">
                <option value=""></option>
                {loop="$cache_list"}
                    {if="$value == True"}
                    <option {if="$varCache == $key"} selected {/if}>{$key}</option>
                    {/if}
                {/loop}
            </select>
        </td>
    </tr>
</table>
</form>
