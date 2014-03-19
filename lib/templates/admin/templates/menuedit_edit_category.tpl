{$inputPrefix=''}
{if="$action == 'edit'"}
    {$inputPrefix='_edit'}
{/if}

<script type="text/javascript">
    
    /**
      * Add menu category
      *
      * @author Mateusz Warzyński
      */
    
    $('#add_category_form{$inputPrefix}').submit(function () {
        panthera.jsonPOST({ data: '#add_category_form{$inputPrefix}', success: function (response) {
                if (response.status == "success")
                {
                    {if="$action == 'edit' and $ref == 'getCategory'"}
                    navigateTo('?display=menuedit&cat=admin&action=getCategory&category={$category}');
                    {else}
                    navigateTo('?display=menuedit&cat=admin');
                    {/if}
                }
            }
        });
    
        return false;
    });
    
    </script>

    <form id="add_category_form{$inputPrefix}" method="POST" action="?display=menuedit&cat=admin&action={if="$action == 'edit'"}saveCategory{else}createCategory{/if}">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">
                            {if="$action == 'edit'"}
                                {function="slocalize('Editing "%s"', 'menuedit', $object->title)"}
                            {else}
                                {function="localize('Create new category', 'menuedit')"}
                            {/if}
                         </p>
                     </td>
                 </tr>
            </thead>
            
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{if="$action == 'edit'"}{function="localize('Save')"}{else}{function="localize('Add', 'users')"}{/if}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
            
            <tbody>
                <tr>
                    <th>{function="localize('Title', 'menuedit')"}:</th>
                    <th><input type="text" name="category_title" id="category_title{$inputPrefix}" style="width: 99%;" {if="isset($object)"}value="{$object->title}"{/if}></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Description', 'menuedit')"}:</th>
                    <th><input type="text" name="category_description" id="category_description{$inputPrefix}" style="width: 99%;" {if="isset($object)"}value="{$object->description}"{/if}></th>
                </tr>
                
                <tr>
                    <th>{function="localize('Subcategory', 'menuedit')"} ({function="localize('Optional')"}):</th>
                    <th>
                        <select name="category_parent" id="category_parent{$inputPrefix}">
                            <option value="">----</option>
                        {loop="$categoriesSelectBox"}
                            <option value="{$key}" {if="isset($object)"}{if="$key == $object->parent"}selected{/if}{/if}>{$value}</option>
                        {/loop}
                        </select>
                    </th>
                </tr>
                
                <tr>
                    <th><small>{function="localize('ID', 'menuedit')"} ({function="localize('Optional')"}):</small></th>
                    <th><input type="text" name="category_type_name" id="category_type_name{$inputPrefix}" style="width: 99%;" {if="isset($object)"}value="{$object->type_name}"{/if}></th>
                </tr>
            </tbody>
        </table>
        
        {if="$action == 'edit'"}<input type="hidden" name="category_id" value="{$object->id}">{/if}
        <input type="hidden" name="category_elements" value="0">
    </form>