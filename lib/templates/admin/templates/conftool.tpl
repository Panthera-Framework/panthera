{$site_header}
<script type="text/javascript">
    var spinner = new panthera.ajaxLoader($('#conftoolTable'));
    
    /**
      * Save variable to database
      *
      * @author Mateusz Warzyński
      */
    
    function saveVariable(jid)
    {
        id = jid.replace('_-_', '.');
        value = $('#value_'+jid).val();
        section = $('#section_'+jid).val();
    
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=conftool&cat=admin&action=change', data: 'id='+id+'&value='+escape(value)+'&section='+section, messageBox: 'w2ui', spinner: spinner});
    }
    
    function removeKey(jid)
    {
        id = jid.replace('_-_', '.');
    
                panthera.jsonPOST({ url: '{$AJAX_URL}?display=conftool&cat=admin&action=remove', data: 'key='+id, messageBox: 'w2ui', spinner: spinner, success: function (response) {
                        if (response.status == "success")
                        {
                           $('#tr_'+jid).remove();
                        }
                    }
                });
    }
    
    /**
      * Callback function for array edition
      *
      * @param string k
      * @param string value
      * @return void 
      * @author Damian Kęska
      */
    
    function conftool_array(k, value)
    {
        $('#value_'+k).val(value);
    }
    
    function addRecord()
    {
        key = $("#add_key").val();
        value = $("#add_value").val();
        type = $("#add_type").val();
        section = $("#add_section").val();
        
        panthera.jsonPOST({ url: '{$AJAX_URL}?display=conftool&cat=admin&action=add', data: 'key='+key+'&value='+value+'&section='+section+'&type='+type, messageBox: 'w2ui', spinner: spinner, success: function (response) {
                if (response.status == "success")
                {
                   navigateTo("{$AJAX_URL}?display=conftool&cat=admin");
                }
            }
        });
    }
    
    function editArray(keyID)
    {
        createPopup('?display=_popup_jsonedit&cat=admin&popup=True&input='+Base64.encode($('#value_'+keyID).val())+'&output=serialize&callback=conftool_array&callback_arg='+keyID, 1024, 550);
    }
    
</script>


{include="ui.titlebar"}
<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
    <div class="separatorHorizontal"></div>
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Create new key', 'conftool')"}" onclick="panthera.popup.toggle('element:#newEntry')">
    </div>
</div>


<!-- Add variable popup -->
<div id="newEntry" style="display: none;">
    <table class="formTable" style="margin: 0 auto;">
        <thead>
            <tr>
                <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Creating new key', 'conftool')"}</p>
                </td>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="3" style="padding-top: 35px;">
                    <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left;">
                    <input type="button" value="{function="localize('Create', 'users')"}" onclick="addRecord();" style="float: right;">
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <th>{function="localize('Key', 'conftool')"}:</th>
                <td><input type="text" id="add_key"style="width: 80%;"></td>
            </tr>
            <tr>
                <th>{function="localize('Value', 'conftool')"}:</th>
                <td><input type="text" id="add_value" style="width: 80%;"></td>
            </tr>
            <tr>
                <th>{function="localize('Section', 'conftool')"}:</th>
                <td><input type="text" id="add_section" style="width: 80%;"></td>
            </tr>
            <tr>
                <th>{function="localize('Type', 'conftool')"}: </th>
                <td>
                    <select id="add_type">
                        <option value="bool">bool</option>
                        <option value="int">int</option>
                        <option value="string" selected>string</option>
                        <option value="int">array</option>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>


<!-- Ajax content -->
<div class="ajax-content" style="text-align: center;">
    <table style="display: inline-block;">
        <thead>
            <tr>
                <th style="width: 250px;">{function="localize('Key')"}</th>
                <th>{function="localize('Value')"}</th>
                <th colspan="2">{function="localize('Section', 'conftool')"}</th>
            </tr>
        </thead>
        <tbody>
            {loop="$a"}
            {$escapedKey=str_replace('.', '_-_', $key)}
            <tr id="tr_{$escapedKey}">
                <td>
                    <small>{$value[0]}</small> &nbsp;<b>{if="isset($value[2])"}{$value[2]}{else}{$key}{/if}</b>
                </td>
                <td>
                    {if="$value[0] == 'bool'"}
                    <select id="value_{$escapedKey}" style="width: 300px;">
                        <option value="0">{function="localize('No')"}</option>
                        <option value="1"{if="$value[1] == '1'"} selected{/if}>{function="localize('Yes')"}</option>
                    </select>
                    {else}
                    {if="$value[0] == 'int'"}
                    {$type = "number"}
                    {else}
                    {$type = "text"}
                    {/if}
                    {if="$value[0] == 'array'"}
                    <input type="{$type}" value='{function="htmlspecialchars($value[1], ENT_QUOTES)"}' id="value_{$escapedKey}" readonly style="width: 200px;">
                    <input type="button" value="{function="localize('Edit')"}" style="width: 49px;" onclick="editArray('{$escapedKey}')">
                    {else}
                    <input type="{$type}" value='{function="htmlspecialchars($value[1], ENT_QUOTES)"}' id="value_{$escapedKey}" style="width: 305px;">
                    {/if}
                    {/if}
                </td>
                <td><input type="text" value="{$value['section']}" id="section_{$escapedKey}" style="width: 95%;"></td>
                <td>
                    <a href="#" onclick="saveVariable('{$escapedKey}');"><img src="{$PANTHERA_URL}/images/admin/ui/save.png" style="max-height: 22px;" alt="{function="localize('Save')"}"></a>
                    <a href="#" onclick="removeKey('{$escapedKey}');"><img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}"></a>
                    <span style="font-color: red;">
                        <div id="errmsg_{$escapedKey}" style="display: none;"></div>
                    </span>
                </td>
            </tr>
            {/loop}    
        </tbody>
    </table>
</div>
