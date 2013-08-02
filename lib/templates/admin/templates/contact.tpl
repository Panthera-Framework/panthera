{$site_header}
<script type="text/javascript">
var map = "";

function gmapsCallback ()
{
    var mapOptions = {
        zoom: {$map_zoom},
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        center: new google.maps.LatLng({$map_x}, {$map_y})
    };
                
    map.createMap("map", mapOptions);
}

jQuery(document).ready(function() {
    /**
      * Init MCE Editor
      *
      * @author Mateusz Warzyński
      */

    {include="mce.tpl"}

    mceInit('address_text');

    //setTimeout(initEditor, 500);

    if ($('#map').length > 0)
    {
        {if="!isset($map_zoom)"}
        {$map_zoom = 10}
        {$map_x = 0}
        {$map_y = 0}
        {/if}

        map = new panthera.googleMap('gmapsCallback');
        
        if (typeof google !== 'undefined')
            gmapsCallback();
        
        /**
          * Find place from input
          *
          * @author Mateusz Warzyński
          */

        $('#map_form').submit(function () {
            place = $('#map_searchbox').val();

            if (place != "")
            {
                map.getLocation(place);
                $('#map_bounds').val(JSON.stringify({ "bounds":map.map.getBounds(), "zoom": map.map.getZoom(), "center": map.map.getCenter() }));
            }

            return false;
        });
        
         /**
          * Save map bounds
          *
          * @author Mateusz Warzyński
          */

        $('#contact_form').submit(function () {
            $('#map_bounds').val(JSON.stringify({ "bounds":map.map.getBounds(), "zoom": map.map.getZoom(), "center": map.map.getCenter() }));
        });
    }

    /**
      * Save contact information
      *
      * @author Mateusz Warzyński
      */

    $('#contact_form').submit(function () {
        panthera.jsonPOST({ data: '#contact_form', mce: 'tinymce_all', messageBox: 'userinfoBox'});

        return false;

    });
    
    panthera.forms.checkboxToggleLayer({ input: '#oneContactCheckbox', layer: '#contactLanguage', reversed: true });
});
</script>

        <div class="titlebar">{function="localize('Contact', 'contactpage')"} - {function="localize('Street adress, phone number, location etc.', 'contactpage')"} ({$selected_language}){include="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>
        
        <div class="grid-1" id="languagesList" style="position: relative;">
          <div class="title-grid">{function="localize('Contact in other languages', 'contactpage')"}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{function="localize('Contact informations can be provided in many diffirent site localisations', 'contactpage')"}</small></td>
                    </tr>
                </tfoot>
            
                <tbody>
                    {loop="$languages"}
                        <tr>
                            <td style="padding: 10px; border-right: 0px; width: 1%;"><a href="#{$key}" onclick="navigateTo('?display=contact&cat=admin&language={$key}');">{$key}</a></td>
                            <td style="width: 60px; padding: 10px; border-right: 0px;"></td>
                        </tr>
                    {/loop}
                </tbody>
            </table>
         </div>
       </div>

        {if="!$skip_map"}
        <div class="grid-1">
               <div class="title-grid">{function="localize('Map', 'contactpage')"}</div>

               <div class="content-gird">
                    <form action="?display=contact&cat=admin&action=save" method="GET" id="map_form">{function="localize('Search', 'contactpage')"}: <input type="text" value="" id="map_searchbox" style="width:300px;height:25px; font-size:15px;"></form>
                    <div id="map" style="width: 100%; height: 300px; margin-top: 10px;"></div>
               </div>
       </div>
       {/if}

       <br>

        <form action="?display=contact&cat=admin&action=save" method="GET" id="contact_form">
         <div class="grid-1">
	      	 <div class="title-grid">{function="localize('Contact page text', 'contactpage')"}</div>
	      	 <div class="content-gird" style="padding: 0px;">
                 <textarea id="address_text" name="address_text" style="width: 100%; height: 550px;">{$adress_text}</textarea><br><br>
            </div>
		 </div>

		 <input type="hidden" name="map_bounds" id="map_bounds">

         <div class="grid-1">
               <div class="title-grid">{function="localize('Options', 'messages')"}</div>

               <div class="content-gird" style="padding: 0px;">
                    <table class="gridTable" style="border: 0px;">
                        <tbody>
                            <tr>
                                <td style="border-right: 0px; width: 20%;">{function="localize('E-mail address where all messages from form will to', 'contactpage')"}: </td>
                                <td style="border-right: 0px;"><input type="text" name="contact_email" value="{$contact_mail}"></td>
                            </tr>
                            
                            <tr>
                                <td style="border-right: 0px;">{function="localize('One contact page for all languages', 'contactpage')"}: </td>
                                <td style="border-right: 0px;"><input type="checkbox" value="1" name="all_langs"{if="$oneContactPage == True} checked{/if"} id="oneContactCheckbox"></td>
                            </tr>
                            
                            <tr{if="$oneContactPage == True} style='display: none;'{/if"} id="contactLanguage">
                                <td style="border-right: 0px; border-bottom: 0px;">{function="localize('Save this contact page in', 'contactpage')"}: </td>
                                <td style="border-right: 0px; border-bottom: 0px;">
                                    <select name="save_as_language">
                                        {loop="$languages"}
                                            <option value="{$key}"{if="$key == $selected_language"} selected{/if}>{$key}</option>
                                        {/loop}
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style="border-bottom: 0px; border-right: 0px;">&nbsp;</td>
                                <td style="border-bottom: 0px; border-right: 0px;">
                                    <div style="float: right;"><input type="button" value="{function="localize('Manage permissions', 'messages')"}" id="permissionsButton" onclick="createPopup('_ajax.php?display=acl&cat=admin&popup=true&name=can_edit_contact', 1024, 'upload_popup');"> <input type="submit" value="{function="localize('Save')"}"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>
        </form>

