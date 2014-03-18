{*} This would happen if current user don't have enought rights to view this category so it shouldn't be listed{/*}
{if="!$z.item"}
{continue}
{/if}

{if="isset($lastID)"}
    {if="$lastID -> type_name != $z.item->parent"}
        {$depth=0}
    {/if}
{/if}

{$lastID=$z.item}

{$depth=$depth+1}
<tr id="category_{$value.item.id}">
    <td>{if="$depth > 1"}{loop="forRange($depth)"}--{/loop}>&nbsp;&nbsp;{/if}
        <a href="?display=menuedit&cat=admin&action=getCategory&category={$z.item->type_name}" class="ajax_link">{$z.item->title}</a></td>
    <td>
        <a href="?display=menuedit&cat=admin&action=getCategory&category={$z.item->type_name}" class="ajax_link">{$z.item->type_name}</a>
    </td>
    <td><a href="?display=menuedit&cat=admin&action=getCategory&category={$z.item->type_name}" class="ajax_link">{$z.item->description}</a></td>
    <td>{$z.item->elements}</td>
    <td>
        <a href="#" onclick="removeMenuCategory('{$z.item->type_name}');">
            <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove')"}">
        </a>
    </td>
</tr>

{loop="$z['subcategories']"}
{$z=$value}
{include="menuedit.categoryrow.tpl"}
{/loop}
