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
        ed.addButton('pantheraUpload', { title : '{"Upload file"|localize}', image : '{$PANTHERA_URL}/images/file-explorer.png', onclick : function() {
                ed.focus();
                createPopup('_ajax.php?display=upload&popup=true&callback=mcePantheraInsertFile', 1024, 'upload_popup');
                tinyMCE.execInstanceCommand(ed,"mceInsertContent",false,'Test');
            }
        });
    }    

    var mceSettings = {
        {$mce_init}
        mode : "textareas",
            theme : "advanced",
            skin : "thebigreason",
            plugins : "emotions,spellchecker,advhr,insertdatetime,preview", 
                    
            // Theme options - button# indicated the row# only
            theme_advanced_buttons1 :"bold,italic,underline,|,justifyleft,justifycenter,justifyright,fontselect,fontsizeselect,formatselect",
            theme_advanced_buttons2 : "outdent,indent,|,bullist,numlist,|,outdent,indent,link,image,|,code,|,forecolor,backcolor,sub,sup,|,charmap,|,pantheraUpload",      
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "top",
            theme_advanced_statusbar_location : "bottom",
            content_css : "{$site_template_css}",
            theme_advanced_resizing : true,
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

