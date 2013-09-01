<script type="text/javascript" src="{$PANTHERA_URL}/js/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    function mcePantheraInsertFile(link, mime, type, directory, id, description, author)
    {
        if (type == "image")
            tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent()+' <img src="'+link+'">');
        else
            tinyMCE.activeEditor.setContent(tinyMCE.activeEditor.getContent()+' <a href="'+link+'">'+link+'</a>');
    }

    function stripslashes(str) {
        str=str.replace(/\\'/g,'\'');
        str=str.replace(/\\"/g,'"');
        str=str.replace(/\\0/g,'\0');
        str=str.replace(/\\\\/g,'\\');
        return str;
    }
    
    function mceSave(id)
    {
        tinyMCE.get(id).save();
    }


    function mcePantheraSetup(ed)
    {
        // Add a custom button
        ed.addButton('pantheraUpload', { title : '{function="localize('Upload file', 'mce')"}', image : '{$PANTHERA_URL}/images/file-explorer.png', onclick : function() {
                ed.focus();
                createPopup('{$AJAX_URL}?display=upload&cat=admin&popup=true&callback=mcePantheraInsertFile', 1024, 'upload_popup');
                tinyMCE.execInstanceCommand(ed,"mceInsertContent",false,'Test');
            }
        });
    }    
    
    var mceSettings = {
        init_instance_callback: initEditor,
        mode : "textareas",
            theme : "{$mceSettings['theme']}",
            skin : "{$mceSettings['skin']}",
            plugins : "{$mceSettings['plugins']}", 
                    
            // Theme options - button# indicated the row# only
            theme_advanced_buttons1 :"{$mceSettings['tollbar1']}",
            theme_advanced_buttons2 : "{$mceSettings['tollbar2']}",      
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "top",
            theme_advanced_statusbar_location : "bottom",
            content_css : "{$mceSettings['css']}",
            theme_advanced_resizing : {$mceSettings['resizable']|intval},
            setup: function (ed) { mcePantheraSetup(ed); }
    }
    
    function mceInit(id)
    {
        tinyMCE.settings = mceSettings;
        tinymce.execCommand('mceRemoveControl',true, id);
        tinyMCE.execCommand('mceAddControl', true, id);
    }

    function mceSetContent(id, html)
    {
        tinyMCE.get(id).setContent(html);
    }
    
    function mceGetContent(id)
    {
        return tinyMCE.get(id).getContent();
    }

    function mceFocus(id)
    {
        tinyMCE.execCommand('mceFocus', true, id);
    }
</script>
