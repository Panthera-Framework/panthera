{$site_header}
<script type="text/javascript">

function initEditor() {}

// array with selected comments
var selected = new Array;

/**
  * Delete comment from database
  *
  * @author Mateusz Warzyński
  */

function deleteComment(id)
{
    if (id == 0) {
        id = transformArrayToString(selected);
        var row = false;
    }
    
    panthera.confirmBox.create('{function="localize('Are you sure you want delete these comments?', 'comments')"}', function (responseText) {
        if (responseText == 'Yes')
        {
            panthera.jsonPOST({ url: '{$AJAX_URL}?display=comments&cat=admin&action=deleteComment', data: 'ids='+id, success: function (response) {
                    if (response.status == "success")
                    {
                        if (row == false) {
                            navigateTo(window.location.href);
                        } else {
                            jQuery('#comment_row_'+id).remove();
                        }
                    }
                }
        }); 
        }
    });
    
}


/**
  * Hold comments
  *
  * @author Mateusz Warzyński
  */

function holdComment(id)
{
    if (id == 0) {
        id = transformArrayToString(selected);
        var row = false;
    }
    
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=comments&cat=admin&action=holdComment', data: 'ids='+id, success: function (response) {
        if (response.status == "success")
            navigateTo(window.location.href);
     }
    });
}


/**
  * Select comment, massive management
  *
  * @author Mateusz Warzyński
  */

function selectComment(id)
{
    var color = $("#comment_row_"+id).css("background-color");
       
    if (color != "rgb(61, 73, 87)") {
        $("#comment_row_"+id).css("background-color", "#3d4957");
        $("#comment_row_"+id).css("color", "white");
        $("#comment_row_"+id).attr("first-color", color);
        selected.push(id)
    } else {
        var first_color = $("#comment_row_"+id).attr("first-color");
        $("#comment_row_"+id).css("color", "black");
        $("#comment_row_"+id).css("background-color", first_color);
        removeFromArrayByValue(selected, id);
    }

       
    if (selected.length == 0)
        $("#massive_buttons").slideUp();
    else
        $("#massive_buttons").slideDown();
}


/**
  * Remove value from array
  *
  * @author Mateusz Warzyński
  */

function removeFromArrayByValue(array, value) {
    for(var i=0; i<array.length; i++) {

        if(array[i] == value) {
            array.splice(i, 1);
            break;
        }

    }
}

/**
  * Transform array to string ([0, 1] -> "0,1")
  *
  * @author Mateusz Warzyński
  */

function transformArrayToString(array) {
    var returnString = array[0];
    
    for(var i=1; i<array.length; i++) {
        returnString = returnString+','+array[i];
    }
    
    return returnString;
}




/* Edit comment code */

function editComment(id)
{
    panthera.jsonPOST({ url: '{$AJAX_URL}?display=comments&cat=admin&action=getComment&id='+id, data: '', messageBox: false, success: function (response) {
            if (response.status == "success") {
                panthera.popup.create('element:#editCommentPopup');
                mceInit('edit_comment_content');
            
                $('#edit_comment_id').val(response.id);
                $('#edit_comment_group').val(response.group);
                $('#edit_comment_objectid').val(response.objectid);
                $('#edit_comment_author').text(response.author_login);
                $('#edit_comment_posted').text(response.posted);
                
                if (response.allowed == 0)
                    $('#edit_comment_allowed').attr('checked', false);
                else
                    $('#edit_comment_allowed').attr('checked', true);

                // init mce editor
                mceFocus("edit_comment_content");
                mceSetContent('edit_comment_content', response.content);
            }
        }
    });

    return false;

} 

</script>
{function="uiMce::display()"}

{include="ui.titlebar"}

<div id="topContent">
    {$uiSearchbarName="uiTop"}
    {include="ui.searchbar"}
</div>

