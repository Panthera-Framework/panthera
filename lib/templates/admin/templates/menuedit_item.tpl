{$site_header}

<script type="text/javascript">

    /**
      * Save changes to database (item)
      *
      * @author Mateusz Warzyński
      */

    $('#save_form').submit(function () {
        panthera.jsonPOST({ data: '#save_form', success: function (response) {
                if (response.status == 'success')
                {
                    navigateTo('?display=menuedit&cat=admin&action=getCategory&category='+$('#item_category').val());
                }
            }
        });
        return false;
    });
    


    function selectLinkType()
    {
        if ($('input[name=item_type]:checked').val() == '1')
        {
            $('.menuedit_static').hide();
            $('.menuedit_routing').show();
        } else {
            $('.menuedit_routing').hide();
            $('.menuedit_static').show();
        }
    }
    
    function getRoutes()
    {
        route = $('#route').val();
    
        panthera.jsonPOST( {'url': '?display=menuedit&cat=admin&category={$category}&action=getRoutes&route='+route, success: function (response) {
        
                if (response.status == 'success')
                {
                    $('.tr_route_params').remove();
                    
                    for (var i in response.params)
                    {
                        $('.tr_route_link').before('<tr class="tr_route_params menuedit_routing"><td><b>'+response.params[i]+':</b></td><td><input type="text" name="routing_param_'+response.params[i]+'" id="routing_param_'+response.params[i]+'" style="width: 99%;"></td></tr>');
                        panthera.inputTimeout({interval: 1200, element: '#routing_param_'+response.params[i], callback: function () { generatePreview(); }});
                    }
                }
            }
        });
    }
    
    function generatePreview()
    {
        inputs = $('#save_form input');
        
        results = {};
        
        for (input in inputs)
        {
            try {
                id = inputs[input].getAttribute('id');
                
                if (id)
                {
                    if(id.substr(0, 14) == 'routing_param_')
                    {
                        results[id.substr(14, id.length)] = $('#'+id).val();
                    }
                }
            } catch (e) { }
        }
        
        console.log(results);
        console.log(JSON.stringify(results));
        
        data = 'getparams='+escape($('#routing_get').val())+'&params='+JSON.stringify(results)+'&route='+$('#route').val();
        
        panthera.jsonPOST( {url: '?display=menuedit&cat=admin&category={$category}&action=getPreviewRoute', data: data,  success: function (response) {
                if (response.status == 'success')
                {
                    $('#preview_link').val(response.url);
                }
            }
        });
    }
    
    panthera.inputTimeout({interval: 1200, element: '#routing_get', callback: function () { generatePreview(); }});
</script>



{include="ui.titlebar"}

<form id="save_form" method="POST" action="?display=menuedit&cat=admin&action=saveItem">

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <div style="float: left; display: inline-block; margin-left: 10px;">
            <input type="button" value="{function="localize('Back')"}" onclick="navigateTo('?display=menuedit&cat=admin&action=getCategory&category={$item_category}');">
        </div>
    
        <input type="submit" value="{function="localize('Save')"}" onclick="">
    </div>
</div>

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">
        <table style="display: inline-block;">
            <thead>
                <tr>
                    <th style="width: 250px;">&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>

            <tbody id="menuitem_tbody" class="menuitem_tbody">
                <tr>
                    <td>{function="localize('Title', 'menuedit')"}:</td>
                    <td><input type="text" name="item_title" value="{$item_title}" style="width: 99%;"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Language', 'menuedit')"}:</td>
                    <td>
                        <select name="item_language">
                        <option value="all" {if="$item_language == 'all'"}checked{/if}> {function="localize('all')"} </option>
                        {loop="$item_language"}
                            <option value="{$key}"{if="$value == True"} selected{/if}> {$key} </option>
                        {/loop}
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td>{function="localize('Category', 'menuedit')"}:</td>
                    <td>
                        <select name="category" id="item_category">
                        {loop="$categories"}
                        <option value="{$key}"{if="$key == $item_category"} selected{/if}>{$value}</option>
                        {/loop}
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td>{function="localize('Enabled', 'menuedit')"}:</td>
                    <td><input type="checkbox" name="item_enabled" value="1" {if="$enabled"}checked{/if}></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Link type', 'menuedit')"}:</td>
                    <td>
                        <input type="radio" name="item_type" value="1" id="item_type" {if="$route"}checked{/if} onchange="selectLinkType()"> {function="localize('SEO link (route)', 'menuedit')"}<br>
                        <input type="radio" name="item_type" value="2" id="item_type" {if="!$route"}checked{/if} onchange="selectLinkType()"> {function="localize('Normal link', 'menuedit')"}
                    </td>
                </tr>
                
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                
                <tr class="tr_route_name menuedit_routing" {if="!$route"}style="display: none;"{/if}>
                    <td>{function="localize('Route name', 'menuedit')"}:</td>
                    <td>
                        <select name="route" id="route" onchange="getRoutes()">
                            <option value=""></option>
                            {loop="$routes"}
                                <option value="{$key}" {if="$route == $key"}selected{/if}>{$key}</option>
                            {/loop}
                        </select>
                    </td>
                </tr>
                
                <tr class="tr_route_get menuedit_routing" {if="!$route"}style="display: none;"{/if}>
                    <td>{function="localize('URL params', 'menuedit')"}:</td>
                    <td>
                        <input type="text" name="routing_get" id="routing_get" value="{$routing_get}" style="width: 99%;">
                    </td>
                </tr>
                
                {if="is_array($routedata)"}
                {loop="$routedata"}
                <tr class="tr_routing_param_{$key} menuedit_routing tr_route_params" {if="!$route"}style="display: none;"{/if}>
                    <td><b>{$key}:</b></td>
                    <td>
                        <input type="text" name="routing_param_{$key}" id="routing_param_{$key}" value="{$value}" style="width: 99%;">
                    </td>
                </tr>
                {/loop}
                {/if}
                
                <tr class="tr_route_link menuedit_routing" {if="!$route"}style="display: none;"{/if}>
                    <td>{function="localize('Preview link', 'menuedit')"}:</td>
                    <td>
                        <input type="text" id="preview_link" value="{$linkPreview}" disabled style="width: 99%;">
                    </td>
                </tr>
                
                <tr class="tr_item_link menuedit_static" {if="$route"}style="display: none;"{/if}>
                    <td>{function="localize('Link', 'messages')"}:</td>
                    <td><input type="text" name="item_link" value="{$item_link}" style="width: 99%;"></td>
                </tr>
                
                <tr class="tr_item_link menuedit_static" {if="$route"}style="display: none;"{/if}>
                    <td>{function="localize('SEO friendly name', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small>:</td>
                    <td><input type="text" name="item_url_id" value="{$item_url_id}" style="width: 99%;"></td>
                </tr>
                
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                
                <tr>
                    <td>{function="localize('Tooltip', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"}):</small></td>
                    <td><input type="text" name="item_tooltip" value="{$item_tooltip}" style="width: 99%;"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Icon', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"}):</small></td>
                    <td><input type="text" name="item_icon" value="{$item_icon}" style="width: 99%;"></td>
                </tr>
                
                <tr>
                    <td>{function="localize('Attributes', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"}):</small></td>
                    <td><input type="text" name="item_attributes" value='{$item_attributes}' style="width: 99%;"></td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="item_id" value="{$item_id}">
        <input type="hidden" id="cat_type" name="cat_type" value="{$cat_type}">
</div>

</form>