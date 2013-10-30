{$titleBarInclude='langtool.titlebar'}
{include="ui.titlebar"}

<div id="topContent" style="min-height: 50px;">
    <div class="searchBarButtonArea">
        <input type="button" value="{function="localize('Add language', 'langtool')"}" onclick="panthera.popup.toggle('element:#newLanguage')">
    </div>
</div>


<!-- New language popup -->

<div id="newLanguage" style="display: none;">
    
    <script type="text/javascript">
    $(document).ready(function () {
        /**
          * Adding new language
          *
          * @author Damian Kęska
          */
    
        $('#createNewLanguage').submit(function () {
            
            panthera.jsonPOST({ data: '#createNewLanguage', type: 'POST', async: true, success: function (response) {
                    if (response.status == "success")
                        navigateTo('?display=langtool&cat=admin');    
                }
            });
            
            return false;
        })
    });
    </script>
    
    <form action="?display=langtool&cat=admin&action=createNewLanguage" method="POST" id="createNewLanguage">
         
         <table class="formTable" style="margin: 0 auto; margin-bottom: 30px;">
            
            <thead>
                 <tr>
                     <td colspan="2" class="formTableHeader" style="padding-top: 0px; padding-bottom: 30px;">
                         <p style="color: #e5ebef; padding: 0px; margin: 0px; margin-left: 30px;">{function="localize('Create new language', 'langtool')"}</p>
                     </td>
                 </tr>
            </thead>
             
            <tfoot>
                <tr>
                    <td colspan="3" style="padding-top: 35px;">
                        <input type="button" value="{function="localize('Cancel')"}" onclick="panthera.popup.close()" style="float: left; margin-left: 30px;">
                        <input type="submit" value="{function="localize('Add', 'users')"}" style="float: right;">
                    </td>
                </tr>
            </tfoot>

            <tbody>
                <tr>
                    <th>{function="localize('Name', 'langtool')"}</th>
                    <th><input type="text" name="languageName"></th>
                </tr>
            </tbody>
         </table>
    </form>
</div>
    
<div id="popupOverlay" style="text-align: center; padding-top: 20px; padding-bottom: 0px;"></div>

<!-- Ajax content -->

<div class="ajax-content" style="text-align: center;">

      <table style="width: 100%;">
            <thead>
                <tr>
                    <th colspan="2">{function="localize('Locale', 'langtool')"}</th>
                    <th>{function="localize('Path', 'langtool')"}</th>
                </tr>
            </thead>


            <tbody class="hovered">
              {loop="$locales"}
                <tr>
                    <td style="width: 30px;"><img src="{$value.icon}"></td>
                    <td><a href="#" onclick="navigateTo('?display=langtool&cat=admin&action=domains&locale={$key}');">{$key}</a></td>
                    <td>{$value.place}/{$key}</td>
                </tr>
              {/loop}
            </tbody>
            
      </table>
</div>
