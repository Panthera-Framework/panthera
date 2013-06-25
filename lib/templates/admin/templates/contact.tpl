
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

    jQuery('#map_form').submit(function () {
        place = jQuery('#map_searchbox').val();

        if (place != "")
        {
            getLocation(place);
            jQuery('#map_bounds').val(JSON.stringify({ "bounds":map.getBounds(), "zoom": map.getZoom(), "center": map.getCenter() }));
        }

        return false;
    });

    /**
      * Save map bounds
      *
      * @author Mateusz Warzyński
      */

    jQuery('#contact_form').submit(function () {
        jQuery('#map_bounds').val(JSON.stringify({ "bounds":map.getBounds(), "zoom": map.getZoom(), "center": map.getCenter() }));
    });

    /**
      * Save contact information
      *
      * @author Mateusz Warzyński
      */

    $('#contact_form').submit(function () {
        panthera.jsonPOST({ data: '#contact_form', messageBox: 'userinfoBox'});

        return false;

    });
});
</script>

        <div class="titlebar">{"Contact"|localize:contactpage} - {"Street adress, phone number, location etc."|localize:contactpage}{include file="_navigation_panel.tpl"}</div><br>

        <div class="msgSuccess" id="userinfoBox_success"></div>
        <div class="msgError" id="userinfoBox_failed"></div>

        <div class="grid-1">
               <div class="title-grid">{"Map"|localize:contactpage}</div>

               <div class="content-gird">
                    <form action="?display=contact&action=save" method="GET" id="map_form">{"Search"|localize:contactpage}: <input type="text" value="" id="map_searchbox" style="width:300px;height:25px; font-size:15px;"></form>
                    <div id="map" style="width: 100%; height: 300px; margin-top: 10px;"></div>
               </div>
       </div>

       <br>

        <form action="?display=contact&action=save" method="GET" id="contact_form">
         <div class="grid-1">
	      	 <div class="title-grid">{"Content"|localize}</div>
	      	 <div class="content-gird">
                 <textarea id="address_text" name="address_text" style="width: 100%;"></textarea><br><br>
            </div>
		 </div>

		 <input type="hidden" name="map_bounds" id="map_bounds">

         <div class="grid-2">
               <div class="title-grid">{"Insert max. 3 e-mail adresses"|localize:contactpage}</div>

               <div class="content-gird">
                    <table class="gridTable" style="border: 0px">
                        <tbody>
                            <tr>
                                <td>1.</td>
                                <td><input type="text" name="email_first" value="{$email_first}"><br></td>
                            </tr>
                            <tr>
                                <td>2.</td>
                                <td><input type="text" name="email_second" value="{$email_second}"><br></td>
                            </tr>
                            <tr>
                                <td>3.</td>
                                <td><input type="text" name="email_third" value="{$email_third}"><br></td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>

         <div class="grid-2">
               <div class="title-grid">{"Options"|localize:messages}</div>

               <div class="content-gird">
                    <table class="gridTable" style="border: 0px">
                        <tbody>
                            <tr>
                                <td><input type="submit" value="{"Save"|localize}"></td>
                            </tr>
                            <tr>
                                <td><input type="button" value="{"Manage permissions"|localize:messages}" onclick="createPopup('_ajax.php?display=acl&popup=true&name=can_manage_custompage_{$custompage_id}', 1024, 'upload_popup');"></td>
                            </tr>
                        </tbody>
                    </table>
               </div>
         </div>
        </form>