<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Editing a comment -->
<div style="display: none;" id="editCommentPopup">
    <form action="{$AJAX_URL}?display=comments&cat=admin&action=editComment" method="POST" id="edit_comment_form">
        <table class="formTable" style="margin: 0 auto; margin-bottom: 25px; width: 85%;">
            <thead>
                <tr>
                    <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                        <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;" id="edit_comment_operation">{function="localize('Editing a comment', 'comments')"}</p>
                    </td>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <th colspan="2" class="textEditor">
                        <textarea name="edit_comment_content" id="edit_comment_content" style="width: 100%; height: 400px;"></textarea>
                        <input type="hidden" id="edit_comment_id" name="edit_comment_id">
                    </th>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Group', 'comments')"}:</b>
                        <br><small>"custompage", "blogpost"</small>
                    </th>
                    <td><input type="text" name="edit_comment_group" id="edit_comment_group"></td>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Object ID', 'comments')"}:</b>
                        <br><small>{function="localize('eg. custompage id, or quick message id', 'comments')"}</small>
                    </th>
                    <td><input type="text" name="edit_comment_objectid" id="edit_comment_objectid"></td>
                </tr>
                <tr>
                    <th style="font-weight: 100;"><b>{function="localize('Allowed', 'comments')"}:</b><br><small>{function="localize('If checked, this comment will be published on your website', 'comments')"}</small></th>
                    <td style="border-right: 0px;"><input type="checkbox" name="edit_comment_allowed" id="edit_comment_allowed" value="1"></td>
                </tr>
                <tr>
                    <th>{function="localize('Author', 'comments')"}:</th>
                    <td><p id="edit_comment_author" style="color: white;"></p></td>
                </tr>
                <tr>
                    <th>{function="localize('Posted', 'comments')"}:</th>
                    <td><p id="edit_comment_posted" style="color: white;"></p></td>
                </tr>
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="2" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Save')"}" style="float: right; margin-right: 30px;">
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
    
    <style>
    .textEditor td {padding-right: 0px !important;}
    </style>
    
    <script type="text/javascript">
    $("#edit_comment_form").submit(function () {
       
       panthera.jsonPOST({ data: '#edit_comment_form', mce: 'tinymce_all', success: function (response) {
                if (response.status == "success")
                {
                    navigateTo(window.location.href);
                }
            }
        });
        
        return false;
    });
    </script>
</div>

<div class="ajax-content" style="text-align: center;">
    <div style="display: inline-block; margin: 0 auto;">
        <table style="min-width: 400px;">
            <thead>
                <tr>
                    <th></th>
                    <th>{function="localize('Content', 'comments')"}</th>
                    <th>{function="localize('Group', 'comments')"}</th>
                    <th>{function="localize('Created', 'comments')"}</th>
                    <th>{function="localize('Modified', 'comments')"}</th>
                    <th>{function="localize('Status', 'comments')"}</th>
                    <th>{function="localize('Options', 'comments')"}</th>
                </tr>
            </thead>
            
            <tbody>
                {if="count($commentsList) > 0"}
                {loop="$commentsList"}
                <tr id="comment_row_{$value['id']}">
                    <td><input id="checkbox_{$value['id']}" value="" type="checkbox" onclick="selectComment('{$value['id']}');"></td>
                    <td><small>{$value['content']|strCut}</small></td>
                    <td>{$value['group']}, {$value['object_id']}</td>
                    <td>{$value['posted']} {function="localize('by', 'comments')"} {$value['author_login']}</td>
                    <td>{if="$value['posted'] == $value['modified']"}{function="localize('without changes', 'comments')"}{else}{$value['modified']}{/if}</td>
                    <td>{if="$value['allowed'] == 1"}<p style="color: green;">{function="localize('Allowed', 'comments')"}</p> {else} <p style="color: red;">{function="localize('Blocked', 'comments')"}</p> {/if}</td>
                    <td style="padding: 10px; min-width: 75px;">
                        <a href="#" onclick="holdComment('{$value['id']}')"><img src="{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png" style="max-height: 22px;" title="{function="localize('Hold', 'comments')"}"></a>
                        <a href="#" onclick="editComment('{$value['id']}');"><img src="{$PANTHERA_URL}/images/admin/menu/mce.png" style="max-height: 22px;" title="{function="localize('Edit', 'messages')"}"></a>
                        <a href="#" onclick="deleteComment('{$value['id']}')"><img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" title="{function="localize('Remove', 'messages')"}"></a>
                    </td>
                </tr>
                {/loop}
                <tr id="massive_buttons" style="display: none; background-color: rgb(61, 73, 87); color: white;">
                    <td><input id="checkbox_all" value="" type="checkbox" onclick="alert('Select All');"></td>
                    <td style="border-right: 0px;">{function="localize('Buttons for selected items:', 'comments')"}</td>
                    <td colspan="5" style="text-align: right; border-left: 0px;">
                        <a href="#" onclick="holdComment(0)"><img src="{$PANTHERA_URL}/images/admin/menu/Actions-transform-move-icon.png" style="max-height: 22px;" title="{function="localize('Hold', 'comments')"}"></a>
                        <a href="#" onclick="deleteComment(0)"><img src="{$PANTHERA_URL}/images/admin/ui/delete.png" style="max-height: 22px;" title="{function="localize('Remove', 'messages')"}"></a>
                    </td>
                </tr>
                {else}
                <tr>
                    <td colspan="7" style="text-align: center;">{function="localize('No any comments found', 'comments')"}</td>
                </tr>
                {/if}
            </tbody>
        </table>
        
        {if="count($commentsList) > 0"}
            <div style="position: relative; text-align: left;" class="pager">{$uiPagerName="comments"}{include="ui.pager"}</div>
        {/if}
    </div>
</div>
