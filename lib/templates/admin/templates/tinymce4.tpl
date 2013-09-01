<script type="text/javascript" src="{$PANTHERA_URL}/js/tinymce4/tinymce.min.js"></script>
<script type="text/javascript">
    console.log('Init mce.tpl');
    
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
        toolbar: '{$mceSettings['toolbar']}',
        theme: '{$mceSettings['theme']}',
        skin: '{$mceSettings['skin']}',
        content_css: '{$mceSettings['css']}'
    }
    
    function mceInit(id)
    {
        tinyMCE.settings = mceSettings;
        tinyMCE.execCommand('mceRemoveControl',true, id);
        //tinyMCE.execCommand('mceAddControl', true, id);
        editor = new tinymce.Editor(id, {}, tinymce.EditorManager);
        
        editor.on('init', function () {
            mcePantheraSetup(editor);
            
            if (initEditor != undefined)
            {
                initEditor();
            }
        });
        editor.render();
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
