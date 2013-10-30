<!-- permissions popup -->
{if="$permissions.admin"}
<a href="#" onclick="panthera.popup.toggle('?display=acl&cat=admin&popup=true&name=langtool_management,langtool_locale_{$locale}');" style="align: right;>
    <img src="{$PANTHERA_URL}/images/admin/pantheraUI/transparent.png" class="pantheraIcon icon-Users" alt="{function="localize('Manage permissions')"}">
</a>
{/if}
