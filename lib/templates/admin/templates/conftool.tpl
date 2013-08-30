{$site_header}
<script>
$('.ajax_link').click(function (event) { event.preventDefault(); navigateTo(jQuery(this).attr('href')); return false;});

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
    w2confirm('{function="localize('Are you sure you want to delete this key?', 'conftool')"}', function callbackBtn(btn) { 
        if (btn == "Yes")
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=conftool&cat=admin&action=remove', data: 'key='+id, messageBox: 'w2ui', spinner: spinner, success: function (response) {
                    if (response.status == "success")
                    {
                       $('#tr_'+jid).remove();
                    }
                }
            });
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
        
        {$uiSearchbarName="uiTop"}
        {include="ui.searchbar"}

        <div class="grid-1" style="position: relative;" id="conftoolTable">
         <table class="gridTable">

            <thead>
                <tr>
                    <th scope="col" class="rounded-company" style="width: 250px;">{function="localize('Key')"}</th>
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
                            <select id="value_{$escapedKey}" style="width: 95%;">
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
                            <input type="{$type}" value='{function="htmlspecialchars($value[1], ENT_QUOTES)"}' id="value_{$escapedKey}" readonly style="width: 80%;">
                            <input type="button" value="{function="localize('Edit')"}" style="width: 49px;" onclick="editArray('{$escapedKey}')">
                            {else}
                            <input type="{$type}" value='{function="htmlspecialchars($value[1], ENT_QUOTES)"}' id="value_{$escapedKey}" style="width: 95%;">
                            {/if}
                        {/if}
                        
                    </td>
                    
                    <td>
                        <input type="text" value="{$value['section']}" id="section_{$escapedKey}" style="width: 95%;">
                    </td>
                    
                    <td>
                    	<a href="#" onclick="saveVariable('{$escapedKey}');">
                        	<img src="{$PANTHERA_URL}/images/admin/ui/save.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                    	</a>
                        <a href="#" onclick="removeKey('{$escapedKey}');">
                        	<img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
                    	</a>
                        <span style="font-color: red;"><div id="errmsg_{$escapedKey}" style="display: none;"></div></span>
                    </td>
                </tr>
              {/loop}
              	<tr id="tr_add">
                    <td>

                        <small>
                        	<select id="add_type">
                        		<option value="bool">bool</option>
                        		<option value="int">int</option>
                        		<option value="string" selected>string</option>
                        		<option value="int">array</option>
                        	</select>
                        </small> 
                        
                        &nbsp;<b><input type="text" id="add_key" placeholder="{function="localize('Key')"}" style="width: 70%;"></b>

                    </td>
                    <td>
                        <input type="text" id="add_value" placeholder="{function="localize('Value')"}" style="width: 95%;">
                    </td>
                    
                    <td>
                        <input type="text" id="add_section" placeholder="{function="localize('Section', 'conftool')"}" style="width: 95%;">
                    </td>
                    
                    <td>
                    	<a onclick="addRecord();" style="cursor: pointer;">
                    		<img src="{$PANTHERA_URL}/images/admin/list-add.png" style="height: 15px;">
                    	</a>
                    </td>
                </tr>
            </tbody>
           </table>

      </div>
</article>

