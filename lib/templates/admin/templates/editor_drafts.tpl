<script type="text/javascript">

/**
  * Put draft in to editor using a defined callback
  *
  * @param int id
  * @author Damian Kęska
  */

function selectDraft(id)
{
    value = $('#draft_'+id).val();
    
    if (typeof callback_{$callback} == "function")
    {
        callback_{$callback}(Base64.decode(value));
    }
}

/**
  * Remove a draft
  *
  * @param int id
  * @author Damian Kęska
  */

function removeDraft(id)
{
    w2confirm('{function="localize('Are you sure?')"}', function (response) {
        if (response == 'Yes')
        {
            panthera.jsonPOST({url: '?display=editor_drafts&cat=admin&id='+id, data: 'action=removeDraft', success: function (response) {
                    if (response.status == 'success')
                    {
                        $('#draft_tr_'+id).remove();
                    }
                }
            });
        }
    });
}
</script>

{if="!$callback"}
{include="ui.titlebar"}
{/if}

<div class="grid-1">
    <table class="gridTable">
    
        <thead>
            <tr>
                <th colspan="2">{function="localize('Saved drafts and sent messages', 'editor')"}</th>
            </tr>
        </thead>
        
        <tbody>
            {loop="$drafts"}
            <tr id="draft_tr_{$value.id}">
                <input type="hidden" id="draft_{$value.id}" value="{$value.content|base64_encode}">
                <td><a href="#" onclick="selectDraft({$value.id})">"{$value.content|strip_tags|strcut:360}" {if="$value.directory == 'drafts'"}<i><b>({function="localize('saved draft', 'editor')"})</b></i>{/if}</a>
                    <br><small><i>{function="slocalize('Created %s by %s', 'editor', $value['date'], $value['user'])"}</i></small></td>
                <td style="width: 64px;">
                    <a href="?display=editor_drafts&cat=admin&id={$value.id}" class="ajax_link">
                        <img src="{$PANTHERA_URL}/images/admin/ui/edit.png" style="max-height: 22px;" alt="{function="localize('Edit', 'editor')"}">
                    </a>
                    
                    <a href="#" onclick="removeDraft({$value.id})">
                        <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Edit', 'editor')"}">
                    </a>
                </td>
            </tr>
            {/loop}
        </tbody>
    </table>
</div>
