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
        if (callback_{$callback} != undefined)
        {
            callback_{$callback}(Base64.decode(value));
        }
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
    panthera.jsonPOST({url: '?display=editor_drafts&cat=admin&id='+id, data: 'action=removeDraft', success: function (response) {
            if (response.status == 'success')
            {
                $('#draft_tr_'+id).remove();
            }
        }
    });
}
</script>

{if="!$callback"}
{include="ui.titlebar"}
{/if}

<div style="display: inline-block;">
<table class="formTable" style="width: 70%; margin: 0 auto;">
        <thead>
            <tr>
                <td class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                    <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Saved drafts and sent messages', 'editor')"}</p>
                </td>
            </tr>
        </thead>
        
        <tbody>
            {loop="$drafts"}
            <tr id="draft_tr_{$value.id}" style="background: transparent;">
                <input type="hidden" id="draft_{$value.id}" value="{$value.content|base64_encode}">
                <th style="font-weight: 100;">
                    <a href="#" onclick="selectDraft({$value.id})">"{$value.content|strip_tags|strcut:180}" {if="$value.directory == 'drafts'"}<i><b>({function="localize('saved draft', 'editor')"})</b></i>{/if}</a>
                    <br><small><i>{function="slocalize('Created %s by %s', 'editor', $value['date'], $value['user'])"}</i></small>
                </th>
                
                <td style="width: 64px;">
                    <a href="#" onclick="panthera.popup.create('?display=editor_drafts&cat=admin&id={$value.id}&callback={$callback}')">
                        <img src="{$PANTHERA_URL}/images/admin/ui/edit.png" style="max-height: 22px;" alt="{function="localize('Edit', 'editor')"}">
                    </a>
                    
                    <a href="#" onclick="removeDraft({$value.id})">
                        <img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" alt="{function="localize('Remove', 'editor')"}">
                    </a>
                </td>
            </tr>
            {/loop}
            
            <tr>
                <td colspan="2" style="text-align: right;"><input type="button" value="{function="localize('Close')"}" onclick="panthera.popup.close()"></td>
            </tr>
        </tbody>
</table>
</div>
