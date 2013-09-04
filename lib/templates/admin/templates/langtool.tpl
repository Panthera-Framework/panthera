<script type="text/javascript">
$(document).ready(function () {
    /**
      * Adding new language
      *
      * @author Damian Kęska
      */

    $('#createNewLanguage').submit(function () {
        spinner = new panthera.ajaxLoader($('#newLanguageGrid'));
    
        panthera.jsonPOST({ data: '#createNewLanguage', type: 'POST', spinner: spinner, async: true, messageBox: 'w2ui', success: function (response) {
                if (response.status == "success")
                    navigateTo('?display=langtool&cat=admin');    
            }
        });
        
        return false;
    })
});
</script>
		
		{include="ui.titlebar"}<br>

        <table class="gridTable">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Locale', 'langtool')"}</th>
                    <th>{function="localize('Path', 'langtool')"}</th>
                </tr>
            </thead>


            <tbody>
              {loop="$locales"}
                <tr>
                    <td style="width: 30px;"><img src="{$value.icon}"></td>
                    <td><a href="#" onclick="navigateTo('?display=langtool&cat=admin&action=domains&locale={$key}');">{$key}</a></td>
                    <td>{$value.place}/{$key}</td>
                </tr>
              {/loop}
            </tbody>
            
            <tfoot>
                <tr>
                    <td colspan="3" class="rounded-foot-left"><em>Panthera - {function="localize('List of available languages', 'langtool')"}<input type="button" value="{function="localize('Locales management', 'langtool')"}" onclick="navigateTo('?display=locales&cat=admin');" style="float: right;"></em></td>
                </tr>
            </tfoot>
            
          </table>
          
        <br>
        
        <table class="gridTable" style="width: 50%; position: relative;" id="newLanguageGrid">
        <thead>
            <tr><th colspan="2"><b>{function="localize('Create new language', 'langtool')"}</b></th></tr>
        </thead>
        
        <tbody>
            <tr>
                <form action="?display=langtool&cat=admin&action=createNewLanguage" method="POST" id="createNewLanguage">
                    <td style="border-bottom: 0px;">{function="localize('Language name', 'langtool')"}<br><small>{function="localize('Single word, eg. polski, english, deutsh', 'langtool')"}</small></td>
                    <td style="border-bottom: 0px; border-right: 0px;"><input type="text" name="languageName"> <input type="submit" value=" {function="localize('Add', 'langtool')"} "></td>
                </form>
            </tr>
        </tbody>
        
    </table>
