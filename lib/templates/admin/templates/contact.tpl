
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript">

jQuery(document).ready(function($) {

    /**
      * Init MCE Editor
      *
      * @author Mateusz Warzyński
      */

    function initEditor()
    {
        mceSetContent('address_text', htmlspecialchars_decode("{$adress_text}"));
    }

    {$mce_init = "init_instance_callback: initEditor,"}
    {include file="mce.tpl"}

    mceInit('address_text');

    //setTimeout(initEditor, 500);

    if ($('#map').length > 0)
    {
        {if !isset($map_zoom)}
        {$map_zoom = 10}
        {$map_x = 0}
        {$map_y = 0}
        {/if}

        var mapOptions = {
           zoom: {$map_zoom},
           mapTypeId: google.maps.MapTypeId.ROADMAP,
           center: new google.maps.LatLng({$map_x}, {$map_y})
         };

        createMap("map", mapOptions);

        /**
          * Find place from input
          *
          * @author Mateusz Warzyński
          */

        $('#map_form').submit(function () {
            place = $('#map_searchbox').val();

            if (place != "")
            {
                getLocation(place);
                $('#map_bounds').val(JSON.stringify({ "bounds":map.getBounds(), "zoom": map.getZoom(), "center": map.getCenter() }));
            }

            return false;
        });
        
         /**
          * Save map bounds
          *
          * @author Mateusz Warzyński
          */

        $('#contact_form').submit(function () {
            $('#map_bounds').val(JSON.stringify({ "bounds":map.getBounds(), "zoom": map.getZoom(), "center": map.getCenter() }));
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
    
    panthera.forms.checkboxToggleLayer({ input: '#oneContactCheckbox', layer: '#oneContactPage', reversed: true });
});
</script>

        <div class="titlebar">{"Contact"|localize:contactpage} - {"Street adress, phone number, location etc."|localize:contactpage}{include file="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>
        
        <div class="grid-1" id="languagesList" style="position: relative;">
          <div class="title-grid">{"Contact in other languages"|localize:contactpage}<span></span></div>
          <div class="content-table-grid">
              <table class="insideGridTable">
                <tfoot>
                    <tr>
                        <td colspan="3"><small>{"Contact informations can be provided in many diffirent site localisations"|localize:contactpage}</small></td>
                    </tr>
                </tfoot>
            
                <tbody>
                    {foreach from=$languages key=k item=i}
                        <tr>
                            <td style="padding: 10px; border-right: 0px; width: 1%;"><a href="#{$k}" onclick="navigateTo('?display=contact&language={$k}');">{$k}</a></td>
                            <td style="width: 60px; padding: 10px; border-right: 0px;"></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
         </div>
       </div>

        {if !$skip_map}
        <div class="grid-1">
               <div class="title-grid">{"Map"|localize:contactpage}</div>

               <div class="content-gird">
                    <form action="?display=contact&action=save" method="GET" id="map_form">{"Search"|localize:contactpage}: <input type="text" value="" id="map_searchbox" style="width:300px;height:25px; font-size:15px;"></form>
                    <div id="map" style="width: 100%; height: 300px; margin-top: 10px;"></div>
               </div>
       </div>
       {/if}

       <br>

        <form action="?display=contact&action=save" method="GET" id="contact_form">
         <div class="grid-1">
	      	 <div class="title-grid">{"Contact page text"|localize:contactpage}</div>
	      	 <div class="content-gird" style="padding: 0px;">
                 <textarea id="address_text" name="address_text" style="width: 100%; height: 550px;"></textarea><br><br>
            </div>
		 </div>

		 <input type="hidden" name="map_bounds" id="map_bounds">

         <div class="grid-1">
               <div class="title-grid">{"Options"|localize:messages}</div>

               <div class="content-gird">
                    <table class="gridTable" style="border: 0px">
                        <tbody>
                            <tr>
                                <td style="border-right: 0px; width: 20%;">{"E-mail address where all messages from form will to"|localize:contactpage}: </td>
                                <td style="border-right: 0px;"><input type="text" name="contact_email" value="{$contact_mail}"></td>
                            </tr>
                            
                            <tr>
                                <td style="border-right: 0px;">{"One contact page for all languages"|localize:contactpage}: </td>
                                <td style="border-right: 0px;"><input type="checkbox" value="1" name="all_langs"{if $oneContactPage == True} checked{/if} id="oneContactCheckbox"></td>
                            </tr>
                            
                            <tr{if $oneContactPage == True} style="display: none;"{/if} id="oneContactPage">
                                <td style="border-right: 0px; border-bottom: 0px;">{"Save this contact page in"|localize:contactpage}: </td>
                                <td style="border-right: 0px; border-bottom: 0px;">
                                    <select name="save_as_language">
                                        {foreach from=$languages key=k item=i}
                                            <option value="{$k}"{if $k == $selected_language} selected{/if}>{$k}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <td style="border-bottom: 0px; border-right: 0px;">&nbsp;</td>
                                <td style="border-bottom: 0px; border-right: 0px;">
                                    <div style="float: right;"><input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_edit_contact', 1024, 'upload_popup');"> <input type="submit" value="{"Save"|localize}"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>
        </form>

