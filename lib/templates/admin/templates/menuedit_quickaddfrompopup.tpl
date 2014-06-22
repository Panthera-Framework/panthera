
    <form id="add_item_form" method="POST" action="?display=menuedit&cat=admin&action=createItem">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Adding item', 'menuedit')"}</p>
                     </td>
                 </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Add', 'menuedit')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <th>{function="localize('Title', 'menuedit')"}:</th>
                    <th><input type="text" name="item_title" value="{$title}" style="width: 99%;"></th>
                </tr>
                
                {if="$routeData"}
                <tr>
                	<th>{function="localize('Link', 'menuedit')"}:</th>
                	<th>
                		<select name="routeEncoded">
                		<option value=""></option>
                {loop="$routeData"}
                	<option value="{$value|serialize|base64_encode}">{$value.title}</option>
                {/loop}
                		</select>
                	</th>
                </tr>
                {else}
                
                <tr>
                    <th>{function="localize('Link', 'menuedit')"}:</th>
                    <th><input type="text" name="item_link" value="{$link}" style="width: 99%;"></th>
                </tr>
                {/if}
                
                <tr>
                    <th>{function="localize('Language', 'menuedit')"}:</th>
                    <th>
                        <select name="item_language">
                         {loop="$languages"}
                            <option value="{$key}" {if="$currentLanguage == $key"} selected {/if}>{$key}</option>
                         {/loop}
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('Category', 'menuedit')"}:</th>
                    <th>
                        <select name="cat_type">
                        {loop="$categories"}
                            <option value="{$key}">{$value}</option>
                        {/loop}
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th>{function="localize('SEO friendly name', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small>:</th>
                    <th><input type="text" name="item_url_id" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Tooltip', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small>:</th>
                    <th><input type="text" name="item_tooltip" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Icon', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small>:</th>
                    <th><input type="text" name="item_icon" style="width: 99%;"></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Attributes', 'menuedit')"} <small>({function="localize('Optional', 'menuedit')"})</small>:</th>
                    <th><input type="text" name="item_attributes" style="width: 99%;"></th>
                </tr>
            </tbody>
        </table>
    </form>
    
    <script type="text/javascript">
    
    /**
      * Add item to category
      *
      * @author Mateusz Warzyński
      */
    
    $('#add_item_form').submit(function () {
        panthera.jsonPOST({ data: '#add_item_form', messageBox: 'w2ui', success: function (response) {
                if (response.status == "success")
                    closePopup();
            }
        });
    
        return false;
    
    });
    </script>